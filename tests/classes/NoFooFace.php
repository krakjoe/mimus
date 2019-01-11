<?php
namespace mimus\tests\classes {
	class NoFooFace {

		public function publicMethod(bool $bool) {
			return !$bool;
		}
	}
}
?>
