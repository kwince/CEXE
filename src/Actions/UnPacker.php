<?php

namespace Cexe\Actions;

use ParagonIE\Halite\KeyFactory;
use ParagonIE\Halite\Symmetric\Crypto as Symmetric;
use ParagonIE\HiddenString\HiddenString;
use ParagonIE\Halite\File;
use ParagonIE\Halite\Stream\ReadOnlyFile;

use Cexe\Utilities\FileUtilities;
use Cexe\Utilities\DiscUtilities;
use Cexe\ProjDataObjs\Password;
use Cexe\ProjDataObjs\PasswordStretcher;
use Cexe\ProjDataObjs\KeyFile;
use Cexe\ProjDataObjs\ObfuscationArrays;


class UnPacker{
//  -P ArchivedDirectoryForSource  DIRECTOYNAMEForDestination  FILENameForKeyFileMadeFromThisProgram  Password\n\n  OPTIONAL_HASH_FILE
// 0 1 2                           3                           4                                      5             6

//  rename('/home/gearond/destination/my.tar.bz2', '/home/gearond/finalDestination/my.tar.bz2')

  public const SOURCE_PACKED_ARCHIVE_INDEX = 2;
  public const DESTINATION_DIRECTORY_INDEX = 3;
  public const KEY_FILE_INDEX = 4 ;
  public const OPTIONAL_HASH_FILE_INDEX = 5;

  public const EXPECTED_MIN_COMPRESSION_RATION = 0.5;

  public const INTERMEDIATE_FILE_NAME = "CEXE_FILE";
  public const ARCHIVE_SUFFIX = ".tar";
  public const ARCHIVE_FILE_NAME = "CEXE_FILE.tar"; //INTERMEDIATE_FILE_NAME . ARCHIVE_SUFFIX;
  public const COMPRESSION_FILE_SUFFIX = '.bz2'; //THESE TWO MUST MATCH NAMES BUT THIS IS lower CASE
  public const COMPRESSION_PHAR_SUFFIX = \Phar::BZ2; //THESE TWO MUST MATCH NAMES BUT THIS IS UPPER CASE
  public const COMPRESSED_FILE_NAME = "CEXE_FILE.tar.bz2"; //ARCHIVE_FILE_NAME . COMPRESSION_SUFFIX;
  public const EXPOR_SUFFIX = '.expor';
  public const EXPOR_FILE_NAME = "CEXE_FILE.tar.bz2.expor"; //COMPRESSED_FILE_NAME . EXPOR_SUFFIX;
  public const FINAL_SUFFIX = '.00.10.00.cexe';

//    $file_utes->readAllofBinaryFile( $key_file_hash_file , $key_file_hash);

  private $file_utes;
  private $disc_utes;
  private $po;
  private $pso;
  private $streched_password;
  private $kfo;
  private $key_file_hash;
  private $obfArraysObj;
  private $exa;

  private $fp_in_packed_archive;
  private $fp_out_expor;
  private $fp_in_expor;
  private $fp_in_compressed_archive;
  private $final_dir_name;

  private $trgt_chnk_length;
  private $mix_xor_pos;

  public function __construct(){
    ;
  }

  public function do( $arr ){
    $start=microtime(true);//str_replace( " ", "", microtime() );
    $this->general_prep($arr);
    $this->p_de_encrypt( $arr  );
    $this->p_de_expor( $arr ); // 'E'XPAND in 'cExe' and 'X'OR in 'ceXe'
    $this->p_de_compress( $arr );
    $end=microtime(true);//str_replace( " ", "", microtime() );
    echo $end-$start . " seconds".PHP_EOL;
  }

  //The original packed archive is NOT deleted
  private function p_de_encrypt($arr){
    echo("****Beginning Decryption\n");
    $passwd = new HiddenString($this->stretched_password);
    $encryptionKey = KeyFactory::deriveEncryptionKey($passwd, \Cexe\ProjDataObjs\Password::HALITE_SALT);
    if(!File::decrypt($arr[self::SOURCE_PACKED_ARCHIVE_INDEX], $arr[self::DESTINATION_DIRECTORY_INDEX ]."/". self::EXPOR_FILE_NAME, $encryptionKey) ){
      throw new \Exception( "The file ". $arr[self::SOURCE_PACKED_ARCHIVE_INDEX] .
        " was not able to be decrypted to " . $arr[self::DESTINATION_DIRECTORY_INDEX ]."/". self::EXPOR_FILE_NAME .
        " at line ".__LINE__. "  in function " . __FUNCTION__ . " in class " . __CLASS__ . "\n");
    }
    echo("****Decryption Ended\n");
  }

