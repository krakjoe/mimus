<?php
namespace mimus\tests\classes {
	class Qux {

		public function __construct(bool $arg) {
			throw new \Error();
		}
	}
}
?>
