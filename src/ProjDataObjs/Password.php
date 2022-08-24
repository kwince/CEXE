<?php


namespace Cexe\ProjDataObjs;

class Password{

  public const CORRECT_PASSWORD_DETECTION_PREPEND = "TRUE-TRUE-TRUE-TRUE";
  public const MIN_PASSWORD_LEN = 20;
  public const MIN_UPPER_ALPHA_CHARS = 1;
  public const MIN_LOWER_ALPHA_CHARS = 1;
  public const MIN_SPECIAL_CHARS = 1;
  public const MIN_NUMBERS = 1;
  public const HALITE_SALT = "\x4c\x86\x4f\xe8\x62\x3a\xd8\xc8\xc6\xf8\x91\x06\xea\x43\x53\x16";

  //DEBUG fix allowed http_negotiate_chars
  public const ALLOWED_SPECIAL_CHARS = "-~#_^.+;=";

  private $pwd="";

  public function __construct( $pwd="" ){
    if( $pwd!==""){
      $msg = $this->chk( $pwd );
      if( strlen($msg) > 0){
        throw new \Exception($msg);
      }
      $this->pwd = $pwd;
    }
  }

  public function getPassword(){
    if( $this->pwd == ""){
      throw new Exception( "\nEMPTY password invalid at line ".__LINE__."  in function " . __FUNCTION__ . " in class " . __CLASS__ . "\n" );
    }
    return $this->pwd;
  }

  public function chk( $password ){
    $msg="";

    if( !isset( $password) || is_null( $password ) || !is_string( $password )){
      $msg = "\nEMPTY password invalid at line ".__LINE__."  in function " . __FUNCTION__ . " in class " . __CLASS__ . "\n";

    } else {
      $msg = $this->chkForLength( $password);
      if("" == $msg){

        $upper_char = 0; $lower_char=0; $num = 0; $other = 0;
        for ($i = 0, $j = strlen($password); $i < $j || ("" != $msg); $i++){
          $c = substr($password,$i,1);
          if(preg_match('/^[[:upper:]]$/',$c)) {
            $upper_char++;
          } elseif (preg_match('/^[[:lower:]]$/',$c)) {
            $lower_char++;
          } elseif (preg_match('/^[[:digit:]]$/',$c)) {
            $num++;
          } elseif ( FALSE !== strpos( self::ALLOWED_SPECIAL_CHARS, $c)){
            $other++;
          } else {
            $msg = "\nInvalid password character ''".$c."' at line ".__LINE__."  in function " . __FUNCTION__ . " in class " . __CLASS__ . "\n";
          }
          if("" != $msg) break;
        }
      }
      if("" == $msg) {
        $msg = $this->chkForSpecialChars( $other ).
        $this->chkForNumbers( $num ) .
        $this->chkForUpperLetters( $upper_char ) .
        $this->chkForLowerLetters( $lower_char );
      }
    }
    return $msg;
  }

  public function chkForLength( $password ){
    if (strlen($password) <  self::MIN_PASSWORD_LEN) {
      return "\nPassword not at least ".self::MIN_PASSWORD_LEN." characters long at line ".__LINE__.
        "  in function " . __FUNCTION__ . " in class " . __CLASS__ . "\n";
    }
  }
  public function chkForSpecialChars( $count ){
    if ($count <  self::MIN_SPECIAL_CHARS) {
      return "\nPassword does not contain at least ".self::MIN_SPECIAL_CHARS." special characters at line ".__LINE__.
        "  in function " . __FUNCTION__ . " in class " . __CLASS__ . "\n";
    }
  }
  public function chkForNumbers( $count ){
    if ($count <  self::MIN_NUMBERS) {
      return "\nPassword does not contain at least ".self::MIN_NUMBERS." numberic characters at line ".__LINE__.
        "  in function " . __FUNCTION__ . " in class " . __CLASS__ . "\n";
    }
  }

  public function chkForUpperLetters( $count ){
    if ($count <  self::MIN_UPPER_ALPHA_CHARS) {
      return "\nPassword does not contain at least ".self::MIN_UPPER_ALPHA_CHARS." UPPER case alphabetic characters at line ".__LINE__.
        "  in function " . __FUNCTION__ . " in class " . __CLASS__ . "\n";
    }
  }

  public function chkForLowerLetters( $count ){
    if ($count <  self::MIN_LOWER_ALPHA_CHARS) {
      throw new \Exception( "\nPassword does not contain at least ".self::MIN_LOWER_ALPHA_CHARS." lower case alphabetic characters at line ".__LINE__.
        "  in function " . __FUNCTION__ . " in class " . __CLASS__ . "\n");
    }
  }
  public function promptForAndSetPassword(){
    do{
      $strPassword2="";
      $strPassword1="";

      echo("Enter Password:       ");
      $strPassword1=ltrim( rtrim( $this->getObscuredText() ) );
      echo("\n");
      echo("Enter Password Again: ");
      $strPassword2=ltrim( rtrim( $this->getObscuredText() ) );
      echo("\n");
      if( (0 !== strcmp( $strPassword2, $strPassword1) ) || $strPassword1==="" || $strPassword2==="" ){
        echo "\nPasswords did not match or are empty\n\n";
        $strPassword2="";
        $strPassword1="";
      } elseif( "" != ($msg = $this->chk($strPassword1 ))){
        echo "\n" . $msg . "\n\n";
        $strPassword2="";
        $strPassword1="";
      } else {
        break;
      }
    } while ( TRUE );
    $this->pwd=$strPassword1;
  }

  function getObscuredText($strMaskChar='*'){
    if(!is_string($strMaskChar) || $strMaskChar==''){
        $strMaskChar='*';
    }
    $strMaskChar=substr($strMaskChar,0,1);
    readline_callback_handler_install('', function(){});
    $strObscured='';
    while(true){
      $strChar = stream_get_contents(STDIN, 1);
      $intCount=0;
// Protect against copy and paste passwords
// Comment \/\/\/ to remove password injection protection
      $arrRead = array(STDIN);
      $arrWrite = NULL;
      $arrExcept = NULL;
      while (stream_select($arrRead, $arrWrite, $arrExcept, 0,0) && in_array(STDIN, $arrRead)){
        stream_get_contents(STDIN, 1);
        $intCount++;
      }
//        /\/\/\
// End of protection against copy and paste passwords
      if($strChar===chr(10)){
        break;
      }
      if($intCount===0){
        if(ord($strChar)===127){
          if(strlen($strObscured)>0){
            $strObscured=substr($strObscured,0,strlen($strObscured)-1);
            echo(chr(27).chr(91)."D"." ".chr(27).chr(91)."D");
          }
        } elseif ($strChar>=' '){
          $strObscured.=$strChar;
          echo($strMaskChar);
          //echo(ord($strChar));
        }
      }
    }
    readline_callback_handler_remove();
    return($strObscured);
  }
}


?>
