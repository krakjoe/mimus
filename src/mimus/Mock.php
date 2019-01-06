<?php
namespace mimus {
	
	use Componere\Definition;
	use Componere\Method;
	
	class Mock {

		public function __construct(string $class) {
			$this->definition = new Definition($class);

			$this->reflector  = $this
				->definition->getReflector();
		}

		public function getMock(bool $register = false) : object {
			$this->build($register);

			return $this->reflector->newInstanceWithoutConstructor();
		}

		public function getMockConstructed(bool $register = false, ...$args) : object {
			$this->build($register);

			return $this->reflector->newInstanceArgs(...$args);
		}

		public function getMockStatic() : void {
			$this->build(true);
		}

		public function rule(string $name) : Rule {
			return $this->table[$name][] = new Rule($this, $name);
		}

		private function build(bool $register) {
			foreach ($this->definition->getClosures() as $name => $closure) {
				$prototype = $this->reflector->getMethod($name);
				$table     = $this->table[$name] ?? null;

				$this->definition->addMethod($name, $implementation = new Method(function(...$args) use($name, $closure, $table) {
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

			if ($register && !($this->state & MOCK::REGISTERED)) {
				$this->definition->register();

				$this->state |= MOCK::REGISTERED;
			}
		}

		private $definition;
		private $table;
		private $state;

		const REGISTERED = 0x00000001;
	}
}
