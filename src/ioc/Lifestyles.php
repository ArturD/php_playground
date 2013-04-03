<?php
class Lifestyles {
	public static $singleton = null;
}
Lifestyles::$singleton = new Lifestyle( function() {/*todo create manager */} );

