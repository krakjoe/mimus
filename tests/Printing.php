<?php
namespace mimus\tests {
	use \mimus\Double as double;

	class Printing extends \PHPUnit\Framework\TestCase {

		public function testPrintInt() {
			$mock = double::class(\mimus\tests\classes\Foo::class);

			$mock->rule("publicMethod")
				->expects(42);

			$object = $mock->getInstance();

			$this->expectException(\mimus\Exception::class);
			$this->assertNull($object->publicMethod(43));
		}

		public function testPrintDouble() {
			$mock = double::class(\mimus\tests\classes\Foo::class);

			$mock->rule("publicMethod")
				->expects(4.2);

			$object = $mock->getInstance();

			$this->expectException(\mimus\Exception::class);
			$this->assertNull($object->publicMethod(4.3));
		}

		public function testPrintString() {
			$mock = double::class(\mimus\tests\classes\Foo::class);

			$mock->rule("publicMethod")
				->expects("hello");

			$object = $mock->getInstance();

			$this->expectException(\mimus\Exception::class);
			$this->assertNull($object->publicMethod("world"));
		}

		public function testPrintArray() {
			$mock = double::class(\mimus\tests\classes\Foo::class);

			$mock->rule("publicMethod")
				->expects([1,2,3]);

			$object = $mock->getInstance();

			$this->expectException(\mimus\Exception::class);
			$this->assertNull($object->publicMethod([4,5,6]));
		}
	}
}
