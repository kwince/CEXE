<?php

namespace Cexe;

class QualifyActionArgument{

  private $default_action="-H";
  private $actions = array(
    "-U" => "Cexe\Actions\UnPacker",
    "-P" => "Cexe\Actions\Packer",
    "-H" => "Cexe\Actions\Helper",
    "-K" => "Cexe\Actions\KeyMaker",
    "-C" => "Cexe\Actions\CheckKey"

  );
  public function __construct(){
    ;
  }

  public function getActionClass( $action_str ){
    $action_class = $this->actions[ $action_str ];
    if( is_null ($action_class) || !isset($action_class) ){
      $action_class = $this->actions[  $this->default_action ];
    }
    return $action_class;
  }
}
?>
