<?php

namespace Cexe\ProjDataObjs;

use Cexe\Utilities\Password;
use Cexe\ProjDataObjs\KeyFile;
use Cexe\Utilities\FileUtilities;

class PasswordStretcher{

	public const DELTA = 7;
	public const TIME_COST = 10;
	public const SPACE_COST = 500;
	public const SALT = "n|>>c[^koqX&_uv[69{Y2ADq72k_3Q_~o[g5+:ZKb7kA";

	private $password_obj;
	private $salt;
	private $space_cost;
	private $time_cost;
	private $delta;

	private $cnt=0;
	private $buf;
	private $stretched=FALSE;

	/*
		PHP-BALLOON-HASHING.
		Based on Python version: https://github.com/nachonavarro/balloon-hashing
	*/

	public function __construct( &$password_obj , $salt=self::SALT, $space_cost=self::SPACE_COST, $time_cost=self::TIME_COST, $delta=self::DELTA ){
		if( is_null( $password_obj ) || !isset( $password_obj ) || !is_object( $password_obj ) || !is_a($password_obj, "Cexe\ProjDataObjs\Password", TRUE    )){
			throw new \Exception( "\nInvalid parameter 'password_obj' at line ".__LINE__." in function " . __FUNCTION__ . "in class " . __CLASS__ . "\n");

		} elseif( is_null( $salt ) || !isset( $salt ) || !is_string( $salt ) || ( strlen($salt) < 1 ) ){
			throw new \Exception( "\nInvalid parameter 'salt' at line ".__LINE__." in function " . __FUNCTION__ . "in class " . __CLASS__ . "\n");

		} elseif( is_null( $space_cost ) || !isset( $space_cost ) || !is_int( $space_cost ) || ( $space_cost < 0  ) ){
			throw new \Exception( "\nInvalid parameter 'space_cost' at line ".__LINE__." in function " . __FUNCTION__ . "in class " . __CLASS__ . "\n");

		} elseif( is_null( $time_cost ) || !isset( $time_cost ) || !is_int( $time_cost ) || ( $time_cost < 0  ) ){
			throw new \Exception( "\nInvalid parameter 'TIME_COST' at line ".__LINE__." in function " . __FUNCTION__ . "in class " . __CLASS__ . "\n");

		} elseif( is_null( $delta ) || !isset( $delta ) || !is_int( $delta ) || ( $delta < 0  ) ){
			throw new \Exception( "\nInvalid parameter 'delta' at line ".__LINE__." in function " . __FUNCTION__ . "in class " . __CLASS__ . "\n");

		} else {
			$this->password_obj=$password_obj;
			$this->salt=$salt;
			$this->space_cost=$space_cost;
			$this->time_cost=$time_cost;
			$this->delta=$delta;

		}
	}

	public function getIfStretched(){
		return $this->stretched;
	}

	public function getCnt(){
		return $this->cnt;
	}

	public function makeAndGetStretchedPassword( ){
		$this->stretched = TRUE;
		$return =  $this->balloon($this->password_obj->getPassword(), $this->salt, $this->space_cost, $this->time_cost, $this->delta );
		return $return;
	}

  public function getMixTableAsString(){
		if( !$this->getIfStretched() ){
			throw new \Exception( "\nPasswordStretcher must have stretched password ONCE and issued a stretched password before any mix tables can be taken from it at line ".__LINE__." in function " . __FUNCTION__ . "in class " . __CLASS__ . "\n");
		}
		$out_str="";
		reset( $this->buf );
		foreach ($this->buf as $value ){
			$out_str .= $value;
		}
		return $out_str;
	}

	public function reMix(){
		$this->mix($this->buf, $this->cnt, $this->delta, $this->salt, $this->space_cost, $this->time_cost);
	}


	/*
		*** function balloon($password, $salt, $space_cost, $time_cost, $delta) ***

		Main function that collects all the substeps. As previously mentioned, first expand, then mix, and finally extract.
		Args:
			password (str): The main string to hash
			salt (str): A user defined random value for security
			space_cost (int): The size of the buffer
			time_cost (int): Number of rounds to mix
			delta (int): Number of random blocks to mix with.
		Returns:
			str: A series of bytes, the hash.
	*/
	private function balloon( $password, $salt, $space_cost=self::SPACE_COST, $time_cost=self::TIME_COST, $delta=self::DELTA  )
	{
		$this->buf[0] = $this->hash_func(array(0, $password, $salt));
		$this->cnt = 1;
		$this->expand($this->buf, $this->cnt, $space_cost);
		$this->mix($this->buf, $this->cnt, $delta, $salt, $space_cost, $time_cost);
		return $this->balloon_extract();
	}

