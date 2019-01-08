<?php
namespace mimus\tests {

	use \mimus\Double as double;

	class Path extends \PHPUnit\Framework\TestCase {

		public function testLogicExceptionReturnCannotBeVoid() {
			$mock = double::class(\mimus\tests\classes\Foo::class);

			$this->expectException(\LogicException::class);

			$mock->rule("publicMethod")
				->expects(true)
				->returns(true)
				->void();
		}

		public function testLogicExceptionVoidCannotReturn() {
			$mock = double::class(\mimus\tests\classes\Foo::class);

			$this->expectException(\LogicException::class);

			$mock->rule("publicMethod")
				->expects(true)
				->void()
				->returns(true);
		}

		public function testLogicExceptionNoneExecutablePathCannotThrow() {
			$mock = double::class(\mimus\tests\classes\Foo::class);

			$this->expectException(\LogicException::class);

			$mock->rule("publicMethod")
				->expects()
				->throws(\Throwable::class);
		}

		public function testWrongArgumentCount() {
			$mock = double::class(\mimus\tests\classes\Foo::class);

			$mock->rule("publicMethod")
				->expects(true)
				->executes(function(){});

			$object = $mock->getInstance();
			
			$this->expectException(\mimus\Exception::class);
			$this->assertNull($object->publicMethod(true, true));
		}

		public function testUnexpectedObject() {
			$mock = double::class(\mimus\tests\classes\Foo::class);

			$mock->rule("publicMethod")
				->expects(\stdClass::class)
				->executes(function(){});

			$object = $mock->getInstance();
			
			$this->expectException(\mimus\Exception::class);
			$this->assertNull($object->publicMethod(new class{}));
		}

		public function testUnexpectedType() {
			$mock = double::class(\mimus\tests\classes\Foo::class);

			$mock->rule("publicMethod")
				->expects(1)
				->executes(function(){});

			$object = $mock->getInstance();
			
			$this->expectException(\mimus\Exception::class);
			$this->assertNull($object->publicMethod(true));
		}

		public function testUnexpectedValue() {
			$mock = double::class(\mimus\tests\classes\Foo::class);
			$mock->rule("publicMethod")
				->expects(true)
				->executes(function(){});

			$object = $mock->getInstance();
			
			$this->expectException(\mimus\Exception::class);
			$this->assertNull($object->publicMethod(false));
		}

		public function testExecutes() {
			$mock = double::class(\mimus\tests\classes\Foo::class);
			$mock->rule("publicMethod")
				->expects()
				->executes();
			$mock->rule("privateMethod")
				->expects()
				->executes();
			$mock->rule("protectedMethod")
				->expects()
				->executes();

			$object = $mock->getInstance();
			
			$this->assertFalse($object->publicMethod(true));
		}

		public function testException() {
			$mock = double::class(\mimus\tests\classes\Foo::class);

			$mock->rule("publicMethod")
				->expects(true)
				->executes(function(){
					throw new \Error();
				})
				->throws(\Error::class);

			$object = $mock->getInstance();
			
			$this->expectException(\Error::class);
			$this->assertNull($object->publicMethod(true));
		}

		public function testExpectedException() {
			$mock = double::class(\mimus\tests\classes\Foo::class);

			$mock->rule("publicMethod")
				->expects(true)
				->executes(function(){
					
				})
				->throws(\Error::class);

			$object = $mock->getInstance();
			
			$this->expectException(\mimus\Exception::class);
			$this->assertNull($object->publicMethod(true));
		}

		public function testReturnMissing() {
			$mock = double::class(\mimus\tests\classes\Foo::class);

			$mock->rule("publicMethod")
				->expects(true)
				->executes(function(){
					
				})
				->returns(true);

			$object = $mock->getInstance();
			
			$this->expectException(\mimus\Exception::class);
			$this->assertNull($object->publicMethod(true));
		}

		public function testReturnUnexpected() {
			$mock = double::class(\mimus\tests\classes\Foo::class);
			$mock->rule("publicMethod")
				->expects(true)
				->executes(function(){
					return true;
				})
				->void();

			$object = $mock->getInstance();
			
			$this->expectException(\mimus\Exception::class);
			$this->assertNull($object->publicMethod(true));
		}

