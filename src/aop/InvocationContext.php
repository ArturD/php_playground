<?php
class InvocationContext {
  public $proxy;
  public $methodName;
  public $args;
  private $handlers;
  private $handerNo;
  function __construct( $proxy, $methodName, $args, $handlers, $chain ) {
    $this->proxy = $proxy;
    $this->methodName = $methodName;
    $this->args = $args;
    $this->handlers = $handlers;
    $this->handlerNo = 0;
    $this->chain = $chain;
  }

  public function proceed() {
    while( $this->handlerNo < count($this->handlers) ) {
      $this->handlerNo += 1;
      if ( $this->handlers[$this->handlerNo-1]->canHandle( $this ) ) {
        return $this->handlers[$this->handlerNo-1]->handle( $this );
      }
    }
    if( $this->handlerNo == count($this->handlers) ) {
      $this->handlerNo += 1;
      return $this->chain->InvokeNext($this->proxy, $this->methodName, $this->args);
    }
  }
}
