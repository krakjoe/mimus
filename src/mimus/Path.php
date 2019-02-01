<?php
namespace mimus {
	
	class Path {
		public static $sentinal; /* DO NOT TOUCH */

		public function __construct(...$accepts) {
			if (func_num_args()) {
				$this->accepts = $accepts;
			} else $this->accepts = Path::$sentinal;
			$this->returns = Path::$sentinal;
		}

		public function executes(\Closure $body = null) : Path {
			if ($body === null) {
				$this->executes = true;
			} else  $this->executes = $body;

			return $this;
		}

		public function returns($value) : Path {
			if ($this->void) {
				throw new \LogicException(
					"cannot return from void path");
			}
			$this->returns = $value;

			return $this;
		}

		public function void() : Path {
			if ($this->returns !== Path::$sentinal) {
				throw new \LogicException(
					"void path cannot return");
			}
			$this->void = true;

			return $this;
		}

		public function throws(string $class) : Path {
			if ($this->executes === false) {
				throw new \LogicException(
					"non executable path cannot throw");
			}

			$this->throws = $class;

			return $this;
		}

		public function never() : Path {
			$this->limit = true;

			return $this;
		}

		public function once() : Path {
			$this->limit = 1;

			return $this;
		}

		public function limit(int $times) : Path {
			$this->limit = $times;

			return $this;
		}

		public function try(?Exception $except, bool $count, ...$args) : bool {
			if ($this->accepts == Path::$sentinal) {
				return true;
			}

			if ($count && count($args) != count($this->accepts)) {
				throw new Exception($except,
					"expected %d arguments, got %d",
					count($this->accepts), count($args));
			}

			foreach ($this->accepts as $idx => $arg) {
				$expected = gettype($arg);
				$got      = gettype($args[$idx]);

				if (($expected == 'string' || $expected == 'object') && is_object($args[$idx])) {
					if (!$args[$idx] instanceof $arg) {
						throw new Exception($except,
							"argument %d expected to be %s got %s",
							$idx,
							\mimus\printable($arg),
							\mimus\printable($args[$idx]));
					}
					continue;
				}

				if ($expected != $got) {
					throw new Exception($except,
						"argument %d expected to be of type %s, got type %s",
						$idx, $expected, $got);
				}

				switch ($expected) {
					case 'null':
					case 'string':
					case 'integer':
					case 'double':
					case 'boolean':
					case 'array':
						if ($arg !== $args[$idx]) {
							throw new Exception($except,
								"argument %d expected to be %s got %s",
								$idx,
								\mimus\printable($arg),
								\mimus\printable($args[$idx]));
						}
					break;

					default:
						throw new Exception($except,
							"argument %d is an unknown type, %s",
							$idx, gettype($arg)); 
				}
			}

			return true;
		}

		private function verifyException(\Throwable $thrown = null) {
			if (!$thrown || !($thrown instanceof $this->throws)) {
				throw new Exception(null,
					"expected exception of type %s, %s thrown",
					$this->throws, $thrown ? get_class($thrown) : "nothing");
			}

			return true;
		}

		private function verifyReturn($value = null) {
			if ($this->void && $value !== null) {
				throw new Exception(null,
					"return value not expected, got %s",
					\mimus\printable($value));
			}

			if ($this->returns === self::$sentinal) {
				return;
			}

			if ($this->returns && $value === null) {
				throw new Exception(null,
					"return value expected to be of type %s, got null",
					gettype($this->returns));
			}

			if (gettype($this->returns) != gettype($value)) {
				if (gettype($this->returns) != 'string' && gettype($value) != 'object') {
					throw new Exception(null,
						"return value expected to be of type %s, got %s",
						gettype($this->returns),
						gettype($value));
				}
			}

			if (gettype($this->returns) == 'object' || gettype($value) == 'object') {
				if (gettype($value) != 'object' || !$value instanceof $this->returns) {
					throw new Exception(null,
						"return value expected to be of class %s, got %s",
						$this->returns, 
							gettype($value) == 'object' ? 
								get_class($value) : gettype($value));
				}
				return;
			}

			if ($value !== $this->returns) {
				throw new Exception(null,
						"return value expected to be %s, got %s",
						\mimus\printable($this->returns),
						\mimus\printable($value));
			}
		}

		private function verifyValidators(?object $object, $retval = null) : void {
			$except = null;

			if (!$this->validators) {
				return;
			}

			foreach ($this->validators as $idx => $validator) {
				$result = $object ? 
					$validator->call($object, $retval) :
					$validator($retval);

				if (!$result) {
					$except = new Exception($except,
						"validator %d failed",
						$idx);
				}
			}

			if ($except)
				throw $except;
		}

		public function travel(?object $object, \Closure $prototype, ...$args) {
			$retval = null;
			$thrown = null;
			if ($this->limit !== false) {
				if ($this->limit === true ||
				    ++$this->count > $this->limit) {
					throw new Exception(null,
						"limit of %d exceeded", $this->limit);
				}
			}
			try {
				if ($this->executes === false) {
					$retval = $this->returns;
				} else if ($this->executes === true) {
					$retval = $object ? 
						$prototype->call($object, ...$args) :
						$prototype(...$args);
				} else {
					$retval = $object ? 
						$this->executes->call($object, $prototype->bindTo($object), ...$args) :
						($this->executes)($prototype, ...$args);
				}
			} catch (\Throwable $thrown) {
				if ($this->throws) {
					$this->verifyException($thrown);
				}

				throw $thrown;
			}

			if ($this->throws) {
				$this->verifyException(null);
			}

			$this->verifyReturn($retval);

			$this->verifyValidators($object, $retval);

			return $retval;
		}

		public function validates(\Closure $validator) : Path {
			$this->validators[] = $validator;

			return $this;
		}

		private $accepts;
		private $returns;
		private $void;
		private $throws;
		private $executes = false;
		private $limit = false;
		private $count = 0;
		private $validators = [];
	}

	Path::$sentinal = new class{};
}
