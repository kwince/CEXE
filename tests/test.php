

<?php

else else {
     echo "got there\n";
    echo filesize( $i_file_name)."\n";
    echo filesize( $$o_file_name)."\n";
    echo disk_free_space( realpath($i_file_name) ) . "\n";
    echo disk_free_space( realpath($$o_file_name) ) . "\n";

}


class start_up_files {

  private $i_file_name;
  private $o_file_name;
  private $k_file_name;
  private $sha256_hash_of_keyfile;
  private $length_of_key_file;

  function __construct( $input_file_name, $output_file_name, $key_file_name ){
    $this->i_file_name=$input_file_name;
    $this->o_file_name=$output_file_name;
    $this->k_file_name=$key_file_name;
  }

  public function open_files(){}
    $this->test_input_file
    $this->test_output_file
    $this->test_key file
    open files get handles
    return array of file handles or false
  }
  private function test_input_file(){
    if( is_null( $this->i_file_name ) || !is_string( $this->i_file_name ) || ( strlen($this->i_file_name) <= 2 ) || stripos($this->i_file_name, "*") ) {
      echo("\nInvalid Input filename\n");
      exit();

    } else if( !file_exists( $this-$this->i_file_name ) ) {
      echo("\nInput file does not exist\n");
      exit();

    } else if(is_dir( $this->i_file_name ) ) {
      echo("\nInput file is a directory. This is an error\n");
      exit();

    } else if(!is_file( $this->i_file_name ) ) {
      echo("\nInput file is NOT a file. This is an error\n");
      exit();
    }
  }
  private function test_output_file(){
    if( is_null( $this->o_file_name ) || !is_string( $this->o_file_name ) || (strlen($this->o_file_name) <= 2 ) || stripos($this->i_file_name, "*") ){
      echo("\nInvalid Output filename\n");
      exit();

    } else if( !file_exists( $$o_file_name ) ) {
      echo("\nOutput file does not exist\n");
      exit();

    } else if(is_dir( $$o_file_name ) ) {
      echo("\n Output file is a directory. This is an error\n");
      exit();

    } else if(!is_file( $$o_file_name ) ) {
      echo("\n Output file is a directory. This is an error\n");
      exit();
    }
  }


  }
  private function test_key_file(){


  }
  private function get_file_handles(){

  }
}
?>
