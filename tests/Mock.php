<?php
namespace mimus\tests {

	use \mimus\Double as double;

	class Mock extends \PHPUnit\Framework\TestCase {

		public function testClassDoesNotExistLogicException() {
			$this->expectException(\LogicException::class);

			double::class(None::class);
		}

		public function testInterfaceDoesNotExistException() {
			$this->expectException(\LogicException::class);

			double::interface(MyInterface::class, None::class);
		}

		public function testClassMock() {
			$mock = double::class(\mimus\tests\classes\Foo::class);

			$object = $mock->getInstance();

			$this->assertInstanceOf(\mimus\tests\classes\Foo::class, $object);
		}

		public function testInterfaceMock() {
			$mock = double::interface(myinterface::class, \mimus\tests\classes\IFooFace::class);

			$object = $mock->getInstance();

			$this->assertInstanceOf(\mimus\tests\classes\IFooFace::class, $object);
		}

		public function testInterfacesMock() {
			$mock = double::interface(myinterfaces::class, [
				\mimus\tests\classes\IFooFace::class,
				\mimus\tests\classes\IFooFaceTwo::class,
			]);

			$object = $mock->getInstance();

			$this->assertInstanceOf(\mimus\tests\classes\IFooFace::class, $object);
			$this->assertInstanceOf(\mimus\tests\classes\IFooFaceTwo::class, $object);
		}

		public function testAbstractMock() {
			$mock = double::abstract(myabstract::class, 
				\mimus\tests\classes\AbstractFoo::class);

			$object = $mock->getInstance();

			$this->assertInstanceOf(\mimus\tests\classes\AbstractFoo::class, $object);
			$this->assertInstanceOf(myabstract::class, $object);
		}
		
		public function testMockNonExistentMethodLogicException() {
			$mock = double::class(\mimus\tests\classes\Bar::class);

			$this->expectException(\LogicException::class);

			$mock->rule("nonExistentMethod");
		}

		public function testPartialLogicExceptionArgs() {
			$mock = double::class(\mimus\tests\classes\Foo::class);

			$this->expectException(\LogicException::class);

			$mock->partialize(42);			
		}

		public function testPartialLogicExceptionArgNotValidClass() {
			$mock = double::class(\mimus\tests\classes\Foo::class);

			$this->expectException(\LogicException::class);

			$mock->partialize("none");			
		}

		public function testPartialMockArray() {
			$mock = double::class(\mimus\tests\classes\Foo::class);
			$mock->partialize([
				"publicMethod",
				"privateMethod",
				"protectedMethod"
			]);
			$object = $mock->getInstance();
			$this->assertFalse($object->publicMethod(true));
		}

		public function testPartialMockClass() {
			$mock = double::class(\mimus\tests\classes\FooFace::class);
			$mock->partialize(\mimus\tests\classes\IFooFace::class);
			$object = $mock->getInstance();
			$this->assertFalse($object->publicMethod(true));
		}

		public function testMockGetInstanceConstructed() {
			$mock = double::class(\mimus\tests\classes\Qux::class, false, [
				"__construct"
			]);

			$this->expectException(\Error::class);
			$mock->getInstance(true);
		}
	}
}
