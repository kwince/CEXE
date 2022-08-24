<?php

namespace Cexe\Actions;

use Cexe\ProjDataObjs\KeyFile;


class KeyMaker{
//  -K FileNameForDestination
// 0 1 2

  public function __construct(){
    ;
  }

  public const DESTINATION_FILE_INDEX = 2;

  public function do( $arr ) {
    $key_file = new KeyFile();
    $key_file->makeKeyFile($arr[self::DESTINATION_FILE_INDEX]);
  }
}
?>
