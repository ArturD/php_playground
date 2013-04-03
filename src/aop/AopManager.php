<?php
require_once(dirname(__FILE__)."/../../lib/dynamic-proxy/ProxyManager.php");
require_once(dirname(__FILE__)."/AopSip.php");
require_once(dirname(__FILE__)."/FuncHandler.php");
class AopManager {
  protected $sip;
  function __construct() {
    $this->sip = new AopSip();
  }

  function build( $className, array $parameters ) {
    $proxy = ProxyManager::GetClassProxyObject($this->sip, $className, $parameters);
    return $proxy;
  }

  function registerHandler( $handler ) {
    $this->sip->registerHandler( $handler );
  }

  function registerHandlerFunc( $func ) {
    $this->registerHandler( new FuncHandler($func) );
  }
}

