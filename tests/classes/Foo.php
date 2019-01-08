<?php
namespace mimus\tests\classes {
	class Foo implements IFooFace {

		public function publicMethod(bool $bool) {
			return $this->protectedMethod($bool);
		}

		protected function protectedMethod(bool $bool) {
			return $this->privateMethod($bool);
		}

		private function privateMethod(bool $bool) {
			return !$bool;
		}

		public static function publicStaticMethod(bool $bool) {
			return self::protectedStaticMethod($bool);
		}

		protected static function protectedStaticMethod(bool $bool) {
			return self::privateStaticMethod($bool);
		}

		private static function privateStaticMethod(bool $bool) {
			return !$bool;
		}

		final public function publicFinalMethod(bool $bool) {
			return !$bool;
		}
	}
}
?>
