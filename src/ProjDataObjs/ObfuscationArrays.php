<?php
namespace Cexe\ProjDataObjs;

use Cexe\Utilities\Password;
use Cexe\ProjDataObjs\KeyFile;
use Cexe\Utilities\FileUtilities;
use Cexe\Utilities\PasswordStretcher;
use Cexe\Utilities\DiscUtilities;


class ObfuscationArrays{
  public const KEY_SAMPLE_SIZE = 65536;

  private $file_utes;

  private $password_stretcher;
  private $key_file;
  private $current_mix_table="";
  private $mix_table_size=0;
  private $mix_table_pos=0;

  public function __construct( &$password_stretcher, &$key_file ){
    if( is_null( $password_stretcher ) || !isset( $password_stretcher ) || !is_object( $password_stretcher )
      || !is_a($password_stretcher, "\Cexe\ProjDataObjs\PasswordStretcher", TRUE    )){
      throw new \Exception( "\nInvalid parameter 'password_stretcher' at line ".__LINE__." in function " . __FUNCTION__ . "in class " . __CLASS__ . "\n");

    } elseif( is_null( $key_file ) || !isset( $key_file ) || !is_object( $key_file )
        || !is_a($key_file, "Cexe\ProjDataObjs\KeyFile", TRUE    )){
        throw new \Exception( "\nInvalid parameter 'key_file' at line ".__LINE__." in function " . __FUNCTION__ . "in class " . __CLASS__ . "\n");

    } elseif( !$password_stretcher->getIfStretched() ){
        throw new \Exception( "\nPasswordStretcher must have stretched password ONCE and issued a stretched password before any mix tables can be taken from it at line ".__LINE__." in function " . __FUNCTION__ . "in class " . __CLASS__ . "\n");

    } else {
      $this->password_stretcher = $password_stretcher;
      $this->key_file = $key_file;
      $this->file_utes = new \Cexe\Utilities\FileUtilities();
      $this->current_mix_table = $this->password_stretcher->getMixTableAsString();
      $this->mix_table_size = strlen( $this->current_mix_table );
      $this->mix_table_pos=0;
    }
  }

  public function makeAndGetArrays(){
    //Sample of array is at bottom of files
    //'exa' means 'expansion_xor_arr'
    $exa = $this->makeAndGetNewEmptyExpansionXORArray();
    $key_sample_start = $this->getKeySampleStart();

    $this->populateXORArrays( $exa, $key_sample_start );
    $this->populateExpArrays( $exa );
    return $exa;
  }

  private function populateXORArrays( &$exa, $key_sample_start ){
    $exa["xor_array"]["mix_table"] =  $this->current_mix_table;
    $exa["xor_array"]["exp_and_xor_key_sample"] = $this->key_file->getBytesFromKey( $key_sample_start, self::KEY_SAMPLE_SIZE );
    $exa["xor_array"]["second_byte_xor_key_sample"] = $this->key_file->getBytesFromKey( $this->alterFourByteInt( $key_sample_start ), self::KEY_SAMPLE_SIZE );

  }

  private function populateExpArrays( &$exa ){
    for($x=0; $x<256; $x++){
      $exa["exp_array"]["counts"][$x]=0;
      $exa["exp_array"]["moduli"][$x]=0;
    }

    for( $byte_pos = 0; $byte_pos < self::KEY_SAMPLE_SIZE; $byte_pos++ ){
      $unpacked = unpack( 'C', $exa["xor_array"]["exp_and_xor_key_sample"], $byte_pos );
      $byte=$unpacked[1];
      $exa["exp_array"]["exp_bytes"][$byte][] = $byte_pos;
    }

    //sort($exa["exp_array"]["exp_bytes"]);

    for($x=0; $x<256; $x++){
      $exa["exp_array"]["moduli"][$x]=count( $exa["exp_array"]["exp_bytes"][$x] );
    }
  }

  private function getKeySampleStart(){
    if( ($this->mix_table_pos + 4) > $this->mix_table_size ){
      $this->password_stretcher->reMix();
      $this->current_mix_table = $this->password_stretcher->getMixTableAsString();
      $this->mix_table_pos = 0;
    }

    $return = unpack( "L",  $this->current_mix_table, $this->mix_table_pos );
    $this->mix_table_pos += 4;
    return $return[1];
  }

  private function makeAndGetNewEmptyExpansionXORArray(){
    $exa = array(
            "exp_array"=>
              array(
                "counts"=>
                  array(),
                "moduli"=>
                  array(),
                "exp_bytes"=>
                  array()
              ),
            "xor_array"=>
              array(
                "mix_table"=>"",
                "exp_and_xor_key_sample"=>"",
                "second_byte_xor_key_sample"=>""
              )
          );
    return $exa;
  }

  private function alterFourByteInt( $four_byte_int ){
    $dude=0;
    $local = ~($four_byte_int - 0xA5802401);
    $local = $local & 0xFFFFFFFF;
    $dude += ( $local & 0x000000FF) << 16;
    $dude += ( $local & 0x0000FF00) << 16;
    $dude += ( $local & 0x00FF0000) >> 16;
    $dude += ( $local & 0xFF000000) >> 16;
    return $dude;
  }

  public function getBytesFromCurrentMixTable( $start, $num_bytes ){
    if( is_null($start) || !isset($start) || !is_int($start) || ($start < 0) || ($start > $this->const_key_file_size) ){
      throw new \Exception("The 'start' parameter supplied in following function is invalid at:\n".
      "line ".__LINE__."  in function " . __FUNCTION__ . "in class " . __CLASS__ . "\n");

    } elseif( is_null($num_bytes) || !isset($num_bytes) || !is_int($num_bytes) || ($num_bytes < 0) || ($num_bytes > $this->const_key_file_size) ){
      throw new \Exception("The 'num_bytes' parameter supplied in following function is invalid at:\n".
      "line ".__LINE__."  in function " . __FUNCTION__ . "in class " . __CLASS__ . "\n");

    } else {
      $after_wrap_bytes = $this->const_key_file_size - $start - $num_bytes;
      if( $after_wrap_bytes < 0){
        $bytes=substr( $this->str, $start, $num_bytes + $after_wrap_bytes );
        $bytes .= substr( $this->str, 0, abs( $after_wrap_bytes ) );

      } else {
        $bytes = substr( $this->str, $start, $num_bytes );
      }
    }
    return $bytes;
  }
}

?>
