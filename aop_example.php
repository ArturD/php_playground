<?php
require_once("services.php");
require_once("src/aop/AopManager.php");

$manager = new AopManager();
$indent = 0;
$manager->registerHandlerFunc( function( $context ) use (&$indent) {
  for( $i = 0; $i < $indent*4; $i++) print " ";
  print "before: " . $context->methodName . "\n";
  $indent++;

  $start = microtime(true);

  $context->proceed();

  $end = microtime(true);

  $indent--;
  for( $i = 0; $i < $indent*4; $i++) print " ";
  print "after: " . $context->methodName . "(" . ($end-$start) . "s)\n";
} );

$dep = $manager->build( "SomeDep" , array() );
$service = $manager->build( "SomeService", array( $dep ) );
$service->hello();

