<?php
namespace App;

// 원문 출처:
// https://code.i-harness.com/ko-kr/q/8d541d

class UnsafeCrypto
{
    const METHOD = 'aes-256-ctr';

    /**
     * Encrypts (but does not authenticate) a message
     * 
     * @param string $message - plaintext message
     * @param string $key - encryption key (raw binary expected)
     * @param boolean $encode - set to TRUE to return a base64-encoded 
     * @return string (raw binary)
     */
    public static function encrypt($message, $key, $encode = false)
    {
        $nonceSize = openssl_cipher_iv_length(self::METHOD);
        $nonce = openssl_random_pseudo_bytes($nonceSize);

        $ciphertext = openssl_encrypt(
            $message,
            self::METHOD,
            $key,
            OPENSSL_RAW_DATA,
            $nonce
        );

        // Now let's pack the IV and the ciphertext together
        // Naively, we can just concatenate
        if ($encode) {
            return base64_encode($nonce.$ciphertext);
        }
        return $nonce.$ciphertext;
    }

    /**
     * Decrypts (but does not verify) a message
     * 
     * @param string $message - ciphertext message
     * @param string $key - encryption key (raw binary expected)
     * @param boolean $encoded - are we expecting an encoded string?
     * @return string
     */
    public static function decrypt($message, $key, $encoded = false)
    {
        if ($encoded) {
            $message = base64_decode($message, true);
            if ($message === false) {
                throw new \Exception('Encryption failure');
            }
        }

        $nonceSize = openssl_cipher_iv_length(self::METHOD);
        $nonce = mb_substr($message, 0, $nonceSize, '8bit');
        $ciphertext = mb_substr($message, $nonceSize, null, '8bit');

        $plaintext = openssl_decrypt(
            $ciphertext,
            self::METHOD,
            $key,
            OPENSSL_RAW_DATA,
            $nonce
        );

        return $plaintext;
    }
}

class Encryption extends UnsafeCrypto
{
    const HASH_ALGO = 'sha256';
    
	// -MANAPIE-
    // 평문인지 암호문인지 구별하기 위한 문자열
    const DISTINGUISHER = '=MNP1E!';
    const RT_DISTINGUISHER = '=mnp1e!';
    
	// -MANAPIE-
	// APP_KEY를 바이너리 변환해서 키로 쓸 수 있게끔
	public static function textBinASCII($text){
		$bin = array();
	    for($i=0; strlen($text)>$i; $i++)
	    	$bin[] = decbin(ord($text[$i]));
	    return implode('',$bin);
	}
	
	// -MANAPIE-
    // 암호문인지 확인
    public static function checkEncrypted($message) {
	    return self::nrt_checkEncrypted($message) || self::rt_checkEncrypted($message);
    }
    
    public static function nrt_checkEncrypted($message) {
	    return mb_substr($message,0,strlen(self::DISTINGUISHER))===self::DISTINGUISHER;
    }
    
    public static function rt_checkEncrypted($message) {
	    return mb_substr($message,0,strlen(self::RT_DISTINGUISHER))===self::RT_DISTINGUISHER;
    }
	
	// -MANAPIE-
	// 어떤 모듈이 암호화 중인지 확인
	public static function isEncrypt($module){
		$setting=\DB::table('encryption_settings')->where('module',$module)->first();
		if(!$setting||$setting->encrypt==0)
			return false;
		else
			return true;
	}
	
	// -MANAPIE-
	// 암호화할 때마다 같은 값이 나오도록 하는 암호화.. 레인보우테이블을 만들 수 있다는 의미에서 rt 붙임^^
    public static function rt_encrypt($message, $key=null){
	    if($key===null)
	    	$key=env('APP_KEY');
	    	
	    return self::RT_DISTINGUISHER.base64_encode(openssl_encrypt($message,'aes-256-ctr',$key,true,mb_substr($key,8,16)));
    }
	
	// -MANAPIE-
	// rt_encrypt()의 복호화 함수
    public static function rt_decrypt($message, $key=null){
	    if(self::rt_checkEncrypted($message)){
            throw new \Exception('Encryption failure: message is not encrypted');
        }
        
        $message=mb_substr($message,strlen(self::RT_DISTINGUISHER));
        
	    if($key===null)
	    	$key=env('APP_KEY');
	    	
	    return openssl_decrypt(base64_decode($message),'aes-256-ctr',$key,true,mb_substr($key,8,16));
    }

