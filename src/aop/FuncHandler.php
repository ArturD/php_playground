<?php
require_once("IHandler.php");
class FuncHandler implements IHandler  {
  private $handler;
  function __construct( $handler ) {
    $this->handler = $handler;
  }

  function canHandle( $context ) {
    return true;
  }

  function handle( $context ) {
    $f = $this->handler;
    return $f( $context );
  }
}
