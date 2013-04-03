<?php
/*
 * @author Pulni4kiya <beli4ko.debeli4ko@gmail.com>
 * @date 2009-04-20
 * @version 1.0 2009-04-20
 */

require_once 'ISimpleInvocationProxy.php';
class TargetSIP implements ISimpleInvocationProxy {
	protected $target = null;
	
	public function __construct($target) {
		if (is_object($target) == false) throw new InvalidArgumentException('The parameter must be an object!');
		$this->target = $target;
	}
	
	public function &InvokeMethod($proxy, $methodName, array $args, ChainSIP $chain) {
		$code = '@$result =& $this->target->' . $methodName . '(';
		foreach ($args AS $k => $v) {
			$code .= '&$array[' . $k . '],';
		}
		if (count($args) > 0) $code = substr($code, 0, -1);
		$code.= ');';

		eval($code);
		return $result;
	}
}
?>