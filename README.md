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
		* @throws LogicException if the method does not exist
		*/
		public function rule(string $method) : Rule;

		/*
		* Shall return an object of the mocked type
		*/
		public function getMock() : object;

		/*
		* Shall return an object of the mocked type, having invoked it's constructor
		* @param args to pass to constructor
		*/
		public function getMockConstructed(...$args) : object;

		/*
		* Shall clear all the rules for the given method
		* @param string the name of a method, or null
		* Note: if method is null, rules for all methods are reset
		*/
		public function reset(string $method = null);
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
		* Note: If Path::executes is not invoked, nothing will be executed for this Path
		*/
		public function executes(Closure $closure = null) : Path;
		/*
		* Shall tell mimus what this path should (or will) return
		* @param mixed
		*	If this path executes, then the return value given is verified to
		*	match the runtime return value.
		*	If this path does not execute, the return value is used as the
		*	runtime return value.
		* Note: If Path::returns is not invoked, any return is allowed for this Path
		* @throws LogicException if this Path is void
		*/
		public function returns($value) : Path;
		/*
		* Shall tell mimus that this path should be void (not return anything)
		* @throws LogicException if this Path returns
		*/
		public function void() : Path;
		/*
		* Shall tell mimus what this path should throw
		* @param string the name of the exception expected
		* Note: If Path::throws is not invoked, any exception is allowed for this Path
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
