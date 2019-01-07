<?php
namespace mimus\tests {

	class Mock extends \PHPUnit\Framework\TestCase {

		public function testPublicMethodExpectAndReturnTrue() {
			$mock = \mimus\Mock::of(\mimus\tests\classes\Foo::class);

			$mock->rule("publicMethod")
				->expects(true)
				->returns(true);

			$object = $mock->getMock();

			$this->assertTrue($object->publicMethod(true));
		}

		public function testPublicMethodExpectFalseAndFalse() {
			$mock = \mimus\Mock::of(\mimus\tests\classes\Foo::class);

			$mock->rule("publicMethod")
				->expects(false)
				->returns(false);

			$object = $mock->getMock();

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

			$object = $mock->getMock();

			$this->assertFalse($object->publicMethod(true));
			$this->assertFalse($object->publicMethod(false));
		}
	}
}