  private function p_de_expor($arr){
    //\$times["de_expor_start"]=microtime(true);
    echo( "****Beginning DeExpor (Xoring and Contraction)\n");
    $this->prep_for_p_de_expor($arr);
    $mix_table_size = strlen( $this->exa["xor_array"]["mix_table"] );

    while (!feof($this->fp_in_expor) &&
      ($trgt_file_chnk = fread($this->fp_in_expor, ( \Cexe\ProjDataObjs\ObfuscationArrays::KEY_SAMPLE_SIZE * 2) )) !== false) {
      $outbuff="";
      $chnk_len= strlen( $trgt_file_chnk);

      for( $key_sample_pos=0, $trgt_chnk_pos1=0, $trgt_chnk_pos2=1 ; $trgt_chnk_pos1 < $chnk_len ; $key_sample_pos++){

        $tmp = $trgt_file_chnk[$trgt_chnk_pos1] ^ $this->exa["xor_array"]["mix_table"][$this->mix_xor_pos % $mix_table_size]
          ^ $this->exa["xor_array"]["exp_and_xor_key_sample"][$key_sample_pos] ;
        $this->mix_xor_pos++;
        $tmp .= $trgt_file_chnk[$trgt_chnk_pos2] ^ $this->exa["xor_array"]["mix_table"][$this->mix_xor_pos % $mix_table_size]
          ^ $this->exa["xor_array"]["second_byte_xor_key_sample"][$key_sample_pos];
        $this->mix_xor_pos++;
        $outbuff .= $this->exa["xor_array"]["exp_and_xor_key_sample"][unpack("v", $tmp)[1]];

        $trgt_chnk_pos1+=2;
        $trgt_chnk_pos2+=2;
      }


//DEBUG put tests here
      fwrite( $this->fp_out_compressed_archive, $outbuff );
      $this->exa = $this->obfArraysObj->makeAndGetArrays();
    }
    fclose( $this->fp_out_compressed_archive );
    unlink($arr[self::DESTINATION_DIRECTORY_INDEX] . "/" . self::EXPOR_FILE_NAME);
    echo( "****DeExpor (Xoring and Contraction) Ended\n");
//    /$times["de_expor_end"]=microtime(true);
  }

  private function p_de_compress( $arr ){
    echo( "****Beginning Decompression\n");
    if (!mkdir( $this->final_dir_name )) {
      throw new \Exception( "The directory ". $this->final_dir_name . " was not able to be created at line ".__LINE__.
        "  in function " . __FUNCTION__ . " in class " . __CLASS__ . "\n");
    }

    $p = new \PharData($arr[self::DESTINATION_DIRECTORY_INDEX] . "/" . self::COMPRESSED_FILE_NAME );
    if( !$p->extractTo( $this->final_dir_name ) ){
      throw new \Exception( "The file ". $arr[self::DESTINATION_DIRECTORY_INDEX] . "/" . self::COMPRESSED_FILE_NAME  .
        " was not able to be extracted to: " . $this->final_dir_name . " at line ".__LINE__.
        "  in function " . __FUNCTION__ . " in class " . __CLASS__ . "\n");
    }
    unset($p);
    unlink($arr[self::DESTINATION_DIRECTORY_INDEX] . "/" . self::COMPRESSED_FILE_NAME);
    echo( "****Decompression Ended\n");
  }


//SUBSIDIARY FUNCTIONS---------------------------------------------------------

  private function general_prep($arr){
    $path_parts = pathinfo( $arr[self::SOURCE_PACKED_ARCHIVE_INDEX ]);
    $this->final_dir_name = $arr[ self::DESTINATION_DIRECTORY_INDEX ] . "/" . $path_parts[ "filename"] ;
    $this->file_utes = new \Cexe\Utilities\FileUtilities();
    $this->disc_utes = new \Cexe\Utilities\DiscUtilities();
    $this->po = new \Cexe\ProjDataObjs\Password(  );
    $this->po->promptForAndSetPassword();
    $this->pso = new \Cexe\ProjDataObjs\PasswordStretcher( $this->po );
    $this->kfo = new \Cexe\ProjDataObjs\KeyFile( $arr[self::KEY_FILE_INDEX], $arr[self::OPTIONAL_HASH_FILE_INDEX] );
    $this->stretched_password = $this->pso->makeAndGetStretchedPassword( );

    $this->obfArraysObj = new \Cexe\ProjDataObjs\ObfuscationArrays( $this->pso, $this->kfo  );

    $this->verifyDiscSpaceAvailableforUnPackingFastHighMem( $arr[ self::DESTINATION_DIRECTORY_INDEX ] );
  }

  private function verifyDiscSpaceAvailableforUnPackingFastHighMem( $trgt_directory ){
    $packed_archive_size = filesize( $trgt_directory);
    $projected_decompressed_archive_size = ( $packed_archive_size / self::EXPECTED_MIN_COMPRESSION_RATION) * 2;
    $disk_burden = $packed_archive_size + $projected_decompressed_archive_size;
    $this->disc_utes->checkDiscFreeSpace( $trgt_directory, $disk_burden );
  }

  private function prep_for_p_de_expor($arr){
    $this->fp_in_expor = $this->file_utes->openFileBinaryRead( $arr[self::DESTINATION_DIRECTORY_INDEX] . "/" . self::EXPOR_FILE_NAME );
    $this->fp_out_compressed_archive = $this->file_utes->openFileBinaryWrite( $arr[self::DESTINATION_DIRECTORY_INDEX] . "/" . self::COMPRESSED_FILE_NAME );
    $this->exa = $this->obfArraysObj->makeAndGetArrays();
    $this->mix_xor_pos = 0;
  }
}
?>
