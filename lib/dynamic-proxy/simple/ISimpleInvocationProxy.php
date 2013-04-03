<?php
/*
 * @author Pulni4kiya <beli4ko.debeli4ko@gmail.com>
 * @date 2009-04-20
 * @version 1.0 2009-04-20
 */

interface ISimpleInvocationProxy {
	public function &InvokeMethod($proxy, $methodName, array $args, ChainSIP $chain);
}

class ChainSIP implements ISimpleInvocationProxy {
	private $sips = array();
	private $next = 0;
	private $chain;
	
	public function __construct() {
		$this->chain = new NullChainSIP();
		$args = func_get_args();
		foreach ($args AS $arg) {
			if ($arg instanceof ISimpleInvocationProxy) {
				$this->sips[] = $arg;
			}
		}
	}
	
	public function AddFirst(ISimpleInvocationProxy $sip) {
		array_unshift($this->sips, $sip);
	}
	
	public function AddLast(ISimpleInvocationProxy $sip) {
		$this->sips[] = $sip;
	}
	
	public function &InvokeNext($proxy, $methodName, array $args) {
		$next =& $this->next;
		if (empty($this->sips[$next])) {
			return $this->chain->InvokeNext($proxy, $methodName, $args);
		}
		
		$sip = $this->sips[$next];
		
		$next++;
		
		return $sip->InvokeMethod($proxy, $methodName, $args, $this);
	}
	
	public function &InvokeMethod($proxy, $methodName, array $args, ChainSIP $chain) {
		$this->chain = $chain;
		$this->next = 0;
		return $this->InvokeNext($proxy, $methodName, $args);
	}
}

class NullChainSIP extends ChainSIP {
	public function __construct() {	}
	
	public function AddFirst($sip) { }

	public function AddLast($sip) { }

	public function &InvokeMethod($proxy, $methodName, array $args, ChainSIP $chain) {
		$a = null;
		return $a;
	}

	public function &InvokeNext($proxy, $methodName, array $args) {
		$a = null;
		return $a;
	}
}
?>