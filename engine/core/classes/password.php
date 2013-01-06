<?php

namespace core\classes;

/**
 * Description of cookie
 *
 * $password = new password(15);
 *
 * $hash = $password->hash('password');
 * $isGood = $password->verify('password', $hash);
 *
 * @author Козленко В.Л.
 */
class password {
	public static function generate($l = 8, $c = 0, $n = 0, $s = 0) {
		 // get count of all required minimum special chars
		 $count = $c + $n + $s;
	 
		 // sanitize inputs; should be self-explanatory
		 if(!is_int($l) || !is_int($c) || !is_int($n) || !is_int($s)) {
			  trigger_error('Argument(s) not an integer', E_USER_WARNING);
			  return false;
		 }
		 elseif($l < 0 || $l > 20 || $c < 0 || $n < 0 || $s < 0) {
			  trigger_error('Argument(s) out of range', E_USER_WARNING);
			  return false;
		 }
		 elseif($c > $l) {
			  trigger_error('Number of password capitals required exceeds password length', E_USER_WARNING);
			  return false;
		 }
		 elseif($n > $l) {
			  trigger_error('Number of password numerals exceeds password length', E_USER_WARNING);
			  return false;
		 }
		 elseif($s > $l) {
			  trigger_error('Number of password capitals exceeds password length', E_USER_WARNING);
			  return false;
		 }
		 elseif($count > $l) {
			  trigger_error('Number of password special characters exceeds specified password length', E_USER_WARNING);
			  return false;
		 }
	 
		 // all inputs clean, proceed to build password
	 
		 // change these strings if you want to include or exclude possible password characters
		 $chars = "abcdefghijklmnopqrstuvwxyz";
		 $caps = strtoupper($chars);
		 $nums = "0123456789";
		 $syms = "!@#$%^&*()-+?";

         $out = '';
		 // build the base password of all lower-case letters
		 for($i = 0; $i < $l; $i++) {
			  $out .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
		 }
	 
		 // create arrays if special character(s) required
		 if($count) {
			  // split base password to array; create special chars array
			  $tmp1 = str_split($out);
			  $tmp2 = array();
	 
			  // add required special character(s) to second array
			  for($i = 0; $i < $c; $i++) {
				   array_push($tmp2, substr($caps, mt_rand(0, strlen($caps) - 1), 1));
			  }
			  for($i = 0; $i < $n; $i++) {
				   array_push($tmp2, substr($nums, mt_rand(0, strlen($nums) - 1), 1));
			  }
			  for($i = 0; $i < $s; $i++) {
				   array_push($tmp2, substr($syms, mt_rand(0, strlen($syms) - 1), 1));
			  }
	 
			  // hack off a chunk of the base password array that's as big as the special chars array
			  $tmp1 = array_slice($tmp1, 0, $l - $count);
			  // merge special character(s) array with base password array
			  $tmp1 = array_merge($tmp1, $tmp2);
			  // mix the characters up
			  shuffle($tmp1);
			  // convert to string for output
			  $out = implode('', $tmp1);
		 }
	 
		 return $out;
        // func. generate
	}


    private $rounds;
    public function __construct($rounds = 12) {
        if(CRYPT_BLOWFISH != 1) {
            throw new Exception("bcrypt not supported in this installation. See http://php.net/crypt");
        }

        $this->rounds = $rounds;
        // func. __construct
    }

    public function hash($input) {
        $hash = crypt($input, $this->getSalt());

        if(strlen($hash) > 13)
            return $hash;

        return false;
        // func. hash
    }

    public function verify($input, $existingHash) {
        $hash = crypt($input, $existingHash);

        return $hash === $existingHash;
        // func. verify
    }

    private function getSalt() {
        $salt = sprintf('$2a$%02d$', $this->rounds);

        $bytes = $this->getRandomBytes(16);

        $salt .= $this->encodeBytes($bytes);

        return $salt;
        // func. getSalt
    }

    private $randomState;
    private function getRandomBytes($count) {
        $bytes = '';

        if(function_exists('openssl_random_pseudo_bytes') &&
            (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN')) { // OpenSSL slow on Win
            $bytes = openssl_random_pseudo_bytes($count);
        }

        if($bytes === '' && is_readable('/dev/urandom') &&
            ($hRand = @fopen('/dev/urandom', 'rb')) !== FALSE) {
            $bytes = fread($hRand, $count);
            fclose($hRand);
        }

        if(strlen($bytes) < $count) {
            $bytes = '';

            if($this->randomState === null) {
                $this->randomState = microtime();
                if(function_exists('getmypid')) {
                    $this->randomState .= getmypid();
                }
            }

            for($i = 0; $i < $count; $i += 16) {
                $this->randomState = md5(microtime() . $this->randomState);

                if (PHP_VERSION >= '5') {
                    $bytes .= md5($this->randomState, true);
                } else {
                    $bytes .= pack('H*', md5($this->randomState));
                }
            }

            $bytes = substr($bytes, 0, $count);
        }

        return $bytes;
        // func. getRandomBytes
    }

    private function encodeBytes($input) {
        // The following is code from the PHP Password Hashing Framework
        $itoa64 = './ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';

        $output = '';
        $i = 0;
        do {
            $c1 = ord($input[$i++]);
            $output .= $itoa64[$c1 >> 2];
            $c1 = ($c1 & 0x03) << 4;
            if ($i >= 16) {
                $output .= $itoa64[$c1];
                break;
            }

            $c2 = ord($input[$i++]);
            $c1 |= $c2 >> 4;
            $output .= $itoa64[$c1];
            $c1 = ($c2 & 0x0f) << 2;

            $c2 = ord($input[$i++]);
            $c1 |= $c2 >> 6;
            $output .= $itoa64[$c1];
            $output .= $itoa64[$c2 & 0x3f];
        } while (1);

        return $output;
        // func. encodeBytes
    }


}