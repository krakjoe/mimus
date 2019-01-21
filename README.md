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

A test double is an object that takes the place of an object of a formal type while a system is under test:

```php
<?php
require "vendor/autoload.php";

use \mimus\Double as double;

class Foo {

	public function doesSomethingAndReturnsBool() : bool {
		/** ... **/
		return true;
	}
}

$builder = double::class(Foo::class);

$object = $builder->getInstance();
?>
```

At this time, the definition of ```Foo``` has been replaced with a mock, it has the same interface as declaration ```Foo``` but none of the methods do anything - they have been stubbed: Subsequent calls to ```new Foo``` will create a test double, and ```$object``` is ```instanceof Foo```.

Stubs
=====

To make the stubs do something, you must tell ```mimus``` what the method should, or will do:

```php
<?php
require "vendor/autoload.php";

use \mimus\Double as double;

class Foo {

	public function doesSomethingAndReturnsBool() : bool {
		/** ... **/
		return true;
	}
}

$builder = double::class(Foo::class);

$builder->rule("doesSomethingAndReturnsBool")
	->expects() /* take any arguments */
	->returns(true); /* return true; */

$object = $builder->getInstance();

var_dump($object->doesSomethingAndReturnsBool()); // bool(true)
?>
```

In some cases, our method needs to return a different value for different input:

```php
<?php
require "vendor/autoload.php";

use \mimus\Double as double;

class Foo {

	public function doesSomethingAndReturnsBool($argument) : bool {
		/** ... **/
		return true;
	}
}

$builder = double::class(Foo::class);

$builder->rule("doesSomethingAndReturnsBool")
	->expects(true) /* takes these arguments */
	->returns(true); /* return true; */
$builder->rule("doesSomethingAndReturnsBool")
	->expects(false) /* takes these arguments */
	->returns(false); /* return false; */

$object = $builder->getInstance();

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

use \mimus\Double as double;

class Foo {

	public function doesSomethingAndReturnsBool($arg) : bool {
		/** ... **/
		return true;
	}
}

$builder = double::class(Foo::class);

$builder->rule("doesSomethingAndReturnsBool")
	->expects("yes")
	->executes() // executes original
	->returns(true);
$builder->rule("doesSomethingAndReturnsBool")
	->expects("no")
	->executes() // executes original
	->returns(false);

$object = $builder->getInstance();

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

use \mimus\Double as double;

class Foo {

	public function doesSomethingAndReturnsBool($arg) : bool {
		/** ... **/
		return true;
	}
}

$builder = double::class(Foo::class);

$builder->rule("doesSomethingAndReturnsBool")
	->expects("yes")
	->executes() // executes original code
	->returns(true);
$builder->rule("doesSomethingAndReturnsBool")
	->expects("no")
	->executes(function(){
		return false;
	}); // no need for returns()

$object = $builder->getInstance();

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

use \mimus\Double as double;

class Foo {

	public function doesSomethingAndReturnsBool($arg) : bool {
		if ($arg) {
			throw new Exception();
		}
		return true;
	}
}

$builder = double::class(Foo::class);

$builder->rule("doesSomethingAndReturnsBool")
	->expects(true)
	->executes()
	->throws(Exception::class);
$builder->rule("doesSomethingAndReturnsBool")
	->expects(false)
	->executes()
	->throws(Exception::class);

$object = $builder->getInstance();

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

use \mimus\Double as double;

class Foo {

	public function doesSomethingAndReturnsBool() : bool {
		/* ... */
		return true;
	}
}

$builder = double::class(Foo::class);

$builder->rule("doesSomethingAndReturnsBool")
	->expects(true)
	->returns(true)
	->once(); // limit() and never() also available

$object = $builder->getInstance();

var_dump($object->doesSomethingAndReturnsBool(true)); // bool(true)
var_dump($object->doesSomethingAndReturnsBool(true));
?>
```

While the first call will succeed, the second will raise: ```mimus\Exception: limit of 1 exceeded```.

Partial Mocks
=============

Partial mocks are used, for example, to allow an object of a mocked type to execute an interface as implemented:

```php
<?php
require "vendor/autoload.php";

use \mimus\Double as double;

interface IFace {
	public function interfaceMethod();
}

class Foo implements IFace {

	public function interfaceMethod() {
		return true;	
	}

	public function nonInterfaceMethod() {
		return false;
	}
}

$builder = double::class(Foo::class);
$builder->partialize([
	"interfaceMethod"
]);
$builder->rule("nonInterfaceMethod")
	->expects()
	->never();

$object = $builder->getInstance();

var_dump($object->interfaceMethod());    // bool(true)
var_dump($object->nonInterfaceMethod());
```

While the first call will be executed as implemented, the second will raise ```mimus\Exception: limit of 1 exceeded```.

```double::partialize``` also accepts the name of a valid class, the call above could be written:

```php
/* ... */
$builder->partialize(IFace::class);
/* ... */
```

Interfaces
==========

It is sometimes useful to mock an interface without an implementation, we can use a test double for this:

