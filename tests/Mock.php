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

		public function testMockImplementsClassDoesNotExist() {
			$builder = double::class(\mimus\tests\classes\NoFooFace::class);

			$this->expectException(\LogicException::class);

			$builder->implements(\mimus\tests\classes\IFooFaceDoesNotExist::class);
		}

		public function testMockImplements() {
			$builder = double::class(\mimus\tests\classes\NoFooFace::class);
			$builder->implements(\mimus\tests\classes\IFooFace::class);

			$object = $builder->getInstance();

			$this->assertInstanceOf(\mimus\tests\classes\IFooFace::class, $object);
		}

		public function testMockImplementsPartial() {
			$builder = double::class(\mimus\tests\classes\NoFooFacePartial::class);
			$builder->implements(\mimus\tests\classes\NoFooFacePartialInterface::class, true);

			$object = $builder->getInstance();

			$this->assertInstanceOf(\mimus\tests\classes\NoFooFacePartialInterface::class, $object);
			$this->assertTrue($object->partialized(false));
			$this->assertNull($object->notPartialized(false));
		}

		public function testMockUsesNonTrait() {
			$builder = double::class(\mimus\tests\classes\FooFaceNoUse::class);

			$this->expectException(\LogicException::class);

			$builder->use(\mimus\tests\classes\NoFooFacePartialInterface::class);
		}

		public function testMockUses() {
			$builder = double::class(\mimus\tests\classes\FooFaceNoUse::class);
			$builder->use(\mimus\tests\classes\FooFaceTrait::class)
				->rule("nonTraitMethod")
				->expects()
				->executes();
			$builder->rule("traitMethod")
				->expects()
				->executes();

			$object = $builder->getInstance();

			$this->assertFalse($object->nonTraitMethod()); /* comes from class */
			$this->assertTrue($object->traitMethod()); /* comes from trait */
		}

		public function testMockUsesAlreadyRegisteredException() {
			$builder = double::class(\mimus\tests\classes\FooFaceNoUse::class);

			$this->expectException(\LogicException::class);

			$builder->use(\mimus\tests\classes\FooFaceTraitUnused::class);
		}

		public function testMockUsesAlreadyRegisteredOkay() {
			/* this only seems to be a duplicate test */
			$builder = double::class(\mimus\tests\classes\FooFaceNoUse::class);

			$builder->use(\mimus\tests\classes\FooFaceTrait::class)
				->rule("nonTraitMethod")
				->expects()
				->executes();
			$builder->rule("traitMethod")
				->expects()
				->executes();

			$object = $builder->getInstance();

			$this->assertFalse($object->nonTraitMethod()); /* comes from class */
			$this->assertTrue($object->traitMethod()); /* comes from trait */
		}

		public function testMockUsesPartial() {
			$builder = double::class(\mimus\tests\classes\FooFaceNoUse::class);
			$builder->use(\mimus\tests\classes\FooFaceTrait::class, true);

			$object = $builder->getInstance();

			$this->assertTrue($object->traitMethod());
		}

		public function testMockUsesUnregisteredPartial() {
			$builder = double::class(\mimus\tests\classes\FooFaceNoRegister::class);
			$builder->use(\mimus\tests\classes\FooFaceTrait::class, true);

			$object = $builder->getInstance();

			$this->assertTrue($object->traitMethod());
		}

		public function testMockImplementsAlreadyRegistered() {
			$builder = double::class(\mimus\tests\classes\NoFooFace::class);

			$object = $builder->getInstance();

			$this->assertInstanceOf(\mimus\tests\classes\IFooFace::class, $object);
		}

		public function testMockImplementsAlreadyBuilt() {
			$builder = double::class(\mimus\tests\classes\NoFooFace::class);
			$builder->implements(\mimus\tests\classes\IFooFace::class);

			$this->expectException(\LogicException::class);

			$builder->implements(\mimus\tests\classes\IFooFaceTwo::class);
		}

		public function testMockImplementsAlreadyRegisteredPartial() {
			$builder = double::class(\mimus\tests\classes\NoFooFace::class);
			$builder->implements(\mimus\tests\classes\IFooFace::class, true);

			$object = $builder->getInstance();

			$this->assertInstanceOf(\mimus\tests\classes\IFooFace::class, $object);
			$this->assertTrue($object->publicMethod(false));
			$this->assertFalse($object->publicMethod(true));
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