		/*
			*** function balloon_hash($password, $salt) ***

			A more friendly client function that just takes a password and a salt and computes outputs the hash in hex.
			Args:
				password (str): The main string to hash
				salt (str): A user defined random value for security
			Returns:
				str: The hash as hex.
		*/
		private function balloon_hash($password, $salt )
		{
			$delta = 5;
			$time_cost = 18;
			$space_cost = 24;
			return bin2hex(balloon($password, $salt, $space_cost, $time_cost, $delta ));
		}

		/*
			*** function expand(&$buf, $cnt, $space_cost) ***

			First step of the algorithm. Fill up a buffer with pseudorandom bytes derived from the password and salt
			by computing repeatedly the hash function on a combination of the password and the previous hash.
			Args:
				$buf (str array): Array of hashes as bytes.
				$cnt (int): Used in a security proof (read the paper)
				$space_cost (int): The size of the buffer
			Returns:
				void: Updates the buffer and counter, but does not return anything.
 		*/
		private function expand(&$buf, &$cnt, $space_cost)
		{
			for($s=1;$s<$space_cost;$s++)
			{
				array_push($buf, $this->hash_func(array($cnt, $buf[$s - 1])));
				$cnt += 1;
			}
			return;
		}
		/*
			*** function mix(&$buf, $cnt, $delta, $salt, $space_cost, $time_cost) ***

			Second step of the algorithm. Mix time_cost number of times the pseudorandom bytes in the buffer.
			At each step in the for loop, update the nth block to be the hash of the n-1th block, the nth block,
			and delta other blocks chosen at random from the buffer.
			Args:
				buf (str array): Array of hashes as bytes.
				cnt (int): Used in a security proof (read the paper)
				delta (int): Number of random blocks to mix with.
				salt (str): A user defined random value for security
				space_cost (int): The size of the buffer
				time_cost (int): Number of rounds to mix
			Returns:
				void: Updates the buffer and counter, but does not return anything.
		*/
		private function mix(&$buf, &$cnt, $delta, $salt, $space_cost, $time_cost)
		{
			echo("salt = " .$salt . "\n" .
						"space_cost = ". $space_cost . "\n" .
						"time_cost = ". $time_cost . "\n" .
						"delta = ". $delta . "\n"  );
			for($t=0;$t<$time_cost;$t++)
			{
				for($s=0;$s<$space_cost;$s++)
				{
					if($s == 0)
					{
						 $buf[$s] = $this->hash_func(array($cnt, end($buf), $buf[$s]));
					}
					else
					{
						$buf[$s] = $this->hash_func(array($cnt, $buf[$s-1], $buf[$s]));
					}
					$cnt += 1;
					for($i=0;$i<$delta;$i++)
					{
						$other = $this->bchexdec($this->hash_func_hex(array($cnt, $salt, $t, $s, $i)));
						$n = bcmod($other, strval($space_cost));
						$cnt += 1;
						$buf[$s] = $this->hash_func(array($cnt, $buf[$s], $buf[$n]));
						$cnt += 1;
					}
				}
			}
			return;
		}

		/*
			*** function balloon_extract() ***

			Final step. Return the last value in the buffer.
			Args:
				buf (str array): Array of hashes as bytes.
			Returns:
				str: Last value of the buffer as bytes
		*/
		private function balloon_extract()
		{
			return end($this->buf);
		}


		/*
			*** function hash_func($params) ***

			Concatenate all the arguments and hash the result.
			Args:
				*args: Arguments to concatenate
			Returns:
				str: The hashed string
		*/
		private function hash_func($params)
		{
			$t = '';
			foreach($params as $param)
			{
				$t .= $param;
			}
			//return hash('sha256', $t, TRUE);
			return hex2bin( blake3( $t, 32 ) );
		}


		/*
			*** function hash_func_hex($params) ***

			Concatenate all the arguments and hash the result.
			Args:
				*args: Arguments to concatenate
			Returns:
				str: The hashed string
		*/
		private function hash_func_hex($params)
		{
			$t = '';
			foreach($params as $param)
			{
				$t .= $param;
			}
			//return hash('sha256', $t);
			return blake3( $t, 32 );
		}

		/*
			*** function bchexdec($hex) ***

			Hexdec function for large numbers using BCMath.
			More info here: http://stackoverflow.com/questions/1273484/large-hex-values-with-php-hexdec

			Args:
				*args: Hexadecimal value

			Returns:
				str: The decimal value

		*/
		private function bchexdec($hex)
		{
			$dec = 0;
			$len = strlen($hex);
			for ($i = 1; $i <= $len; $i++) {
				$dec = bcadd($dec, bcmul(strval(hexdec($hex[$i - 1])), bcpow('16', strval($len - $i))));
			}
			return $dec;
		}


}
?>
