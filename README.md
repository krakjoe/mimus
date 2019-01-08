mimus
=====

[![Build Status](https://travis-ci.org/krakjoe/mimus.svg?branch=master)](https://travis-ci.org/krakjoe/mimus)
[![Coverage Status](https://coveralls.io/repos/github/krakjoe/mimus/badge.svg?branch=master)](https://coveralls.io/github/krakjoe/mimus?branch=master)

Requirements
============

  * PHP 7.1+
  * [Componere](https://github.com/krakjoe/componere)

Doubles
=======

A test double is a class that takes the place of an existing class while a system is under test.

```php
<?php
require "vendor/autoload.php";

class Foo {

	public function doesSomethingAndReturnsBool() : bool {
		/** ... **/
		return true;
	}
}

$mock = \mimus\Mock::of(Foo::class);
?>
```

At this time, the definition of ```Foo``` has been replaced with a double, it has the same interface as ```Foo``` but none of the methods do anything - they have been stubbed.

Stubs
=====

To make the stubs do something, you must tell ```mimus``` what the method should, or will do:

```php
<?php
require "vendor/autoload.php";

class Foo {

	public function doesSomethingAndReturnsBool() : bool {
		/** ... **/
		return true;
	}
}

$mock = \mimus\Mock::of(Foo::class);

$mock->rule("doesSomethingAndReturnsBool")
	->expects() /* take any arguments */
	->returns(true); /* return true; */

$object = $mock->getInstance();

var_dump($object->doesSomethingAndReturnsBool()); // bool(true)
?>
```

In some cases, our method needs to return a different value for different input:

```php
<?php
require "vendor/autoload.php";

class Foo {

	public function doesSomethingAndReturnsBool($argument) : bool {
		/** ... **/
		return true;
	}
}

$mock = \mimus\Mock::of(Foo::class);

$mock->rule("doesSomethingAndReturnsBool")
	->expects(true) /* takes these arguments */
	->returns(true); /* return true; */
$mock->rule("doesSomethingAndReturnsBool")
	->expects(false) /* takes these arguments */
	->returns(false); /* return false; */

$object = $mock->getInstance();

var_dump($object->doesSomethingAndReturnsBool(true)); // bool(true)
var_dump($object->doesSomethingAndReturnsBool(false)); // bool(false)
?>
```

At this time, we have defined two valid paths through the method based on the arguments given at runtime, should the method be invoked like this:

```php
<?php
var_dump($object->doesSomethingAndReturnsBool("mimus"));
```

mimus will raise ```\mimus\Exception``` for each rule that has been broken (2).

Paths
=====

A path may:

  * expect (or set) a return value (previous examples)
  * execute original implementation
  * execute different implementation
  * expect an exception
  * expect to be entered a maximum number of times (or never)

Execute Original Implementation
-------------------------------

Suppose we want to allow the original implementation to execute, and to ensure that the return value is as expected:

```php
<?php
require "vendor/autoload.php";

class Foo {

	public function doesSomethingAndReturnsBool($arg) : bool {
		/** ... **/
		return true;
	}
}

$mock = \mimus\Mock::of(Foo::class);

$mock->rule("doesSomethingAndReturnsBool")
	->expects("yes")
	->executes() // executes original
	->returns(true);
$mock->rule("doesSomethingAndReturnsBool")
	->expects("no")
	->executes() // executes original
	->returns(false);

$object = $mock->getInstance();

var_dump($object->doesSomethingAndReturnsBool("yes")); // bool(true)
var_dump($object->doesSomethingAndReturnsBool("no"));
?>
```

While the first call will succeed, the second will raise ```\mimus\Exception: return value expected to be bool(false), got bool(true)```.


Execute Different Implementation
--------------------------------

Suppose we want to execute a different implementation in place of the original:

```php
<?php
require "vendor/autoload.php";

class Foo {

	public function doesSomethingAndReturnsBool($arg) : bool {
		/** ... **/
		return true;
	}
}

$mock = \mimus\Mock::of(Foo::class);

$mock->rule("doesSomethingAndReturnsBool")
	->expects("yes")
	->executes() // executes original code
	->returns(true);
$mock->rule("doesSomethingAndReturnsBool")
	->expects("no")
	->executes(function(){
		return false;
	}); // no need for returns()

$object = $mock->getInstance();

var_dump($object->doesSomethingAndReturnsBool("yes")); // bool(true)
var_dump($object->doesSomethingAndReturnsBool("no"));  // bool(false)
?>
```

While the first call will invoke the original implementation, the second will invoke the given implementation.

Exceptions
----------

Suppose we want to verify that a Path throws an exception:

```php
<?php
require "vendor/autoload.php";

class Foo {

	public function doesSomethingAndReturnsBool($arg) : bool {
		if ($arg) {
			throw new Exception();
		}
		return true;
	}
}

$mock = \mimus\Mock::of(Foo::class);

$mock->rule("doesSomethingAndReturnsBool")
	->expects(true)
	->executes()
	->throws(Exception::class);
$mock->rule("doesSomethingAndReturnsBool")
	->expects(false)
	->executes()
	->throws(Exception::class);

$object = $mock->getInstance();

try {
	$object->doesSomethingAndReturnsBool(true);
} catch (Exception $ex) {
	
}

$object->doesSomethingAndReturnsBool(false);
?>
```

While the first call will succeed and the resulting exception caught, the second will raise (uncaught): ```mimus\Exception: expected exception of type Exception, nothing thrown```.

Limits
------

Suppose we want to limit the number of times a method is entered:

```php
<?php
require "vendor/autoload.php";

class Foo {

	public function doesSomethingAndReturnsBool() : bool {
		/* ... */
		return true;
	}
}

$mock = \mimus\Mock::of(Foo::class);

$mock->rule("doesSomethingAndReturnsBool")
	->expects(true)
	->returns(true)
	->once(); // limit() and never() also available

$object = $mock->getInstance();

var_dump($object->doesSomethingAndReturnsBool(true)); // bool(true)
var_dump($object->doesSomethingAndReturnsBool(true));
?>
```

While the first call will succeed, the second will raise: ```mimus\Exception: limit of 1 exceeded```.

API
===

```php
<?php
namespace mimus {

	class Mock {
		/*
		* Shall create or return Mock builder for $class
		* @param string the name of the class to mock
		* @param string optionally prohibit resetting rules
		* @throws LogicException if class does not exist
		*/
		public static function of(string $class, bool $reset = true);
		
		/*
		* Shall turn this Mock into a Partial Mock by allowing execution of the given methods
		*/
		public function partialize(array $methods = []);
		
		/*
		* Shall turn this Mock into a Partial Mock by allowing execution of the methods in the given class
		*/
		public function partialize(string $class);

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

TODO
====

  * more tests would be nice ...
  * I've always wanted to meet a polar bear ...
