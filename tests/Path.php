<?php
namespace mimus\tests {

	class Path extends \PHPUnit\Framework\TestCase {

		public function testWrongArgumentCount() {
			$mock = new \mimus\Mock(\mimus\tests\classes\Foo::class);
			$mock->rule("publicMethod")
				->expects(true)
				->executes(function(){});

			$object = $mock->getMock();
			
			$this->expectException(\mimus\Exception::class);
			$this->assertNull($object->publicMethod(true, true));
		}

		public function testUnexpectedObject() {
			$mock = new \mimus\Mock(\mimus\tests\classes\Foo::class);
			$mock->rule("publicMethod")
				->expects(\stdClass::class)
				->executes(function(){});

			$object = $mock->getMock();
			
			$this->expectException(\mimus\Exception::class);
			$this->assertNull($object->publicMethod(new class{}));
		}

		public function testUnexpectedType() {
			$mock = new \mimus\Mock(\mimus\tests\classes\Foo::class);
			$mock->rule("publicMethod")
				->expects(1)
				->executes(function(){});

			$object = $mock->getMock();
			
			$this->expectException(\mimus\Exception::class);
			$this->assertNull($object->publicMethod(true));
		}

		public function testUnexpectedValue() {
			$mock = new \mimus\Mock(\mimus\tests\classes\Foo::class);
			$mock->rule("publicMethod")
				->expects(true)
				->executes(function(){});

			$object = $mock->getMock();
			
			$this->expectException(\mimus\Exception::class);
			$this->assertNull($object->publicMethod(false));
		}

		public function testException() {
			$mock = new \mimus\Mock(\mimus\tests\classes\Foo::class);
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
			$mock = new \mimus\Mock(\mimus\tests\classes\Foo::class);
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
			$mock = new \mimus\Mock(\mimus\tests\classes\Foo::class);
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
			$mock = new \mimus\Mock(\mimus\tests\classes\Foo::class);
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
			$mock = new \mimus\Mock(\mimus\tests\classes\Foo::class);
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
			$mock = new \mimus\Mock(\mimus\tests\classes\Foo::class);
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
			$mock = new \mimus\Mock(\mimus\tests\classes\Foo::class);
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
			$mock = new \mimus\Mock(\mimus\tests\classes\Foo::class);
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
	}
}
