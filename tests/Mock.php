<?php
namespace mimus\tests {

	use \mimus\Double as double;

	class Mock extends \PHPUnit\Framework\TestCase {

		public function testClassDoesNotExistLogicException() {
			$this->expectException(\LogicException::class);

			double::class(None::class);
		}

		public function testClassMock() {
			$builder = double::class(\mimus\tests\classes\Foo::class);

			$object = $builder->getInstance();

			$this->assertInstanceOf(\mimus\tests\classes\Foo::class, $object);
		}

		public function testAbstractMockFails() {
			$this->expectException(\LogicException::class);
			$builder = double::class(myabstract::class, 
				\mimus\tests\classes\AbstractFoo::class);
		}

		public function testMake() {
			$builder = double::make(DateTime::class, [\DateTime::class]);

			$object = $builder->getInstance();

			$this->assertInstanceOf(\DateTime::class, $object);
			$this->assertInstanceof(DateTime::class, $object);
		}

		public function testMakeAbstract() {
			$builder = double::make(myabstract::class, 
				[\mimus\tests\classes\AbstractFoo::class]);
	
			$object = $builder->getInstance();

			$this->assertInstanceOf(myabstract::class, $object);
			$this->assertInstanceof(\mimus\tests\classes\AbstractFoo::class, $object);
		}

		public function testMakeParentAndInterfaces() {
			$builder = double::make(complex::class, [
				/* extends */ \mimus\tests\classes\AbstractFoo::class,
				/* implements */ [
					\mimus\tests\classes\IFooFace::class,
					\mimus\tests\classes\IFooFaceTwo::class
				]
			]);

			$object = $builder->getInstance();

			$this->assertInstanceOf(complex::class, $object);
			$this->assertInstanceOf(\mimus\tests\classes\AbstractFoo::class, $object);
			$this->assertInstanceOf(\mimus\tests\classes\IFooFace::class, $object);
			$this->assertInstanceOf(\mimus\tests\classes\IFooFaceTwo::class, $object);
		}
		
		public function testMockNonExistentMethodLogicException() {
			$builder = double::class(\mimus\tests\classes\Bar::class);

			$this->expectException(\LogicException::class);

			$builder->rule("nonExistentMethod");
		}

		public function testPartialLogicExceptionArgs() {
			$builder = double::class(\mimus\tests\classes\Foo::class);

			$this->expectException(\LogicException::class);

			$builder->partialize(42);			
		}

		public function testPartialLogicExceptionArgNotValidClass() {
			$builder = double::class(\mimus\tests\classes\Foo::class);

			$this->expectException(\LogicException::class);

			$builder->partialize("none");			
		}

		public function testPartialMockArray() {
			$builder = double::class(\mimus\tests\classes\Foo::class);
			$builder->partialize([
				"publicMethod",
				"privateMethod",
				"protectedMethod"
			]);
			$object = $builder->getInstance();
			$this->assertFalse($object->publicMethod(true));
		}

		public function testPartialMockClass() {
			$builder = double::class(\mimus\tests\classes\FooFace::class);
			$builder->partialize(\mimus\tests\classes\IFooFace::class);
			$object = $builder->getInstance();
			$this->assertFalse($object->publicMethod(true));
		}

		public function testMockGetInstanceConstructed() {
			$builder = double::class(\mimus\tests\classes\Qux::class);
			$builder->partialize([
				"__construct"
			]);

			$this->expectException(\Error::class);
			$builder->getInstance(true);
		}
	}
}
