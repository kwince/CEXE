<?php
namespace Cexe\Utilities ;

//(self::KEY_FILE_SIZE + (2**22)

class FileUtilities {

  public const MIN_FQFN_LEN=5;

  public function __construct(){
    ;
  }

  public function testInputFileName( $FQFN, $min_len=self::MIN_FQFN_LEN ){
    if( is_null( $min_len ) ||!isset( $min_len ) || !is_int( $min_len ) || ($min_len <= 0)){
      throw new \Exception( "\nInvalid parameter 'min_len' at line ".__LINE__." in function " . __FUNCTION__ . "in class " . __CLASS__ . "\n");

    } elseif ( is_null( $FQFN ) || !isset( $FQFN ) || !is_string( $FQFN)  || !file_exists( $FQFN ) || is_dir( $FQFN ) ){
      throw new \Exception( "\nInvalid input filename at line ".__LINE__." in function " . __FUNCTION__ . "in class " . __CLASS__ . "\n");

    }
    return true;
  }

  public function testInputDirName( $FQFN, $min_len=self::MIN_FQFN_LEN ){
    if( is_null( $min_len ) ||!isset( $min_len ) || !is_int( $min_len ) || ($min_len <= 0)){
      throw new \Exception( "\nInvalid parameter 'min_len' at line ".__LINE__." in function " . __FUNCTION__ . "in class " . __CLASS__ . "\n");

    } elseif ( is_null( $FQFN ) || !isset( $FQFN ) || !is_string( $FQFN)  || !file_exists( $FQFN ) || !is_dir( $FQFN ) ){
      throw new \Exception( "\nInvalid input Directory at line ".__LINE__." in function " . __FUNCTION__ . "in class " . __CLASS__ . "\n");

    }
    return true;
  }

  public function testOutputFileName( $FQFN, $min_len=self::MIN_FQFN_LEN ){

    if( is_null( $min_len ) ||!isset( $min_len ) || !is_int( $min_len ) || !($min_len >= 0)){
      throw new \Exception( "\nInvalid parameter 'min_len' at line ".__LINE__." in function " . __FUNCTION__ . "in class " . __CLASS__ . "\n");

    } elseif( is_null( $FQFN ) || !isset( $FQFN ) || !is_string( $FQFN ) || (strlen( $FQFN) < $min_len  ) || stripos($FQFN, "*") ){
      throw new \Exception( "\nInvalid output filename at line ".__LINE__." in function " . __FUNCTION__ . "in class " . __CLASS__ . "\n");

    } elseif( file_exists( $FQFN ) ) {
      throw new \Exception( "\nOutput file ". $FQFN ." ALREADY exists at line ".__LINE__."  in function " . __FUNCTION__ . "in class " . __CLASS__ . "\n");

    } elseif( '/' ===  substr($FQFN, -1) ) {
      throw new \Exception( "\nCannot use directory as output file, file name ended in '/' at line ".__LINE__." in function " . __FUNCTION__ . "in class " . __CLASS__ . "\n");

    } else {
      $path_parts = pathinfo($FQFN);
      $path=$path_parts['dirname'];
      if( !is_dir( $path ) ){
        throw new \Exception( "\nDestination directory does not exist. This is an error at line ".__LINE__." in function " . __FUNCTION__ . "in class " . __CLASS__ . "\n");
      } else {
        return $path;
      }

    }
  }
    public function testOutputDirName( $FQFN, $min_len=self::MIN_FQFN_LEN){
      if( is_null( $min_len ) ||!isset( $min_len ) || !is_int( $min_len ) || !($min_len >= 0)){
        throw new \Exception( "\nInvalid parameter 'min_len' at line ".__LINE__." in function " . __FUNCTION__ . "in class " . __CLASS__ . "\n");

      } elseif( is_null( $FQFN ) || !is_string( $FQFN ) ||!isset( $FQFN ) || (strlen( $FQFN) <= $min_len  ) || stripos($FQFN, "*") ){
        throw new \Exception( "\nInvalid output filename at line ".__LINE__." in function " . __FUNCTION__ . "in class " . __CLASS__ . "\n");

      } elseif( file_exists( $FQFN ) ) {
        throw new \Exception( "\nOutput directory (or file of the same name) ALREADY exists at line ".__LINE__."  in function " . __FUNCTION__ . "in class " . __CLASS__ . "\n");

      } else {
        ;
      }
      return true;
  }

