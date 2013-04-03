<?php
require_once(dirname(__FILE__)."/../../lib/dynamic-proxy/simple/ISimpleInvocationProxy.php");
require_once(dirname(__FILE__)."/InvocationContext.php");
class AopSip implements ISimpleInvocationProxy {
  private $handlers = array();
  public function &InvokeMethod($proxy, $methodName, array $args, ChainSIP $chain) {
    $context = new InvocationContext($proxy
        , $methodName
        , $args
        , $this->handlers
        , $chain );
    $res =& $context->proceed();
    return $res;
  }
  public function registerHandler( $handler ) {
    $this->handlers[] = $handler;
  }
}
