<?php
require_once("IDependencyResolver.php");
class FuncDependencyResolver implements IDependencyResolver {
	function __construct(Container $c, callable $func) {
		$this->container = $c;
		$this->func = $func;
	}

	function canResolve() {
		return true;
	}

	function resolve() {
		$f = $this->func;
		return $f($this->container);
	}
}