  public function openFileBinaryWrite( $FQFN ){
    $this-> testOutputFileName( $FQFN, self::MIN_FQFN_LEN );
      $file_handle = fopen($FQFN, 'wb');
      if( is_null($file_handle) || !$file_handle ){
           throw new \Exception( "File '". $FQFN . "' could not be opened for write for some reason. Check permissions.\n".
           "This is an error at line ".__LINE__." in function " . __FUNCTION__ . "in class " . __CLASS__ . "\n");
      } else {
        return $file_handle;
      }
  }

  public function openFileBinaryReadWrite( $FQFN ){
    $this-> testOutputFileName( $FQFN, self::MIN_FQFN_LEN );
      $file_handle = fopen($FQFN, 'rwb');
      if( is_null($file_handle) || !$file_handle ){
           throw new \Exception( "File '". $FQFN . "' could not be opened for read write for some reason. Check permissions.\n".
           "This is an error at line ".__LINE__." in function " . __FUNCTION__ . "in class " . __CLASS__ . "\n");
      } else {
        return $file_handle;
      }
  }

  public function openFileBinaryRead( $FQFN ){
    $this-> testInputFileName( $FQFN, self::MIN_FQFN_LEN );
      $file_handle = fopen($FQFN, 'rb');
      if( is_null($file_handle) || !$file_handle ){
           throw new \Exception( "File '". $FQFN . "' could not be opened for write for some reason. Check permissions.\n".
           "This is an error at line ".__LINE__." in function " . __FUNCTION__ . "in class " . __CLASS__ . "\n");
      } else {
        return $file_handle;
      }
  }

  public function readAllofBinaryFile( $FQFN, &$dest_buff ){
    $this->testInputFileName( $FQFN, self::MIN_FQFN_LEN );
    $file_handle = fopen($FQFN, 'rb');
    if( is_null($file_handle) || !$file_handle ){
         throw new \Exception( "File '". $FQFN . "' could not be opened for read for some reason. Check permissions.\n".
         "This is an error at line ".__LINE__." in function " . __FUNCTION__ . "in class " . __CLASS__ . "\n");
    } else {
      $dest_buff=fread($file_handle, filesize( $FQFN ));
      fclose($file_handle);
    }
  }

  public function directorySizeRecursive( $dir ){
    $size = 0;
    foreach (glob(rtrim($dir, '/').'/*', GLOB_NOSORT) as $each) {
        $size += is_file($each) ? filesize($each) : $this->directorySizeRecursive($each);
    }
    return $size;
  }


  /**
* Dumps a string into a traditional hex dump for programmers,
* in a format similar to the output of the BSD command hexdump -C file.
* The default result is a string.
* Supported options:
* <pre>
*   line_sep        - line seperator char, default = "\n"
*   bytes_per_line  - default = 16
*   pad_char        - character to replace non-readble characters with, default = '.'
* </pre>
*
* @param string $string
* @param array $options
* @param string|array
*/
 public function hexDump($string, array $options = null) {
    if (!is_scalar($string)) {
        throw new InvalidArgumentException('$string argument must be a string');
    }
    if (!is_array($options)) {
        $options = array();
    }
    $line_sep       = isset($options['line_sep'])   ? $options['line_sep']          : "\n";
    $bytes_per_line = @$options['bytes_per_line']   ? $options['bytes_per_line']    : 16;
    $pad_char       = isset($options['pad_char'])   ? $options['pad_char']          : '.'; # padding for non-readable characters

    $text_lines = str_split($string, $bytes_per_line);
    $hex_lines  = str_split(bin2hex($string), $bytes_per_line * 2);

    $offset = 0;
    $output = array();
    $bytes_per_line_div_2 = (int)($bytes_per_line / 2);
    foreach ($hex_lines as $i => $hex_line) {
        $text_line = $text_lines[$i];
        $output []=
            sprintf('%08X',$offset) . '  ' .
            str_pad(
                strlen($text_line) > $bytes_per_line_div_2
                ?
                    implode(' ', str_split(substr($hex_line,0,$bytes_per_line),2)) . '  ' .
                    implode(' ', str_split(substr($hex_line,$bytes_per_line),2))
                :
                implode(' ', str_split($hex_line,2))
            , $bytes_per_line * 3) .
            '  |' . preg_replace('/[^\x20-\x7E]/', $pad_char, $text_line) . '|';
        $offset += $bytes_per_line;
    }
    $output []= sprintf('%08X', strlen($string));
    return @$options['want_array'] ? $output : join($line_sep, $output) . $line_sep;
  }

}


?>
