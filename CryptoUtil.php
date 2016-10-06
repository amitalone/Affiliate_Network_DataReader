<?php
class CryptoUtil
{

	public static function crossConvert($text, $key) {
	 	if(strlen($key) < strlen($text)) {
	 		while(strlen($key) != strlen($text)) {
	 			$key = $key."A";
	 		}
	 	}
	 	for($i=0; $i<strlen($text); $i++) {
	 		$assci = ord($text{$i});
	 		$maskbit =  ord($key{$i});
	 		$assci = $assci ^ $maskbit;
	 		$text{$i} = chr($assci);
	 	}
	 	return $text;
  }
	
	public static function convert($text, $key = '') {
	    // return text unaltered if the key is blank
	    if ($key == '') {
	        return $text;
	    }
	
	    // remove the spaces in the key
	    $key = str_replace(' ', '', $key);
	    if (strlen($key) < 8) {
	        exit('key error');
	    }
	    // set key length to be no more than 32 characters
	    $key_len = strlen($key);
	    if ($key_len > 32) {
	        $key_len = 32;
	    }
	
	    $k = array(); // key array
	    // fill key array with the bitwise AND of the ith key character and 0x1F
	    for ($i = 0; $i < $key_len; ++$i) {
	        $k[$i] = ord($key{$i}) & 0x1F;
	    }
	
	    // perform encryption/decryption
	    for ($i = 0, $j = 0; $i < strlen($text); ++$i) {
	        $e = ord($text{$i});
	        // if the bitwise AND of this character and 0xE0 is non-zero
	        // set this character to the bitwise XOR of itself and the jth key element
	        // else leave this character alone
	        if ($e & 0xE0) {
	            $text{$i} = chr($e ^ $k[$j]);
	        }
	        // increment j, but ensure that it does not exceed key_len-1
	        $j = ($j + 1) % $key_len;
	    }
	    return $text;
	}
}
?>