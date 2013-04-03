<?php
interface IArgumentResolver {
	function canResolve(ArgumentDefinition $propertyDefinition);
	function resolve(ArgumentDefinition $propertyDefinition);
}