    /**
     * Encrypts then MACs a message
     * 
     * @param string $message - plaintext message
     * @param string $key - encryption key (raw binary expected)
     * @param boolean $encode - set to TRUE to return a base64-encoded string
     * @return string (raw binary)
     */
    public static function encrypt($message, $key=null, $encode = true)
    {
	    // -MANAPIE-
	    // key로 Laravel의 APP_KEY를 사용할 수 있게 함
	    if($key===null)
	    	$key=self::textBinASCII(env('APP_KEY'));
	    
        list($encKey, $authKey) = self::splitKeys($key);

        // Pass to UnsafeCrypto::encrypt
        $ciphertext = parent::encrypt($message, $encKey);

        // Calculate a MAC of the IV and ciphertext
        $mac = hash_hmac(self::HASH_ALGO, $ciphertext, $authKey, true);

        if ($encode) {
            return self::DISTINGUISHER.base64_encode($mac.$ciphertext);
        }
        
        // Prepend MAC to the ciphertext and return to caller
        return self::DISTINGUISHER.$mac.$ciphertext;
    }

    /**
     * Decrypts a message (after verifying integrity)
     * 
     * @param string $message - ciphertext message
     * @param string $key - encryption key (raw binary expected)
     * @param boolean $encoded - are we expecting an encoded string?
     * @return string (raw binary)
     */
    public static function decrypt($message, $key=null, $encoded = true)
    {
	    // -MANAPIE-
	    // 암호문인지 확인
	    if(self::rt_checkEncrypted($message)){
		    return self::rt_decrypt($message,$key);
	    }
	    
	    if(!self::nrt_checkEncrypted($message)){
            throw new \Exception('Encryption failure: message is not encrypted');
        }
        
        $message=mb_substr($message,strlen(self::DISTINGUISHER));
	    
	    
	    // -MANAPIE-
	    // key로 Laravel의 APP_KEY를 사용할 수 있게 함
	    if($key===null)
	    	$key=self::textBinASCII(env('APP_KEY'));
	    
        list($encKey, $authKey) = self::splitKeys($key);
        if ($encoded) {
            $message = base64_decode($message, true);
            if ($message === false) {
                throw new \Exception('Encryption failure');
            }
        }

        // Hash Size -- in case HASH_ALGO is changed
        $hs = mb_strlen(hash(self::HASH_ALGO, '', true), '8bit');
        $mac = mb_substr($message, 0, $hs, '8bit');

        $ciphertext = mb_substr($message, $hs, null, '8bit');

        $calculated = hash_hmac(
            self::HASH_ALGO,
            $ciphertext,
            $authKey,
            true
        );

        if (!self::hashEquals($mac, $calculated)) {
            throw new Exception('Encryption failure');
        }

        // Pass to UnsafeCrypto::decrypt
        $plaintext = parent::decrypt($ciphertext, $encKey);

        return $plaintext;
    }

    /**
     * Splits a key into two separate keys; one for encryption
     * and the other for authenticaiton
     * 
     * @param string $masterKey (raw binary)
     * @return array (two raw binary strings)
     */
    protected static function splitKeys($masterKey)
    {
        // You really want to implement HKDF here instead!
        return [
            hash_hmac(self::HASH_ALGO, 'ENCRYPTION', $masterKey, true),
            hash_hmac(self::HASH_ALGO, 'AUTHENTICATION', $masterKey, true)
        ];
    }

    /**
     * Compare two strings without leaking timing information
     * 
     * @param string $a
     * @param string $b
     * @ref https://paragonie.com/b/WS1DLx6BnpsdaVQW
     * @return boolean
     */
    protected static function hashEquals($a, $b)
    {
        if (function_exists('hash_equals')) {
            return hash_equals($a, $b);
        }
        $nonce = openssl_random_pseudo_bytes(32);
        return hash_hmac(self::HASH_ALGO, $a, $nonce) === hash_hmac(self::HASH_ALGO, $b, $nonce);
    }
}