<?php

namespace Cexe\Actions;

use ParagonIE\Halite\KeyFactory;
use ParagonIE\Halite\Symmetric\Crypto as Symmetric;
use ParagonIE\HiddenString\HiddenString;
use ParagonIE\Halite\File;


use Cexe\Utilities\FileUtilities;
use Cexe\Utilities\DiscUtilities;
use Cexe\ProjDataObjs\Password;
use Cexe\ProjDataObjs\PasswordStretcher;
use Cexe\ProjDataObjs\KeyFile;
use Cexe\ProjDataObjs\ObfuscationArrays;


class Packer{
//  -P DIRECTORYNameForSource  DIRECTOYNAMEForArchiveDestination  FILENameForKeyFileMadeFromThisProgram  OPTIONAL_HASH_FILE
// 0 1 2                       3                                  4                                      5

  public const SOURCE_DIRECTORY_INDEX = 2;
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

  private $file_utes;
  private $disc_utes;
  private $po;
  private $pso;
  private $streched_password;
  private $kfo;
  private $key_file_hash;
  private $obfArraysObj;
  private $exa;
  private $fp_in_archive;
  private $fp_in_compressed_archive;
  private $fp_out_expor;
  private $fp_in_expor;
  private $fp_in_final;
  private $trgt_chunk_length;
  private $final_file_name;

  private $mix_xor_pos;

  public function __construct(){
    ;
  }

  public function do( $arr ){
    $start=microtime(true);//str_replace( " ", "", microtime() );
    $this->general_prep($arr);
    $this->p_compress( $arr );
    $this->p_expor( $arr ); // 'E'XPAND in 'cExe' and 'X'OR in 'ceXe'
    $this->p_encrypt( $arr  );
    $end=microtime(true);//str_replace( " ", "", microtime() );
    echo $end-$start . " seconds" .PHP_EOL;
  }

  private function p_compress( $arr ){
    echo("****Beginning Compression\n");
    $this->file_utes->testInputDirName( $arr[ self::SOURCE_DIRECTORY_INDEX ] );

    $p = new \PharData(self::ARCHIVE_FILE_NAME );
    $p->buildFromDirectory($arr[ self::SOURCE_DIRECTORY_INDEX ]);
    $p->compress(self::COMPRESSION_PHAR_SUFFIX);
    unset($p);
    unlink(self::ARCHIVE_FILE_NAME);
    echo("****Compression Ended\n");
  }

  private function p_expor($arr){
    echo("****Beginning Expor (Expansion and XOR)\n");
    $this->prep_for_p_expor($arr);
    $mix_table_size = strlen( $this->exa["xor_array"]["mix_table"] );
    while (!feof($this->fp_in_compressed_archive) &&
        ($target_file_chunk = fread($this->fp_in_compressed_archive, \Cexe\ProjDataObjs\ObfuscationArrays::KEY_SAMPLE_SIZE)) !== false) {

       $outbuff="";
       $chunk_len= strlen( $target_file_chunk);
       $bytes_out="  ";
       for( $trgt_chnk_pos=0 ; $trgt_chnk_pos < $chunk_len ; $trgt_chnk_pos++ ){
        $trgt_byte_array = unpack( 'C', $target_file_chunk, $trgt_chnk_pos );
        $trgt_byte = $trgt_byte_array[1];

        $which_of_many_exp_bytes = mt_rand( 0, $this->exa["exp_array"]["moduli"][$trgt_byte]-1 );
        $exp_byte = $this->exa["exp_array"]["exp_bytes"][$trgt_byte][$which_of_many_exp_bytes];
        $exp_bytes=pack( "S", $exp_byte );


        $bytes_out[0] = $exp_bytes[0] ^ $this->exa["xor_array"]["mix_table"][$this->mix_xor_pos % $mix_table_size]
          ^ $this->exa["xor_array"]["exp_and_xor_key_sample"][$trgt_chnk_pos];
        $this->mix_xor_pos++;
        $bytes_out[1] = $exp_bytes[1] ^ $this->exa["xor_array"]["mix_table"][$this->mix_xor_pos % $mix_table_size]
          ^ $this->exa["xor_array"]["second_byte_xor_key_sample"][$trgt_chnk_pos];
        $this->mix_xor_pos++;

        $outbuff .= $bytes_out;
      }
//DEBUG put tests here
      fwrite( $this->fp_out_expor, $outbuff );
      $this->exa = $this->obfArraysObj->makeAndGetArrays();
    }
    fclose( $this->fp_in_compressed_archive );
    fclose( $this->fp_out_expor );
    unlink(self::COMPRESSED_FILE_NAME);
    echo("****Expor (Expansion and XOR) Ended\n");
  }

  private function p_encrypt(){
    echo("****Beginning Encryption\n");
    $passwd = new HiddenString($this->stretched_password);
    $encryptionKey = KeyFactory::deriveEncryptionKey($passwd, \Cexe\ProjDataObjs\Password::HALITE_SALT);
    File::encrypt(self::EXPOR_FILE_NAME, $this->final_file_name, $encryptionKey);
    unlink( self::EXPOR_FILE_NAME );
    echo("****Encryption Ended\n");
  }

//SUBSIDIARY FUNCTIONS---------------------------------------------------------

  private function general_prep($arr){
    $path_parts = pathinfo( $arr[self::SOURCE_DIRECTORY_INDEX ]);
    $trgt_dir = $path_parts['basename'];
    $this->final_file_name = $arr[ self::DESTINATION_DIRECTORY_INDEX ] . "/" . $trgt_dir ."." . date('Y-m-d_H-m-s') . self::FINAL_SUFFIX ;
    echo "Name of output file will be: " .$this->final_file_name . "\n";
    $this->file_utes = new \Cexe\Utilities\FileUtilities();
    $this->disc_utes = new \Cexe\Utilities\DiscUtilities();
    $this->po = new \Cexe\ProjDataObjs\Password( );
    $this->po->promptForAndSetPassword();
    $this->pso = new \Cexe\ProjDataObjs\PasswordStretcher( $this->po );
    $this->kfo = new \Cexe\ProjDataObjs\KeyFile( $arr[self::KEY_FILE_INDEX], $arr[self::OPTIONAL_HASH_FILE_INDEX] );
    $this->stretched_password = $this->pso->makeAndGetStretchedPassword( );

    $this->obfArraysObj = new \Cexe\ProjDataObjs\ObfuscationArrays( $this->pso, $this->kfo  );

    $this->verifyDiscSpaceAvailableforPackingFastHighMem( $trgt_dir );
  }

  private function verifyDiscSpaceAvailableforPackingFastHighMem( $trgt_directory ){
    $projected_archive_size = $this->file_utes->directorySizeRecursive( $trgt_directory."/" );
    $projected_compressed_archive_size = $projected_archive_size * self::EXPECTED_MIN_COMPRESSION_RATION * 2;
    $disk_burden = $projected_archive_size + $projected_compressed_archive_size;
    $this->disc_utes->checkDiscFreeSpace( dirname(__FILE__), $disk_burden );
  }

  private function prep_for_p_expor($arr){
    $this->fp_in_compressed_archive = $this->file_utes->openFileBinaryRead( self::COMPRESSED_FILE_NAME );
    $this->fp_out_expor = $this->file_utes->openFileBinaryWrite( self::EXPOR_FILE_NAME );
    $this->exa = $this->obfArraysObj->makeAndGetArrays();
    $this->mix_xor_pos = 0;
  }
}

?>
