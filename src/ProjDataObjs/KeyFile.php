<?php

namespace Cexe\ProjDataObjs;

use Cexe\Utilties\FileUtilities;
use Cexe\Utilites\DiscUtilties;

class KeyFile{
  /* create array of 256  bytes
     concantenate 2^24 + 2^12 + 2^6 + 2^3 +3 sets of the $array
     randomize the contests of the array
     write it to a file
   */

   public const KEY_FILE_SECTOR_SIZE=(2**8); //DO NOT CHANGE THIS -> So that every possiblity in a byte is equally represented
   public const RAW_KEY_FILE_SECTORS=(2**24);
   public const KEY_FILE_SIZE = 2**32; // (2**8) * (2**24)
   public const MIN_FQFN_LEN=5;
   public const HASH_FILE_SUFFIX=".hash";

   private $key;
   private $hash;
   private $file_utes;
   private $disc_utes;
   private $key_file_name;
   private $key_hash_file_name;
   private $fp_key_file;
   private $fp_key_hash_file;
   private $fully_buffered;
   //-------------------------------------------------------------------------------------------------------------------
   //-------------------------------------------------------------------------------------------------------------------
  public function __construct( $FQFN=NULL, $optional_hash_file=NULL, $hold_in_memory=false ){
    $this->file_utes = new \Cexe\Utilities\FileUtilities();
    $this->disc_utes = new \Cexe\Utilities\DiscUtilities();

    if( isset($FQFN) && !is_null( $FQFN ) ){

      if( is_null( $optional_hash_file ) ){
        ;
      } else {
        $this->file_utes->testInputFileName( $optional_hash_file);
      }

      if( !isset($hold_in_memory) || is_null( $hold_in_memory ) || !is_bool($hold_in_memory) ){
        throw new \Exception( "\nInvalid parameter 'hold_in_memory' at line ".__LINE__." in function " . __FUNCTION__ . "in class " . __CLASS__ . "\n");
      }
      $this->fully_buffered=$hold_in_memory;

      if (file_exists( $FQFN ) ){
        $this->testAndLoadFromExistingFile( $FQFN, $optional_hash_file);

        if( !$hold_in_memory ){
          unset( $this->key );
          echo("Key file object initialized with key file POINTER to disc file\n");
        } else {
          echo("Key file object initialized with key file DATA from disc file\n");
        }
      } elseif(!is_null( $optional_hash_file ) ){
        throw new \Exception( "\n'optional_hash_file' name supplied, but matching 'key_file' does not exist at line ".__LINE__.
          " in function " . __FUNCTION__ . "in class " . __CLASS__ . "\n");

      } else {
        $this->makeKeyFile( $FQFN, $hold_in_memory);
      }
    }
  }
  //-------------------------------------------------------------------------------------------------------------------
  //-------------------------------------------------------------------------------------------------------------------
   public function getKeyFileSize(){
     return self::KEY_FILE_SIZE;
   }

   //-------------------------------------------------------------------------------------------------------------------
   //-------------------------------------------------------------------------------------------------------------------
   public function getKey(){
     return $this->getBytesFromKey( 0, self::KEY_FILE_SIZE);
   }

