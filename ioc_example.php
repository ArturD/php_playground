<?php
require_once("services.php");
require_once("src/ioc/Container.php");
// TODO: implement lifestyle
// TODO: reslove circular deps
// TODO: lots of other thinks. It's just prototype.
//////// EXAMPLE /////////////

echo " -- \n";

function FuncExample() {
	$c = new Container();

	$c->registerByFunction( "someDep", function($c) { return new SomeDep(); } );
	$c->registerByFunction( "someService", function($c) { return new SomeService( $c->resolve('someDep') ); } );

	$service = $c->resolve("someService");
	$service->hello();
}
FuncExample();

function AutowireExample() {
	$c = new Container();

	// rename to autowire ?
	$c->register( "someDep", "SomeDep" );
	$c->register( "someService", "SomeDep" );

	$service = $c->resolve("someService");
	$service->hello();
}
AutowireExample();

