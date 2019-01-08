<?php
namespace mimus\tests {

	use \mimus\Double as double;

	class Path extends \PHPUnit\Framework\TestCase {

		public function testLogicExceptionReturnCannotBeVoid() {
			$builder = double::class(\mimus\tests\classes\Foo::class);

			$this->expectException(\LogicException::class);

			$builder->rule("publicMethod")
				->expects(true)
				->returns(true)
				->void();
		}

		public function testLogicExceptionVoidCannotReturn() {
			$builder = double::class(\mimus\tests\classes\Foo::class);

			$this->expectException(\LogicException::class);

			$builder->rule("publicMethod")
				->expects(true)
				->void()
				->returns(true);
		}

		public function testLogicExceptionNoneExecutablePathCannotThrow() {
			$builder = double::class(\mimus\tests\classes\Foo::class);

			$this->expectException(\LogicException::class);

			$builder->rule("publicMethod")
				->expects()
				->throws(\Throwable::class);
		}

		public function testWrongArgumentCount() {
			$builder = double::class(\mimus\tests\classes\Foo::class);

			$builder->rule("publicMethod")
				->expects(true)
				->executes(function(){});

			$object = $builder->getInstance();
			
			$this->expectException(\mimus\Exception::class);
			$this->assertNull($object->publicMethod(true, true));
		}

		public function testUnexpectedObject() {
			$builder = double::class(\mimus\tests\classes\Foo::class);

			$builder->rule("publicMethod")
				->expects(\stdClass::class)
				->executes(function(){});

			$object = $builder->getInstance();
			
			$this->expectException(\mimus\Exception::class);
			$this->assertNull($object->publicMethod(new class{}));
		}

		public function testUnexpectedType() {
			$builder = double::class(\mimus\tests\classes\Foo::class);

			$builder->rule("publicMethod")
				->expects(1)
				->executes(function(){});

			$object = $builder->getInstance();
			
			$this->expectException(\mimus\Exception::class);
			$this->assertNull($object->publicMethod(true));
		}

		public function testUnexpectedValue() {
			$builder = double::class(\mimus\tests\classes\Foo::class);
			$builder->rule("publicMethod")
				->expects(true)
				->executes(function(){});

			$object = $builder->getInstance();
			
			$this->expectException(\mimus\Exception::class);
			$this->assertNull($object->publicMethod(false));
		}

		public function testExecutes() {
			$builder = double::class(\mimus\tests\classes\Foo::class);
			$builder->rule("publicMethod")
				->expects()
				->executes();
			$builder->rule("privateMethod")
				->expects()
				->executes();
			$builder->rule("protectedMethod")
				->expects()
				->executes();

			$object = $builder->getInstance();
			
			$this->assertFalse($object->publicMethod(true));
		}

		public function testException() {
			$builder = double::class(\mimus\tests\classes\Foo::class);

			$builder->rule("publicMethod")
				->expects(true)
				->executes(function(){
					throw new \Error();
				})
				->throws(\Error::class);

			$object = $builder->getInstance();
			
			$this->expectException(\Error::class);
			$this->assertNull($object->publicMethod(true));
		}

		public function testExpectedException() {
			$builder = double::class(\mimus\tests\classes\Foo::class);

			$builder->rule("publicMethod")
				->expects(true)
				->executes(function(){
					
				})
				->throws(\Error::class);

			$object = $builder->getInstance();
			
			$this->expectException(\mimus\Exception::class);
			$this->assertNull($object->publicMethod(true));
		}

		public function testReturnMissing() {
			$builder = double::class(\mimus\tests\classes\Foo::class);

			$builder->rule("publicMethod")
				->expects(true)
				->executes(function(){
					
				})
				->returns(true);

			$object = $builder->getInstance();
			
			$this->expectException(\mimus\Exception::class);
			$this->assertNull($object->publicMethod(true));
		}

		public function testReturnUnexpected() {
			$builder = double::class(\mimus\tests\classes\Foo::class);
			$builder->rule("publicMethod")
				->expects(true)
				->executes(function(){
					return true;
				})
				->void();

			$object = $builder->getInstance();
			
			$this->expectException(\mimus\Exception::class);
			$this->assertNull($object->publicMethod(true));
		}

