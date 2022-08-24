<?php
namespace Cexe;
require __DIR__ . '/vendor/autoload.php';
const CEXE_VERSION = "00.10.00";
const SALT = "jf#i3q!SQ4U7qL3*4CthqK@gpyf.E%tyrXfi-u3F,q8+y:Tq43=lbb";

//Exceptions are not caught to give a trace of execution path during failures
  $qa=new \Cexe\QualifyActionArgument();
  $classname= $qa->getActionClass( $_SERVER['argv'][1] );
  $obj = new $classname();
  $obj->do($argv);

?>
