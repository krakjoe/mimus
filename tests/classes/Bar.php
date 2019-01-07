<?php
namespace mimus\tests\classes {
	class Bar {

		public function publicMethodUntouched(bool $bool) {
			return $bool;
		}

		public function publicMethod(bool $bool) {
			return $bool;
		}
	}
}
?>
