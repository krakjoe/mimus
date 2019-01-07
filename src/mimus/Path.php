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
			$this->throws = $class;

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
							$this->printable($arg),
							$this->printable($args[$idx]));
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
					case 'float':
					case 'boolean':
					case 'array':
						if ($arg !== $args[$idx]) {
							throw new Exception($except,
								"argument %d expected to be %s got %s",
								$idx,
								$this->printable($arg),
								$this->printable($args[$idx]));
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
					$this->printable($value));
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
						$this->printable($this->returns),
						$this->printable($value));
			}
		}

		private function printable($value) {
			switch (gettype($value)) {
				case 'null':
					return 'null';
				case 'boolean':
					return $value ? "bool(true)" : "bool(false)";
				case 'integer':
					return sprintf("int(%d)", $value);
				case 'double':
					return sprintf("float(%f)", $value);
				case 'string': /* TODO limit length */
					if (class_exists($value, 0))
						return $value;
					return sprintf("string(%d) \"%s\"", strlen($value), $value);
				case 'array': /* TODO limit length */
					return sprintf("array(%d) [%s]", count($value), implode(',', $value));
				case 'object':
					return get_class($value);
				default:
					return 'unknown';
			}
		}

		public function travel(?object $object, \Closure $prototype, ...$args) {
			$retval = null;
			$thrown = null;
			try {
				if ($this->executes === false) {
					$retval = $this->returns;
				} else if ($this->executes === true) {
					$retval = $object ? 
						$prototype->call($object, ...$args) :
						$prototype(...$args);
				} else {
					$retval = $object ? 
						$this->executes->call($object, ...$args) :
						($this->executes)(...$args);
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

			return $retval;
		}

		private $accepts;
		private $returns;
		private $void;
		private $throws;
		private $executes = false;
	}

	Path::$sentinal = new class{};
}
