<?php
namespace mimus\tests {
	use \mimus\Double as double;

	class Rule extends \PHPUnit\Framework\TestCase {

		public function testExpectationAlreadySet() {
			$builder = double::class(\mimus\tests\classes\Foo::class);

			$rule = $builder->rule("publicMethod");
			
			$rule->expects();

			$this->expectException(\mimus\Exception::class);

			$rule->expects();
		}
	}
}
