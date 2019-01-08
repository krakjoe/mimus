<?php
namespace mimus {

	class Double {

		public static function class(string $class, bool $reset = true) {
			if (!class_exists($class)) {
				throw new \LogicException(
					"{$class} does not exist or is not a class");
			}

			return self::definition($class, $reset);
		}

		public static function interface(string $name, $interfaces, bool $reset = true) {
			if (!is_array($interfaces) && !is_string($interfaces)) {
				throw new \LogicException(
					"interfaces expected to be an array of, or an interface name");
			}
			
			foreach ((array) $interfaces as $interface) {
				if (!interface_exists($interface)) {
					throw new \LogicException(
						"{$interface} does not exist or is not an interface");
				}
			}

			return self::definition($name, $reset, (array) $interfaces);
		}

		private static function definition($name, $reset, ...$args) {
			if (!isset(Double::$doubles[$name])) {
				double::$doubles[$name] = 
					new self(new \Componere\Definition($name, ...$args));
			} else if ($reset) {
				double::$doubles[$name]->reset();
			}

			return double::$doubles[$name];
		}

		private function __construct(\Componere\Definition $definition) {
			$this->definition = $definition;
			$this->reflector  = $this->definition->getReflector();

			if ($this->reflector->isAbstract()) {
				throw new \LogicException(
					"cannot mock an abstract class");
			}

			foreach ($this->reflector->getMethods() as $prototype) {
				$name      = $prototype->getName();

				$this->table[$name] = [];

				$closure   = $this->definition->getClosure($name);
				$table     =& $this->table[$name];

				$this->definition->addMethod($name, $implementation = new \Componere\Method(function(...$args) use($name, $closure, $prototype, &$table) {
					$except = null;
					$path    = null;

					if (!$table) {
						return;
					}

					foreach ($table as $idx => $rule) {
						try {
							$path = $rule->match($except, true, ...$args);
						} catch (Exception $ex) {
							$except = $ex;
						}

						if ($path) {
							$except = null;
							break;
						}
					}

					if ($except) {
						throw $except;
					}

					if ($prototype->isStatic()) {
						return $path->travel(null, $closure, ...$args);
					}

					return $path->travel($this, $closure, ...$args);
				}));

				if ($prototype) {
					if ($prototype->isStatic()) {
						$implementation->setStatic();
					}

					if ($prototype->isPrivate()) {
						$implementation->setPrivate();
					}

					if ($prototype->isProtected()) {
						$implementation->setProtected();
					}

					if ($prototype->isFinal()) {
						$implementation->setFinal();
					}
				}
			}
			
			$this->definition->register();
		}

		public function partialize($on) {
			if (!is_array($on) && !is_string($on)) {
				throw new \LogicException(
					"expected an array of method names or the name of a valid class");
			}

			if (is_array($on)) {
				foreach ($on as $method) {
					$this->rule($method)
						->expects()
						->executes();
				}
			} else {
				try {
					$reflector = new \ReflectionClass($on);
				} catch (\ReflectionException $re) {
					throw new \LogicException(
						"expected a valid class name, {$on} cannot be loaded");
				}

				foreach ($reflector->getMethods() as $method) {
					$this->rule($method->getName())
						->expects()
						->executes();
				}
			}
		}

		public function rule(string $name) : Rule {
			if (!isset($this->table[$name])) {
				throw new \LogicException(
					"method {$name} does not exist, or is whitelisted");
			}
			return $this->table[$name][] = new Rule($name);
		}

		public function reset(string $name = null) {
			if ($name === null) {
				foreach ($this->table as $name => $rules) {
					$this->reset($name);
				}
			} else {
				foreach ($this->table[$name] as $idx => $rule) {
					unset($this->table[$name][$idx]);
				}		
			}
		}

		public function getInstance(...$args) {
			if (!func_num_args()) {
				return $this->reflector->newInstanceWithoutConstructor();
			}
			return $this->reflector->newInstanceArgs($args);
		}

		private $definition;
		private $reflector;
		private $table;

		private static $doubles;
	}

	function printable($value) {
		switch (gettype($value)) {
			case 'null':
				return 'null';
			case 'boolean':
				return $value ? "bool(true)" : "bool(false)";
			case 'integer':
				return sprintf("int(%d)", $value);
			case 'double':
				return sprintf("float(%s)", $value);
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
}
