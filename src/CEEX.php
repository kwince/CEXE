<?php


class CEEX
{
/*
1/ make value and getter function static  in "key file" class
2/ move this file and key files to class mamed directories
*/
//this file is to both encode/obfuscate and decode/deobfuscate


$in_buff="";
$out_buff="";

// This is the encode EXPANSION and xor section
//-----------------------
      $key = new key fileamager $key file name, `$key start, $key_buff_dize)
	  $key->encode_buff( $in_buff, fwd);
	  key->xor_buff( buff, REV ) // accepts reference
  }

// This is the decode DE-EXPANSION section
//-----------------------
      key = new key fileamager $key file name, `$key start, $key_buff_dize)
	  key->xor_buff( buff, REV ) // accepts reference
      $key->encode_buff( $in_buff, fwd);
  }



snippets
=========={=======
  while in_buff=read input file != EOF
	  for(x=strlen(in_buff); x++;x++){
	     out_buff.= key->addr_next_occur(in_buff[x], fwd);
	  }


$key_buff_size=2**10;
$in_buff_size=2**10;

  $key->init_start_point();


class  key_file_manager{

	public static const $FWD=1, $REV=2;
	private const $salt="supercalifragilisticexpialidocious8675309";
	private const $key_size=2**32;
	private $file_handle;
	private $key_start_position;

	public function __constructor( $key_file_name, $key start pos ){
		if
	    test file name,
		open file
		test hash,
		test and assign key start position
	}
	public function encode_buff( &binary_strig_buff, $direction ){
		if ( $direction === $this->FWD ){

		} elseif ( direction === $this->REV ) {

		} else {

		}
		return encoded_buff;
	}
	public function decode_buff( &binary_strig_buff, $direction ){
		if ( $direction === $this->FWD ){

		} elseif ( direction === $this->REV ) {

		} else {

		}
		return decoded_buff;
	}
	public function xor_buff( &binary_strig_buff, $direction ){
		if ( $direction === $this->FWD ){

		} elseif ( direction === $this->REV ) {

		} else {

		}
	}

	private function chk_file_name_get_hndl_for_read( $file_name ){

	}
	private function convert_file_name_to_key_start_pos( $file_name ){
		$hash=sha256( $file_name . $this->salt );
		$hex=substr( $hash, -8);
		$key_start_pos=hex2dec( $hex );
		return $key_start_pos;
	}
}
?>
