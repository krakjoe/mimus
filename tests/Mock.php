<?php
namespace mimus\tests {

	class Mock extends \PHPUnit\Framework\TestCase {
		public function testClassDoesNotExistLogicException() {
			$this->expectException(\LogicException::class);

			\mimus\Mock::of(None::class);
		}

		public function testMockNonExistentMethodLogicException() {
			$mock = \mimus\Mock::of(\mimus\tests\classes\Bar::class);

			$this->expectException(\LogicException::class);

			$mock->rule("nonExistentMethod");
		}

		public function testPartialLogicExceptionArgs() {
			$mock = \mimus\Mock::of(\mimus\tests\classes\Foo::class);

			$this->expectException(\LogicException::class);

			$mock->partialize(42);			
		}

		public function testPartialLogicExceptionArgNotValidClass() {
			$mock = \mimus\Mock::of(\mimus\tests\classes\Foo::class);

			$this->expectException(\LogicException::class);

			$mock->partialize("none");			
		}

		public function testPartialMockArray() {
			$mock = \mimus\Mock::of(\mimus\tests\classes\Foo::class);
			$mock->partialize([
				"publicMethod",
				"privateMethod",
				"protectedMethod"
			]);
			$object = $mock->getInstance();
			$this->assertFalse($object->publicMethod(true));
		}

		public function testPartialMockClass() {
			$mock = \mimus\Mock::of(\mimus\tests\classes\FooFace::class);
			$mock->partialize(\mimus\tests\classes\IFooFace::class);
			$object = $mock->getInstance();
			$this->assertFalse($object->publicMethod(true));
		}

		public function testMockGetInstanceConstructed() {
			$mock = \mimus\Mock::of(\mimus\tests\classes\Qux::class, false, [
				"__construct"
			]);

			$this->expectException(\Error::class);
			$mock->getInstance(true);
		}
	}
}
