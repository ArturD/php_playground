<?php
/*
 * @author Pulni4kiya <beli4ko.debeli4ko@gmail.com>
 * @date 2009-04-20
 * @version 1.0 2009-04-20
 */

require_once 'ISimpleInvocationProxy.php';
class ClassSIP implements ISimpleInvocationProxy {
	public function __construct() {}
	
	public function &InvokeMethod($proxy, $methodName, array $args, ChainSIP $chain) {
		$callParentMethod = ProxyManager::CALL_PARENT_METHOD_NAME;
		$result =& $proxy->$callParentMethod($methodName, $args);
		return $result;
	}
}
?>