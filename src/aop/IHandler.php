<?php
interface IHandler {
  function canHandle( $context );
  function handle( $context );
}