   //-------------------------------------------------------------------------------------------------------------------
   //-------------------------------------------------------------------------------------------------------------------
   public function getBytesFromKey( $start, $num_bytes ){
     $bytes="";

     if( is_null($start) || !isset($start) || !is_int($start) || ($start < 0) || ($start > self::KEY_FILE_SIZE ) ){
       throw new \Exception("The 'start' parameter supplied in following function is invalid at:\n".
       "line ".__LINE__."  in function " . __FUNCTION__ . "in class " . __CLASS__ . "\n");

     } elseif( is_null($num_bytes) || !isset($num_bytes) || !is_int($num_bytes) || ($num_bytes < 0) || ($num_bytes > self::KEY_FILE_SIZE ) ){
       throw new \Exception("The 'num_bytes' parameter supplied in following function is invalid at:\n".
       "line ".__LINE__."  in function " . __FUNCTION__ . "in class " . __CLASS__ . "\n");

     } elseif( $this->fully_buffered ) {
       if( !( isset($this->key) && is_string($this->key) && (strlen($this->key) == self::KEY_FILE_SIZE) ) ){
         throw new \Exception("The 'key' key file object parameter contents in following function is invalid at:\n".
         "line ".__LINE__."  in function " . __FUNCTION__ . "in class " . __CLASS__ . "\n");

       } else {
         $after_wrap_bytes = self::KEY_FILE_SIZE - $start - $num_bytes;
         if( $after_wrap_bytes < 0){
           $bytes=substr( $this->key, $start, $num_bytes + $after_wrap_bytes );
           $bytes .= substr( $this->key, 0, abs( $after_wrap_bytes ) );

         } else {
           $bytes = substr( $this->key, $start, $num_bytes );
         }
       }
     } elseif( !(  isset($this->key_file_name) && is_string($this->key_file_name) && (strlen($this->key_file_name) >= self::MIN_FQFN_LEN) && file_exists($this->key_file_name) ) ){
         throw new \Exception("The 'key_file_name' private key file object parameter contents in following function is invalid at:\n".
         "line ".__LINE__."  in function " . __FUNCTION__ . "in class " . __CLASS__ . "\n");

     } elseif( !( isset($this->fp_key_file) && is_resource($this->fp_key_file) && (FALSE!==fstat($this->fp_key_file) ) ) ){
         throw new \Exception("The 'fp_key_file' private key file object parameter contents in following function is invalid at:\n".
         "line ".__LINE__."  in function " . __FUNCTION__ . "in class " . __CLASS__ . "\n");

     } else {
       if( 0 !== fseek($this->fp_key_file, $start) ){
         throw new \Exception("fseek( this->fp_key_file, ".$start.") failed in following function at:\n".
         "line ".__LINE__."  in function " . __FUNCTION__ . "in class " . __CLASS__ . "\n");

       } else {
 //could test more tests of return values below, but just summed it up testing return value further down.
         $after_wrap_bytes = self::KEY_FILE_SIZE - $start - $num_bytes;
         if( $after_wrap_bytes < 0){
           $bytes=fread( $this->fp_key_file, $num_bytes + $after_wrap_bytes );
           rewind($this->fp_key_file);
           $bytes .= fread( $this->fp_key_file, abs( $after_wrap_bytes ) );

         } else {
           $bytes = fread( $this->fp_key_file, $num_bytes );
         }
       }
     }
     if( !isset( $bytes) || !is_string($bytes) || ($num_bytes !== strlen($bytes) ) ){
       throw new \Exception("Invalid value obtained in key_file read in following function at:\n".
       "line ".__LINE__."  in function " . __FUNCTION__ . "in class " . __CLASS__ . "\n");

     } else {
       return $bytes;
     }
   }

   //-------------------------------------------------------------------------------------------------------------------
   //-------------------------------------------------------------------------------------------------------------------
    public function makeKeyFile( $FQFN, $hold_in_memory=false ){
      if( !isset($hold_in_memory) || is_null( $hold_in_memory ) || !is_bool($hold_in_memory) ){
        throw new \Exception( "\nInvalid parameter 'hold_in_memory' at line ".__LINE__." in function " . __FUNCTION__ . "in class " . __CLASS__ . "\n");

      }
      if( isset( $this->fp_key_file)){
        throw new \Exception( "\nThis key file object is already initialized at line ".__LINE__." in function " . __FUNCTION__ . "in class " . __CLASS__ . "\n");

      }
      $file_parts = pathinfo( $FQFN );
      $file_path=$file_parts["dirname"];
      $this->disc_utes->checkDiscFreeSpace( $file_path, self::KEY_FILE_SIZE );
      $fp_key_file = $this->file_utes->openFileBinaryWrite( $FQFN );
      $fp_key_hash_file = $this->file_utes->openFileBinaryWrite( $FQFN . self::HASH_FILE_SUFFIX );

      echo("Starting key making. This will take 5-10 minutes and 100% for one thread/processor.\n");

      $this->key = random_bytes( self::KEY_FILE_SIZE);

      echo self::KEY_FILE_SIZE . " sized binary key built.\n";

      $binary_hash = sodium_crypto_generichash($this->key, "", SODIUM_CRYPTO_GENERICHASH_BYTES);
      $this->hash = sodium_bin2hex( $binary_hash );
      fwrite($fp_key_file, $this->key);
      fwrite($fp_key_hash_file, $this->hash);
      fclose($fp_key_hash_file);
      fclose($fp_key_file);
      $this->fp_key_file = $this->file_utes->openFileBinaryRead( $FQFN );
      $this->fully_buffered=$hold_in_memory;
      $this->key_file_name = $FQFN;
      $this->fullKeyFileCheck();

      if( !$hold_in_memory ){
        unset( $this->key );
        echo("Key file object initialized with key file POINTER to disc file\n");
      } else {
        echo("Key file object initialized with key file DATA from disc file\n");
      }
      echo ( "File ". $FQFN ." created and written to disc\n");


      echo ( "Hash Generated\n\n".
        "It is recommended that you install and run the test: dieharder -a -f ".$FQFN."\n".
        "That test will take another 5-10 minutes. If mostly 'PASSED', it's fine.".
        "**Securely** store multiple copies of key and DON'T LOSE IT!\n".
        "In OTHER locations, store the 'sodium_crypto_generichash' hash:\n\t".
              $hash . "\n".
        "It has been saved in the file: " . $FQFN . self::HASH_FILE_SUFFIX . "\n\n" .

        "\nCheck the hash and key before using the key by doing\n".
        "\tphp cexe.phar -C TheHashFileAbove  KeyFileMadeFromThisProgram\n");
   }

// passed by reference to prevent copying and too muchh memory usage, although interpreter may do it automatically since nothing is modified
//-------------------------------------------------------------------------------------------------------------------
//-------------------------------------------------------------------------------------------------------------------
   public function simpleKeyFileCheck( ){
     if( filesize( $FQFN ) != self::KEY_FILE_SIZE ){
       throw new \Exception( "\nInvalid contents in key file ".FQFN.": file is wrong size at line ".
       __LINE__." in function " . __FUNCTION__ . "in class " . __CLASS__ . "\n");
     }

     echo ("Key passed simpleKeyFileCheck\n");
   }


