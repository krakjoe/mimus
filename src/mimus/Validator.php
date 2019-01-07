<?php
namespace mimus {

	interface Validator {

		public function getName() : string;

		public function validate(Path $path, object $object = null, $retval = null) : bool;
	}
}
