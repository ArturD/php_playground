<?php
interface IContainer {
	function resolve(/* string */ $name);
	function resolveAll(/* string */ $name);
}

