<?php
namespace mimus\tests\classes {
	class NoFooFacePartial {

		public function partialized(bool $bool) {
			return !$bool;
		}

		public function notPartialized(bool $bool) {
			return !$bool;
		}
	}
}
?>
