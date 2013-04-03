<?php

class SomeDep {
	function __construct() {
	}
	function hello() {
		echo "hello world \n";
	}
}

class SomeService {
	private $someDep;
	function __construct( $someDep ) {
		$this->someDep = $someDep;
	}

	function hello() {
		$this->someDep->hello();
	}
}
