<?php
namespace mimus\tests\classes {
	class FooFace implements IFooFace {

		public function publicMethod(bool $bool) {
			return !$bool;
		}
	}
}
?>
