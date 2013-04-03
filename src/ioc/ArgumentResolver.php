<?php
require_once("IArgumentResolver.php");
class ArgumentResolver implements IArgumentResolver {
	private $container;

	function __construct( $c ) {
		$this->container = $c;
	}

	function canResolve(ArgumentDefinition $propertyDefinition) {
		return $this->container->canResolve( $propertyDefinition->name );
	}

	function resolve(ArgumentDefinition $propertyDefinition) {
		return $this->container->resolve( $propertyDefinition->name );
	}
}

