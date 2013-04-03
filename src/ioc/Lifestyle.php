<?php
require_once("ILifestyle.php");
class Lifestyle implements ILifestyle {
	private $func;
	function __construct(callable $func) {
		$this->func = $func;
	}
	function buildManager() {
		$f = $this->func;
		return $f();
	}
}
