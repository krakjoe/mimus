mimus
=====
*mocking*

[![Build Status](https://travis-ci.org/krakjoe/mimus.svg?branch=master)](https://travis-ci.org/krakjoe/mimus)
[![Coverage Status](https://coveralls.io/repos/github/krakjoe/mimus/badge.svg?branch=master)](https://coveralls.io/github/krakjoe/mimus?branch=master)

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
		* @param array whitelist of method names to execute as implemented (results in partial mock)
		* @throws LogicException if class does not exist
		*/
		public static function of(string $class, bool $reset = true, array $whitelist = []);

		/*
		* Shall create a new Rule for method in this builder
		* @param string the name of the method
		* @throws LogicException if the method does not exist
		*/
		public function rule(string $method) : Rule;

		/*
		* Shall clear all the rules for the given method
		* @param string the name of a method, or null
		* Note: if method is null, rules for all methods are reset
		*/
		public function reset(string $method = null);

		/*
		* Shall return an object of the mocked type
		* Note: if not arguments are passed, no constructor is invoked
		*/
		public function getInstance(...$args) : object;
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
		* @throws LogicException for non executable Path
		*/
		public function throws(string $class) : Path;

		/*
		* Shall tell mimus that this path should never be travelled
		*/
		public function never() : Path;
		
		/*
		* Shall tell mimus that this path should only be travelled once
		*/
		public function once() : Path;

		/*
		* Shall tell mimus that this path should be travelled a maximum number of times
		*/
		public function limit(int $times) : Path;

		/*
		* Shall tell mimus to add a validator to Path
		* Note: Validators will be executed after all other conditions before returning,
		*	Validators will be bound to the correct object before invokation
		*	Validators that return false will raise exceptions
		*	Validators should have the prototype function($retval = null)
		*/
		public function validates(\Closure $validator) : Path;
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

		$object = $mock->getInstance();

		$this->assertTrue($object->method(true));
	}

	public function testHelloWorldExecutes() {
		$mock = \mimus\Mock::of(Foo::class);

		$mock->rule("method")
			->expects(false)
			->executes();

		$object = $mock->getInstance();

		$this->assertFalse($object->method(false));
	}

	public function testHelloWorldExecutesStubbed() {
		$mock = \mimus\Mock::of(Foo::class);

		$mock->rule("method")
			->expects(true)
			->executes(function(){
			return "mimus";
		});

		$object = $mock->getInstance();

		$this->assertSame("mimus", $object->method(true));
	}
}
?>

```

TODO
====

  * Everything ...

__PLEASE DO NOT USE THIS YET, WIP!!__
