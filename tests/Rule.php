<?php
namespace mimus\tests {
	use \mimus\Double as double;

	class Rule extends \PHPUnit\Framework\TestCase {

		public function testExpectationAlreadySet() {
			$mock = double::class(\mimus\tests\classes\Foo::class);

			$rule = $mock->rule("publicMethod");
			
			$rule->expects();

			$this->expectException(\mimus\Exception::class);

			$rule->expects();
		}
	}
}