```php
<?php
require "vendor/autoload.php";

use mimus\Double as double;

interface IFace {
	public function publicMethod();
}

$builder = double::make(myinterfaces::class, [
	[
		IFace::class
	]
]);

$builder->rule("publicMethod")
	->expects()
	->executes(function(){
		return true;
	});

$object = $builder->getInstance();

var_dump($object->publicMethod());  // bool(true)
```

The ```$object``` will be ```instanceof IFace``` with the name ```myinterfaces```.

The method ```Double::implements``` can be used to add an interface to a double after construction.

Traits
======

Traits are treated like copy-pastable units of code by the compiler; When there is a ```use``` in a class declaration
the interface of the trait is pasted into the current declaration such that the declarations inline will overwrite the
declarations in the trait.

For mocks, we wants to use traits a little differently: We want to paste on top of the class declaration so that the trait
becomes the source of truth for implementations.

```php
<?php
require "vendor/autoload.php";

use \mimus\Double as double;

class Foo {

	public function doesSomethingAndReturnsBool() : bool {
		/** ... **/
		return true;
	}
}

trait FooDoubleMethods {
	public function doesSomethingAndReturnsBool() : bool {
		return false;
	}
}

$builder = double::class(Foo::class);
$builder->use(FooDoubleMethods::class);

$builder->rule("doesSomethingAndReturnsBool")
	->expects()
	->executes();

$object = $builder->getInstance();

var_dump($object->doesSomethingAndReturnsBool()); // bool(false)
?>
```

*Note that ```use``` does not imply that the double should be partialized.*

Life Cycle of a Double
======================

The named constructors ```Double::class``` and ```Double::make``` will try to return a cached double based on the ```$name``` passed to the constructor, they may optionally ```$reset``` the double as they retrieve it.

From the first call to ```Double::getInstance``` or ```Double::rule``` the class exists in the engine with exactly the ```$name``` given; Certain actions such as implementing interfaces and using traits are no longer possible and must be performed previously to these calls taking place.

The class remains present until it is explicitly removed with ```Double::unlink```: When a double is removed any class which it replaced is restored to it's original implementation.

API
===

```php
<?php
namespace mimus {

	class Double {
		/*
		* Shall create or return mock by name
		* @param string the name of the class to mock
		* @param bool optionally prohibit resetting rules
		* @throws LogicException if name does not exist
		* @throws LogicException if name is the name of an abstract class
		*/
		public static function class(string $name, bool $reset = true) : Double;

		/*
		* Shall create or return mock by name
		* @see \Componere\Definition::__construct
		*/
		public static function make(string $name, mixed $args, bool $reset = true) : Double;

		/*
		* Shall delete a mock by name
		* @param name of mock
		* @throws LogicException if mock does not exist
		*/
		public static function unlink(string $name) : void;

		/*
		* Shall check if a mock exists
		* @param name of mock
		*/
		public static function exists(string $name) : bool;

		/*
		* Shall delete all mocks
		*/
		public static function clear() : void;

		/*
		* Shall implement the given interface
		* @param name of interface
		* @param optionally partialize on interface
		* @throws LogicException if invoked after rule() or getInstance()
		* @throws LogicException if not a valid interface
		*/
		public function implements(string $interface, bool $partialize = false) : Double;

		/*
		* Shall use the given trait
		* @param name of trait
		* @param optionally partialize on trait
		* @throws LogicException if invoked after rule() or getInstance()
		* @throws LogicException if not a valid trait
		*/
		public function use(string $interface, bool $partialize = false) : Double;

		/*
		* Shall turn this into a partial by allowing execution of the given methods
		*/
		public function partialize(array $methods = []) : Double;
		
		/*
		* Shall turn this into a partial by allowing execution 
		*	of the methods in the given class
		*/
		public function partialize(string $class) : Double;

		/*
		* Shall turn this into a partial by allowing execution 
		*	of the methods in the given class with exceptions
		*/
		public function partialize(string $class, array $except = []) : Double;

		/*
		* Shall ensure the class is available by name
		* Note: until the first call to rule() or getInstance() the class is not registered
		*	this method serves the case where no rule() or getInstance() call is made
		*	in the current scope.
		*/
		public function commit() : void;

		/*
		* Shall create a new Rule for method
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
		* Note: Closure should be function(Closure $prototype, ...$args)
		*	Closure is bound to the correct scope before invocation
		*	If Path::executes is not invoked, nothing will be executed for this Path
		*/
		public function executes(Closure $closure = null) : Path;
		/*
		* Shall tell mimus what this path should (or will) return
		* @param mixed
		*	If this path executes, then the return value given is verified to
		*	match the runtime return value.
		*	If this path does not execute, the return value is used as the
		*	runtime return value.
		* @throws LogicException if this Path is void
		* Note: If Path::returns is not invoked, any return is allowed for this Path
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
		* @throws LogicException for non executable Path
		* Note: If Path::throws is not invoked, any exception is allowed for this Path
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
		*	Validators will be bound to the correct object before invocation
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
