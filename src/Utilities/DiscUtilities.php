<?php

namespace Cexe\Utilities;

class DiscUtilities{

  public const SPARE_DISC_SPACE_FOR_WRITE= 2**22;
  public function __construct(){
    ;
  }

  public function checkDiscFreeSpace( $path, $room_requested){
    if( disk_free_space( realpath($path) ) < ($room_requested + self::SPARE_DISC_SPACE_FOR_WRITE) ){
        throw new \Exception( "\nNot enough room in destination directory/disk: " . $path . " . This is an error at line ".__LINE__." in function " . __FUNCTION__ . "in class " . __CLASS__ . "\n");
    }
  }



}

?>
