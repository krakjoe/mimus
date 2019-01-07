<?php
namespace mimus\tests {

	class Mock extends \PHPUnit\Framework\TestCase {
		public function testClassDoesNotExistLogicException() {
			$this->expectException(\LogicException::class);

			\mimus\Mock::of(None::class);
		}
		
		public function testWhiteList() {
			$mock = \mimus\Mock::of(\mimus\tests\classes\Bar::class, false, [
				"publicMethodUntouched"
			]);

			$mock->rule("publicMethod")
				->expects()
				->returns(false);

			$object = $mock->getInstance();

			$this->assertTrue($object->publicMethodUntouched(true));
			$this->assertFalse($object->publicMethod(true));
		}

		public function testPublicMethodExpectAndReturnTrue() {
			$mock = \mimus\Mock::of(\mimus\tests\classes\Foo::class);

			$mock->rule("publicMethod")
				->expects(true)
				->returns(true);

			$object = $mock->getInstance();

			$this->assertTrue($object->publicMethod(true));
		}

		public function testPublicMethodExpectFalseAndFalse() {
			$mock = \mimus\Mock::of(\mimus\tests\classes\Foo::class);

			$mock->rule("publicMethod")
				->expects(false)
				->returns(false);

			$object = $mock->getInstance();

			$this->assertFalse($object->publicMethod(false));
		}
		
		public function testPublicMethodExecutes() {
			$mock = \mimus\Mock::of(\mimus\tests\classes\Foo::class);

			$mock->rule("publicMethod")
				->expects(true)
				->executes();

			$mock->rule("protectedMethod")
				->expects(true)
				->executes();

			$mock->rule("privateMethod")
				->expects(true)
				->executes();

			$mock->rule("publicMethod")
				->expects(false)
				->returns(false);

			$object = $mock->getInstance();

			$this->assertFalse($object->publicMethod(true));
			$this->assertFalse($object->publicMethod(false));
		}
	}
}
