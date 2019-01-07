<?php
namespace mimus {

	use Componere\Definition;
	use Componere\Method;

	class Mock {
		public /* please */ static /* don't look at my shame */
			function of(string $class, bool $reset = true, array $whitelist = []) {

			if (!class_exists($class)) {
				throw new \LogicException("{$class} does not exist, nothing to mock");
			}

			if (!isset(Mock::$mocks[$class])) {
				Mock::$mocks[$class] = new self($class, $whitelist);
			} else if ($reset) {
				Mock::$mocks[$class]->reset();
			}

			return Mock::$mocks[$class];
		}

		private function __construct(string $class, array $whitelist = []) {
			$this->definition = new Definition($class);
			$this->reflector  = $this->definition->getReflector();

			foreach ($this->reflector->getMethods() as $prototype) {
				$name      = $prototype->getName();

				$this->table[$name] = [];

				if ($whitelist && in_array($name, $whitelist)) {
					continue;
				}

				$closure   = $this->definition->getClosure($name);
				$table     =& $this->table[$name];

				$this->definition->addMethod($name, $implementation = new Method(function(...$args) use($name, $closure, $prototype, &$table) {
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

		public function rule(string $name) : Rule {
			if (!isset($this->table[$name])) {
				throw new \LogicException(
					"method {$name} does not exist, or is whitelisted");
			}
			return $this->table[$name][] = new Rule($this, $name);
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

		public function getInstance(...$args) : object {
			if (!func_num_args()) {
				return $this->reflector->newInstanceWithoutConstructor();
			}
			return $this->reflector->newInstanceArgs(...$args);
		}

		private $definition;
		private $reflector;
		private $table;

		private static $mocks;
	}
}
