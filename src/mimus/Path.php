<?php
namespace mimus {
	
	class Path {

		public function __construct(...$accepts) {
			$this->accepts = $accepts;
		}

		public function executes(\Closure $body = null) : Path {
			if ($body === null) {
				$this->executes = true;
			} else  $this->executes = $body;

			return $this;
		}

		public function returns($value) : Path {
			$this->returns = $value;

			return $this;
		}

		public function throws(string $class) : Path {
			$this->throws = $class;

			return $this;
		}

		public function try(?Exception $except, bool $count, ...$args) : bool {
			if ($count && count($args) != count($this->accepts)) {
				throw new Exception($except,
					"expected %d arguments, got %d",
					count($this->accepts), count($args));
			}

			foreach ($args as $idx => $arg) {
				if (gettype($arg) != gettype($this->accepts[$idx])) {
					throw new Exception($except,
						"argument %d expected to be of type %s, got type %s",
						$idx, gettype($this->accepts[$idx]), gettype($args[$idx]));
				}

				switch (gettype($args[$idx])) {

					case 'string':
					case 'integer':
					case 'float':
					case 'boolean':
						if ($args[$idx] !== $this->accepts[$idx]) {
							throw new Exception($except,
								"argument %d expected to be '%s' got '%s'",
								$idx, $this->accepts[$idx], $args[$idx]); 
						}
					break;

					default:
						var_dump(gettype($args[$idx]));
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

		private function verifyReturn($value = null) : bool {
			if ($this->returns && $value === null) {
				throw new Exception(null,
					"return value expected to be of type %s, got null",
					gettype($this->returns));
			}

			if ($this->returns === null && $value) {
				throw new Exception(null,
					"return value not expected, got null",
					gettype($value));
			}

			if (gettype($this->returns) != gettype($value)) {
				throw new Exception(null,
					"return value expected to be of type %s, got %s",
					gettype($this->returns),
					gettype($value));
			}

			if (gettype($this->returns) == 'object') {
				if (gettype($value) != 'object' || !$value instanceof $this->returns) {
					throw new Exception(null,
						"return value expected to be of class %s, got %s",
						$this->returns, 
							gettype($value) == 'object' ? 
								get_class($value) : gettype($value));
				}
			}

			if ($value !== $this->returns) {
				throw new Exception(null,
						"return value expected to be %s, got %s",
						(string) $this->returns,
						(string) $value); /* TODO(serialize) */
			}

			return true;
		}

		public function travel(object $object, \Closure $prototype, ...$args) {
			$except = null;
			$retval = null;
			try {
				if ($this->executes === false) {
					$retval = $this->returns;
				} else if ($this->executes === true) {
					$retval = $prototype->call($object, ...$args);
				} else {
					$retval = $this->executes->call($object, ...$args);
				}
			} catch (\Throwable $thrown) {
				if ($this->throws) {
					try {
						$this->verifyException($thrown);
					} catch (Exception $ex) {
						$except = $ex;
					}
				}
			} finally {
				if ($except) {
					throw $except;
				}

				if ($this->throws) {
					$this->verifyException(null);
				}

				if ($this->returns) {
					$this->verifyReturn($retval);
				}

				return $retval;
			}
		}

		private $accepts;
		private $returns;
		private $throws;
		private $executes = false;
	}
}
