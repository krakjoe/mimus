<?php
namespace mimus {

	class Exception extends \Exception {
		public function __construct(?Exception $except, string $message, ...$format) {
			parent::__construct(vsprintf($message, $format), 0, $except);
		}
	}
}
