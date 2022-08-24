<?php
class KeyFileTest
{
  private const TEST_FILE_DIRECTORY = $_SERVER['PWD'];
  private const TEST_FILE_NAME = "KeyFileTestFile.bin";
  private const TEST_FILE_FQN = self::TEST_FILE_DIRECTORY . DIRECTORY_SEPARATOR . self::TEST_FILE_NAME
  private const NON_EXISTENT_DIRECTORY = uniqid("", TRUE);
  private const NON_EXISTENT_FQN = self::NON_EXISTENT_DIRECTORY . DIRECTORY_SEPARATOR . self::TEST_FILE_NAME:

/* The arr[] array passed in is taken from the OS. It is the array of command line arguments
 * arr[0] equals the name of the executing program
 * arr[1] is the arguments to thaty program, in this case, the FQN of the desination file
 */
  public function test()
  {
    $kf=new KeyFile();

    echo "test1(SHOULD FAIL): Null argument to function 'makeKeyFile'\n":;
    $kf->makeKeyFile( );

    echo "test2(SHOULD FAIL): Non array argument to function 'makeKeyFile'\n":;
    $kf->makeKeyFile( int(1) );

    echo "test3(SHOULD FAIL): Empty array argument to function 'makeKeyFile'\n":;
    $kf->makeKeyFile( array() );

    echo "test4(SHOULD FAIL): Array argument with only 1 memeber to function 'makeKeyFile'\n":;
    $arr=array();
    $arr[0]="single element array"
    $kf->makeKeyFile( $arr );

    echo "test5(SHOULD FAIL): Two element Array argument with only position [1] member NULL to function 'makeKeyFile'\n":;
    $arr=array();
    $arr[0]="single element array";
    $arr[2]=self::TEST_FILE_FQN;
    $kf->makeKeyFile( $arr );

    echo "test6(SHOULD FAIL): Two element Array argument with position [1] member non string to function 'makeKeyFile'\n":;
    $arr=array();
    $arr[0]="single element array";
    $arr[1]= int(1);
    $kf->makeKeyFile( $arr );

    echo "test7(SHOULD FAIL): Two element Array argument with position [1] member NON_EXISTENT_FQN to function 'makeKeyFile'\n":;
    $arr=array();
    $arr[0]="single element array";
    $arr[1]= self::NON_EXISTENT_FQN;
    $kf->makeKeyFile( $arr );

    if(!$file = fopen(self::TEST_FILE_FQN, 'wb')){
       echo "file name ". self::TEST_FILE_FQN . " could not be opened for write for some reason. Fatal error in test 8!";
       exit();
     } else {
       fwrite($file, "contents");
       fclose($file);
       echo "File created and written at \n\t".
         $args[1];
     }
     echo "test8(SHOULD FAIL): Two element Array argument with position [1] set to existing file to function 'makeKeyFile'\n":;
     $arr=array();
     $arr[0]="single element array";
     $arr[1]= self::TEST_FILE_FQN;
     $kf->makeKeyFile( $arr );

     if(!unlink(self::TEST_FILE_FQN)){
        echo "file name ". self::TEST_FILE_FQN . " could not be deleted for some reason. Fatal error in test 9!";
        exit();
      }
      echo "test9(SHOULD P-A-S-S): Two element Array argument with position [1] set to DESIRED file to function 'makeKeyFile'\n":;
      $arr=array();
      $arr[0]="single element array";
      $arr[1]= self::TEST_FILE_FQN;
      $kf->makeKeyFile( $arr );
    }
  }
}
?>