		public function testReturnTypeMismatch() {
			$builder = double::class(\mimus\tests\classes\Foo::class);

			$builder->rule("publicMethod")
				->expects(true)
				->executes(function(){
					return "hello";
				})
				->returns(true);

			$object = $builder->getInstance();
			
			$this->expectException(\mimus\Exception::class);
			$this->assertNull($object->publicMethod(true));
		}

		public function testReturnTypeWrongObject() {
			$builder = double::class(\mimus\tests\classes\Foo::class);

			$builder->rule("publicMethod")
				->expects(true)
				->executes(function(){
					return new class{};
				})
				->returns(\stdClass::class);

			$object = $builder->getInstance();
			
			$this->expectException(\mimus\Exception::class);
			$this->assertNull($object->publicMethod(true));
		}

		public function testReturnTypeCorrectObject() {
			$builder = double::class(\mimus\tests\classes\Foo::class);

			$builder->rule("publicMethod")
				->expects(true)
				->executes(function(){
					return new \stdClass;
				})
				->returns(\stdClass::class);

			$object = $builder->getInstance();
			
			$this->assertTrue($object->publicMethod(true) instanceof \stdClass);
		}

		public function testReturnMismatch() {
			$builder = double::class(\mimus\tests\classes\Foo::class);

			$builder->rule("publicMethod")
				->expects(true)
				->executes(function(){
					return false;
				})
				->returns(true);

			$object = $builder->getInstance();
			
			$this->expectException(\mimus\Exception::class);
			$this->assertNull($object->publicMethod(true));
		}

		public function testExpectsAny() {
			$builder = double::class(\mimus\tests\classes\Foo::class);

			$builder->rule("publicMethod")
				->expects()
				->executes(function(){
					return "mimus";
				})
				->returns("mimus");

			$object = $builder->getInstance();
			
			$this->assertSame("mimus", $object->publicMethod(true));
		}

		public function testExpectsAnyFallback() {
			$builder = double::class(\mimus\tests\classes\Foo::class);

			$builder->rule("publicMethod")
				->expects("unused")
				->executes(function(){
					return "unused";
				});
			$builder->rule("publicMethod")
				->expects()
				->executes(function(){
					return "mimus";
				})
				->returns("mimus");

			$object = $builder->getInstance();
			
			$this->assertSame("mimus", $object->publicMethod(true));
		}

		public function testLimitToOnce() {
			$builder = double::class(\mimus\tests\classes\Foo::class);

			$builder->rule("publicMethod")
				->expects()
				->executes(function(){
					return true;	
				})
				->once();

			$object = $builder->getInstance();
			
			$this->assertTrue($object->publicMethod(true));
			$this->expectException(\mimus\Exception::class);
			$this->assertTrue($object->publicMethod(true));
		}

		public function testLimitToNever() {
			$builder = double::class(\mimus\tests\classes\Foo::class);

			$builder->rule("publicMethod")
				->expects()
				->executes(function(){
					return true;	
				})
				->never();

			$object = $builder->getInstance();
			$this->expectException(\mimus\Exception::class);
			$this->assertNull($object->publicMethod(true));

		}

		public function testLimitToN() {
			$builder = double::class(\mimus\tests\classes\Foo::class);

			$builder->rule("publicMethod")
				->expects()
				->executes(function(){
					return true;	
				})
				->limit(2);

			$object = $builder->getInstance();

			$this->assertTrue($object->publicMethod(true));
			$this->assertTrue($object->publicMethod(true));

			$this->expectException(\mimus\Exception::class);

			$this->assertNull($object->publicMethod(true));
		}

		public function testValidatorsFail() {
			$builder = double::class(\mimus\tests\classes\Foo::class);

			$builder->rule("publicMethod")
				->expects()
				->executes()
				->validates(function($retval = null) : bool {
					return false;
				});

			$object = $builder->getInstance();

			$this->expectException(\mimus\Exception::class);
			$this->assertTrue($object->publicMethod(true));
		}

		public function testValidatorsSuccess() {
			$builder = double::class(\mimus\tests\classes\Foo::class);

			$builder->rule("publicMethod")
				->expects()
				->returns(true)
				->validates(function($retval = null) : bool {
					return true;
				});

			$object = $builder->getInstance();

			$this->assertTrue($object->publicMethod(true));
		}
	}
}
