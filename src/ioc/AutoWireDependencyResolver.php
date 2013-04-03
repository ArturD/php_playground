<?php
require_once("IDependencyResolver.php");
class AutoWireDependencyResolver implements IDependencyResolver {
	function __construct( IArgumentResolver $pr, /* string */ $typeName) {
		$this->parameterResolver = $pr;
		$this->typeName = $typeName;
	}

	function findConstructorArguments() {
		$args = array();
		$class = new ReflectionClass( $this->typeName );
		$constructor = $class->getConstructor();
		// TODO handle no constructor
		$params = $constructor->getParameters();
		foreach( $params as $i => $rp ) {
			$arg = new ArgumentDefinition();
			$arg->name = $arg->getName();
			$argType = $rp->getClass();
			if( $argType instanceof ReflectionClass ) {
				$arg->typeName = $argType->getName();;
			}
			$args[] = $arg;

		}
		return $args;
	}

	function activate( $args ) {
		$class = new ReflectionClass( $this->typeName );
		return $class->newInstanceArgs( $args );
	}

	function canResolve() {
		foreach( $this->findConstructorArguments() as $i => $arg ) {
			if( !$this->parameterResolver->canResolve($arg) ) {
				return false;
			}
		}
		return true;
	}

	function resolve() {
		$args = array();
		foreach( $this->findConstructorArguments() as $i => $arg ) {
			$args[] = $this->parameterResolver->resolve($arg);
		}
		return $this->activate($args);
	}
}