   //-------------------------------------------------------------------------------------------------------------------
   //-------------------------------------------------------------------------------------------------------------------
   public function fullKeyFileCheck( ){
     echo("Starting Full Key Check. Will take between 3-6 minutes. Only done when making keys or\n".
          "   when confirming hash file of key not supplied.\n");
     $start=microtime(true);//str_replace( " ", "", microtime() );
     $arr=array();
     for( $t=0; $t<256; $t++){
       $arr[$t]=0;
     }
     $local_key = $this->getBytesFromKey( 0, self::KEY_FILE_SIZE);

     for( $c=0; $c < self::KEY_FILE_SIZE; $c++){
       $index_from_char = unpack("C", $local_key[$c])[1];
       $arr[ $index_from_char ]++;
     }
     for( $t=0; $t<256; $t++){
       if( !isset( $arr[$t])  || ( $arr[$t] < (self::KEY_FILE_SIZE/256/2) ) ){
         throw new \Exception( "\nInvalid contents in key file ".$FQFN.
            __LINE__." in function " . __FUNCTION__ . "in class " . __CLASS__ . "\n" .
          "\nNOTE:----If this was while making a new key, this failure should be extremely rare. Try again.\n".
          "...........ALSO, after failure to makme a new key, you must delete the bad new key and hash files\n".
          "--------------that were just made BEFORE running it again\n");
       }
     }
     $end=microtime(true);//str_replace( " ", "", microtime() );
     echo( "\n Good Key in ".$end - $start . " seconds\n");
   }

   //-------------------------------------------------------------------------------------------------------------------
   //-------------------------------------------------------------------------------------------------------------------
   private function testAndLoadFromExistingFile( $FQFN, $optional_hash_file){
      if( !isset($optional_hash_file) || is_null( $optional_hash_file ) ) {
        $hash_avail=FALSE;
      } else {
       $hash_avail = TRUE;
      }

     if( filesize( $FQFN ) != self::KEY_FILE_SIZE ){
       throw new \Exception( "\nInvalid contents in key file ".FQFN.": file is wrong size at line ".
       __LINE__." in function " . __FUNCTION__ . "in class " . __CLASS__ . "\n");
     }
     $this->file_utes->readAllofBinaryFile($FQFN, $this->key );// Also qualifies file name and it's existence
     $this->key_file_name=$FQFN;
     $this->fp_key_file = $this->file_utes->openFileBinaryRead( $FQFN );

     if( $hash_avail ){
       $this->file_utes->readAllofBinaryFile($optional_hash_file, $this->hash );// Also qualifies file name and it's existence

       if( !$this->simpleKeyHashCheck( $this->hash ) ){
         throw new \Exception( "\nInvalid contents in key file hash ".$FQFN.": file is wrong size or contents at line ".
          __LINE__." in function " . __FUNCTION__ . "in class " . __CLASS__ . "\n");
       }
       if( 0 !== strcmp( $this->hash, sodium_bin2hex (sodium_crypto_generichash($this->key, "", SODIUM_CRYPTO_GENERICHASH_BYTES) ) )){
         throw new \Exception( "The hash does not match the key. Either you have the wrong key or hash, or your keys are under attack\n");
      } else {
        echo( "Loaded Key passed hash check\n" );
      }
    } else{
      $this->fullKeyFileCheck( );
    }
  }

  //-------------------------------------------------------------------------------------------------------------------
  //-------------------------------------------------------------------------------------------------------------------
    public function simpleKeyHashCheck( $hash ){
      if(  !isset( $hash ) || is_null( $hash ) || !is_string( $hash )|| ((SODIUM_CRYPTO_GENERICHASH_BYTES * 2 ) != strlen( $hash ) ) || !ctype_xdigit($hash ) ){
         $output=false;
      } else {
         $output=true;
      }
      return $output;
    }

}

?>
