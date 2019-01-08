<?php
namespace mimus\tests {
	use \mimus\Double as double;

	class Printing extends \PHPUnit\Framework\TestCase {

		public function testPrintInt() {
			$builder = double::class(\mimus\tests\classes\Foo::class);

			$builder->rule("publicMethod")
				->expects(42);

			$object = $builder->getInstance();

			$this->expectException(\mimus\Exception::class);
			$this->assertNull($object->publicMethod(43));
		}

		public function testPrintDouble() {
			$builder = double::class(\mimus\tests\classes\Foo::class);

			$builder->rule("publicMethod")
				->expects(4.2);

			$object = $builder->getInstance();

			$this->expectException(\mimus\Exception::class);
			$this->assertNull($object->publicMethod(4.3));
		}

		public function testPrintString() {
			$builder = double::class(\mimus\tests\classes\Foo::class);

			$builder->rule("publicMethod")
				->expects("hello");

			$object = $builder->getInstance();

			$this->expectException(\mimus\Exception::class);
			$this->assertNull($object->publicMethod("world"));
		}

		public function testPrintArray() {
			$builder = double::class(\mimus\tests\classes\Foo::class);

			$builder->rule("publicMethod")
				->expects([1,2,3]);

			$object = $builder->getInstance();

			$this->expectException(\mimus\Exception::class);
			$this->assertNull($object->publicMethod([4,5,6]));
		}
	}
}
