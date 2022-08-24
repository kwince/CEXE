<?php

namespace Cexe\Actions;

use Cexe\Utilties\FileUtilities;
use Cexe\Utilites\DiscUtilties;
use Cexe\ProjDataObjs\KeyFile;


class CheckKey {
//  -C HashFileOfKeyToBeTested  KeyFileMadeFromThisProgram
// 0 1 2                    3

  public const HASH_FILE_INDEX = 2;
  public const KEY_INDEX = 3 ;
  public const MIN_FQFN_LEN=5;

  private $file_utes;
  private $disc_utes;
  private $key_file;
  private $fp_hash_file;

    public function __construct(){
      $this->file_utes = new \Cexe\Utilities\FileUtilities();
      $this->disc_utes = new \Cexe\Utilities\DiscUtilities();
      $this->key_file = new \Cexe\ProjDataObjs\KeyFile();
    }

    function do( $arr ){
      $key_buffer="";
      $key_buffer_hash="";
      $hash_buffer="";

      //$this->key_file->simpleHashTest( $arr[ self::HASH_INDEX ] );
      $this->file_utes->readAllofBinaryFile( $arr[ self::KEY_INDEX ], $key_buffer );
      echo "Key file read in.\n";
      $this->file_utes->readAllofBinaryFile( $arr[ self::HASH_FILE_INDEX ], $hash_buffer );
      echo "Hash file read in.\n";

      $key_buffer_hash = sodium_crypto_generichash($key_buffer, "", SODIUM_CRYPTO_GENERICHASH_BYTES) ;
      $key_buffer_hash = sodium_bin2hex( $key_buffer_hash );

      echo "Hash of key file calculated.\n";

      if( 0 !== strcmp( $hash_buffer, $key_buffer_hash )){
        throw new \Exception( "The hash does not match the key. Either you have the wrong key or hash, or your keys are under attack\n\n");

      } else {
        echo("\n\n\tThe key matches the hash, the key is safe to use.\n\n");
      }
    }

}
?>
