mimus
=====
*mocking*

mimus relies on [Componere](https://github.com/krakjoe/componere) to provide mocking facilities for testing.

API
===

```
namespace mimus {

	class Mock {
		/*
		* Shall create or return Mock builder for $class
		* @param string the name of the class to mock
		* @param string optionally prohibit resetting rules
		*/
		public static function of(string $class, bool $reset = true);

		/*
		* Shall create a new Rule for method in this builder
		* @param string the name of the method
		*/
		public function rule(string $method) : Rule;

		/*
		* Shall return an object of the mocked type
		* @param bool controls registration of the class
		*/
		public function getMock() : object;

		/*
		* Shall return an object of the mocked type, having invoked it's constructor
		* @param bool controls registration of the class
		*/
		public function getMockConstructed() : object;
	}

	class Rule {
		/*
		* Shall return the path for the given arguments, or any arguments if none are given
		*/
		public function expects(...$args) : Path;		
	}

	class Path {
		/*
		* Shall tell mimus to execute something for this path
		* @param Closure
		* 	If no Closure is passed, the original method is allowed to execute
		*	If a Closure is passed, it is executed in place of the original method
		* Note: if this method is not called, nothing is executed for this path
		*/
		public function executes(Closure $closure = null) : Path;
		/*
		* Shall tell mimus what this path should (or will) return
		* @param mixed
		*	If this path executes, then the return value given is verified to
		*	match the runtime return value.
		*	If this path does not execute, the return value is used as the
		*	runtime return value.
		*/
		public function returns($value) : Path;
		/*
		* Shall tell mimus that this path should be void (not return anything)
		*/
		public function void() : Path;
		/*
		* Shall tell mimus what this path should throw
		* @param string the name of the exception expected
		*/
		public function throws(string $class) : Path;
	}
}
```

Hello World
===========

```
<?php
require_once("vendor/autoload.php");

class Foo {
	public function method() {
		return false;
	}
}

class Test extends \PHPUnit\Framework\TestCase {
	
	public function testHelloWorldStubbed() {
		$mock = \mimus\Mock::of(Foo::class);

		$mock->rule("method")
			->expects(true)
			->returns(true);

		$object = $mock->getMock();

		$this->assertTrue($object->method(true));
	}

	public function testHelloWorldExecutes() {
		$mock = \mimus\Mock::of(Foo::class);

		$mock->rule("method")
			->expects(false)
			->executes();

		$object = $mock->getMock();

		$this->assertFalse($object->method(false));
	}

	public function testHelloWorldExecutesStubbed() {
		$mock = \mimus\Mock::of(Foo::class);

		$mock->rule("method")
			->expects(true)
			->executes(function(){
			return "mimus";
		});

		$object = $mock->getMock();

		$this->assertSame("mimus", $object->method(true));
	}
}
?>

```

TODO
====

  * Everything ...

__PLEASE DO NOT USE THIS YET, WIP!!__
