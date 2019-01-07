<?php
namespace mimus\tests {

	class Path extends \PHPUnit\Framework\TestCase {

		public function testLogicExceptionReturnCannotBeVoid() {
			$mock = \mimus\Mock::of(\mimus\tests\classes\Foo::class);

			$this->expectException(\LogicException::class);

			$mock->rule("publicMethod")
				->expects(true)
				->returns(true)
				->void();
		}

		public function testLogicExceptionVoidCannotReturn() {
			$mock = \mimus\Mock::of(\mimus\tests\classes\Foo::class);

			$this->expectException(\LogicException::class);

			$mock->rule("publicMethod")
				->expects(true)
				->void()
				->returns(true);
		}

		public function testWrongArgumentCount() {
			$mock = \mimus\Mock::of(\mimus\tests\classes\Foo::class);

			$mock->rule("publicMethod")
				->expects(true)
				->executes(function(){});

			$object = $mock->getMock();
			
			$this->expectException(\mimus\Exception::class);
			$this->assertNull($object->publicMethod(true, true));
		}

		public function testUnexpectedObject() {
			$mock = \mimus\Mock::of(\mimus\tests\classes\Foo::class);

			$mock->rule("publicMethod")
				->expects(\stdClass::class)
				->executes(function(){});

			$object = $mock->getMock();
			
			$this->expectException(\mimus\Exception::class);
			$this->assertNull($object->publicMethod(new class{}));
		}

		public function testUnexpectedType() {
			$mock = \mimus\Mock::of(\mimus\tests\classes\Foo::class);

			$mock->rule("publicMethod")
				->expects(1)
				->executes(function(){});

			$object = $mock->getMock();
			
			$this->expectException(\mimus\Exception::class);
			$this->assertNull($object->publicMethod(true));
		}

		public function testUnexpectedValue() {
			$mock = \mimus\Mock::of(\mimus\tests\classes\Foo::class);
			$mock->rule("publicMethod")
				->expects(true)
				->executes(function(){});

			$object = $mock->getMock();
			
			$this->expectException(\mimus\Exception::class);
			$this->assertNull($object->publicMethod(false));
		}

		public function testException() {
			$mock = \mimus\Mock::of(\mimus\tests\classes\Foo::class);

			$mock->rule("publicMethod")
				->expects(true)
				->executes(function(){
					throw new \Error();
				})
				->throws(\Error::class);

			$object = $mock->getMock();
			
			$this->expectException(\Error::class);
			$this->assertNull($object->publicMethod(true));
		}

		public function testExpectedException() {
			$mock = \mimus\Mock::of(\mimus\tests\classes\Foo::class);

			$mock->rule("publicMethod")
				->expects(true)
				->executes(function(){
					
				})
				->throws(\Error::class);

			$object = $mock->getMock();
			
			$this->expectException(\mimus\Exception::class);
			$this->assertNull($object->publicMethod(true));
		}

		public function testReturnMissing() {
			$mock = \mimus\Mock::of(\mimus\tests\classes\Foo::class);

			$mock->rule("publicMethod")
				->expects(true)
				->executes(function(){
					
				})
				->returns(true);

			$object = $mock->getMock();
			
			$this->expectException(\mimus\Exception::class);
			$this->assertNull($object->publicMethod(true));
		}

		public function testReturnUnexpected() {
			$mock = \mimus\Mock::of(\mimus\tests\classes\Foo::class);
			$mock->rule("publicMethod")
				->expects(true)
				->executes(function(){
					return true;
				})
				->void();

			$object = $mock->getMock();
			
			$this->expectException(\mimus\Exception::class);
			$this->assertNull($object->publicMethod(true));
		}

		public function testReturnTypeMismatch() {
			$mock = \mimus\Mock::of(\mimus\tests\classes\Foo::class);

			$mock->rule("publicMethod")
				->expects(true)
				->executes(function(){
					return "hello";
				})
				->returns(true);

			$object = $mock->getMock();
			
			$this->expectException(\mimus\Exception::class);
			$this->assertNull($object->publicMethod(true));
		}

		public function testReturnTypeWrongObject() {
			$mock = \mimus\Mock::of(\mimus\tests\classes\Foo::class);

			$mock->rule("publicMethod")
				->expects(true)
				->executes(function(){
					return new class{};
				})
				->returns(stdClass::class);

			$object = $mock->getMock();
			
			$this->expectException(\mimus\Exception::class);
			$this->assertNull($object->publicMethod(true));
		}

		public function testReturnTypeCorrectObject() {
			$mock = \mimus\Mock::of(\mimus\tests\classes\Foo::class);

			$mock->rule("publicMethod")
				->expects(true)
				->executes(function(){
					return new \stdClass;
				})
				->returns(\stdClass::class);

			$object = $mock->getMock();
			
			$this->assertTrue($object->publicMethod(true) instanceof \stdClass);
		}

		public function testReturnMismatch() {
			$mock = \mimus\Mock::of(\mimus\tests\classes\Foo::class);

			$mock->rule("publicMethod")
				->expects(true)
				->executes(function(){
					return false;
				})
				->returns(true);

			$object = $mock->getMock();
			
			$this->expectException(\mimus\Exception::class);
			$this->assertNull($object->publicMethod(true));
		}

		public function testExpectsAny() {
			$mock = \mimus\Mock::of(\mimus\tests\classes\Foo::class);

			$mock->rule("publicMethod")
				->expects()
				->executes(function(){
					return "mimus";
				})
				->returns("mimus");

			$object = $mock->getMock();
			
			$this->assertSame("mimus", $object->publicMethod());
		}

		public function testExpectsAnyFallback() {
			$mock = \mimus\Mock::of(\mimus\tests\classes\Foo::class);

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

			$object = $mock->getMock();
			
			$this->assertSame("mimus", $object->publicMethod());
		}
	}
}
