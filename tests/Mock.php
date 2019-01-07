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

		public function testMockNonExistentMethodLogicException() {
			$mock = \mimus\Mock::of(\mimus\tests\classes\Bar::class);

			$this->expectException(\LogicException::class);

			$mock->rule("nonExistentMethod");
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
