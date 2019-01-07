<?php
namespace mimus\tests {

	class Rule extends \PHPUnit\Framework\TestCase {

		public function testExpectationAlreadySet() {
			$mock = \mimus\Mock::of(\mimus\tests\classes\Foo::class);

			$this->expectException(\LogicException::class);

			$mock->rule("publicMethod")
				->expects(true)
				->expects(true);
		}
	}
}