		public function testReturnTypeMismatch() {
			$mock = double::class(\mimus\tests\classes\Foo::class);

			$mock->rule("publicMethod")
				->expects(true)
				->executes(function(){
					return "hello";
				})
				->returns(true);

			$object = $mock->getInstance();
			
			$this->expectException(\mimus\Exception::class);
			$this->assertNull($object->publicMethod(true));
		}

		public function testReturnTypeWrongObject() {
			$mock = double::class(\mimus\tests\classes\Foo::class);

			$mock->rule("publicMethod")
				->expects(true)
				->executes(function(){
					return new class{};
				})
				->returns(\stdClass::class);

			$object = $mock->getInstance();
			
			$this->expectException(\mimus\Exception::class);
			$this->assertNull($object->publicMethod(true));
		}

		public function testReturnTypeCorrectObject() {
			$mock = double::class(\mimus\tests\classes\Foo::class);

			$mock->rule("publicMethod")
				->expects(true)
				->executes(function(){
					return new \stdClass;
				})
				->returns(\stdClass::class);

			$object = $mock->getInstance();
			
			$this->assertTrue($object->publicMethod(true) instanceof \stdClass);
		}

		public function testReturnMismatch() {
			$mock = double::class(\mimus\tests\classes\Foo::class);

			$mock->rule("publicMethod")
				->expects(true)
				->executes(function(){
					return false;
				})
				->returns(true);

			$object = $mock->getInstance();
			
			$this->expectException(\mimus\Exception::class);
			$this->assertNull($object->publicMethod(true));
		}

		public function testExpectsAny() {
			$mock = double::class(\mimus\tests\classes\Foo::class);

			$mock->rule("publicMethod")
				->expects()
				->executes(function(){
					return "mimus";
				})
				->returns("mimus");

			$object = $mock->getInstance();
			
			$this->assertSame("mimus", $object->publicMethod(true));
		}

		public function testExpectsAnyFallback() {
			$mock = double::class(\mimus\tests\classes\Foo::class);

			$mock->rule("publicMethod")
				->expects("unused")
				->executes(function(){
					return "unused";
				});
			$mock->rule("publicMethod")
				->expects()
				->executes(function(){
					return "mimus";
				})
				->returns("mimus");

			$object = $mock->getInstance();
			
			$this->assertSame("mimus", $object->publicMethod(true));
		}

		public function testLimitToOnce() {
			$mock = double::class(\mimus\tests\classes\Foo::class);

			$mock->rule("publicMethod")
				->expects()
				->executes(function(){
					return true;	
				})
				->once();

			$object = $mock->getInstance();
			
			$this->assertTrue($object->publicMethod(true));
			$this->expectException(\mimus\Exception::class);
			$this->assertTrue($object->publicMethod(true));
		}

		public function testLimitToNever() {
			$mock = double::class(\mimus\tests\classes\Foo::class);

			$mock->rule("publicMethod")
				->expects()
				->executes(function(){
					return true;	
				})
				->never();

			$object = $mock->getInstance();
			$this->expectException(\mimus\Exception::class);
			$this->assertNull($object->publicMethod(true));

		}

		public function testLimitToN() {
			$mock = double::class(\mimus\tests\classes\Foo::class);

			$mock->rule("publicMethod")
				->expects()
				->executes(function(){
					return true;	
				})
				->limit(2);

			$object = $mock->getInstance();

			$this->assertTrue($object->publicMethod(true));
			$this->assertTrue($object->publicMethod(true));

			$this->expectException(\mimus\Exception::class);

			$this->assertNull($object->publicMethod(true));
		}

		public function testValidatorsFail() {
			$mock = double::class(\mimus\tests\classes\Foo::class);

			$mock->rule("publicMethod")
				->expects()
				->executes()
				->validates(function($retval = null) : bool {
					return false;
				});

			$object = $mock->getInstance();

			$this->expectException(\mimus\Exception::class);
			$this->assertTrue($object->publicMethod(true));
		}

		public function testValidatorsSuccess() {
			$mock = double::class(\mimus\tests\classes\Foo::class);

			$mock->rule("publicMethod")
				->expects()
				->returns(true)
				->validates(function($retval = null) : bool {
					return true;
				});

			$object = $mock->getInstance();

			$this->assertTrue($object->publicMethod(true));
		}
	}
}
