<?php
require_once("DependencyDefinition.php");
require_once("ArgumentDefinition.php");
require_once("ArgumentResolver.php");
require_once("FuncDependencyResolver.php");
require_once("AutoWireDependencyResolver.php");
require_once("Lifestyle.php");
require_once("Lifestyles.php");
class Container {
	private $dependencies = array();
	private $finalized = false;

	function __construct() {
		$this->parameterResolver = new ArgumentResolver( $this );
	}

	function registerByFunction( /* string */ $name, callable $func, ILifestyle $lifestyle = null ) {
		$lifestyle = $lifestyle ?: Lifestyles::$singleton;
		$def = new DependencyDefinition();
		$def->name = $name;
		$def->resolver = new FuncDependencyResolver( $this, $func );
		$this->dependencies[$name][] = $def;
	}

	function registerByInstance( /* string */ $name, $instance ) {
		registerByFunction($name, function() { return $instance; } ); // better way ?
	}

	function register( /* string */ $name, /* string */ $typeName ) {
		$def = new DependencyDefinition();
		$def->name = $name;
		$def->typeName = $typeName;
		$def->resolver = new AutoWireDependencyResolver( $this->parameterResolver, $typeName );
		$this->dependencies[$name][] = $def;
	}

	private function resolvableDependencies( /* string */ $name ) {
		$deps = array();
		if( array_key_exists( $name ,$this->dependencies ) ) {
			foreach ( $this->dependencies[$name] as $i => $dep ) {
				if( $dep->resolver->canResolve() ) {
					$deps[] = $dep;
				}
			}
		}
		return $deps;
	}

	function canResolve($name) {
		return count( $this->resolvableDependencies($name) ) == 1;
	}

	function resolve($name) {
		$deps = $this->resolvableDependencies($name);
		if( count( $deps ) == 0 ) { throw new ResolutionException('Resolver for '.$name.' not found.'); }
		if( count( $deps ) > 1 ) { throw new ResolutionException('Found '.$deps->count().' resolvers for'.$name.' but expected 1.'); }
		return $deps[0]->resolver->resolve();
	}

        function resolveAll($name) {
		$deps = $this->resolvableDependencies();
		$objects = array();
		foreach( $objects as $i => $o ) {
			$deps[0]->resolver->resolve( $o );
		}
		return $objects;
	}
}

