<?php

/**
 * Class designed to deal with decrypting strings sent to it, either using XOR
 * or AES encryption.
 *
 * To use this class you create it, then set the encryption type and then call
 * decrypt().
 *
 * Id the string was base64 encoded, you also need to call decode().
 *
 * EG:
 *
 * Using simple XOR encryption:
 *
 * $encrypt = StringEncryptor::create('encrypt this')
 *              ->setHash('hashcode')
 *              ->decrypt()
 *              ->get();
 *
 *
 * XOR encryption then base 64 encoded:
 *
 * $encrypt = StringEncryptor::create('encrypt this')
 *              ->setHash('hashcode')
 *              ->decode()
 *              ->decrypt()
 *              ->get();
 *
 * MCrypt AES encryption
 *
 * $encrypt = StringEncryptor::create('encrypt this')
 *              ->setHash('hashcode')
 *              ->setEncryption('MCRYPT')
 *              ->decrypt()
 *              ->get();
 */

class StringDecryptor {

    /**
     * String that we are going to decrypt
     * @var String
     */
    private $data;

    /**
     * String that is being decrypted
     * @var String
     */
    private $decrypted_data;

    /**
     * Choose encryption type
     * @var String
     * @default XOR
     */
    private $encryption = 'XOR';

    /**
     * Hash used to decrypt our string
     * @var String
     */
    private $hash;

    private function __construct($string) {
        $this->data = $string;
    }

    /**
     * Factory method allowing chaining
     *
     * @para, $string string to encrypt
     * @return StringEncryptor
     */
    public static function create($string) {
        return new StringDecryptor($string);
    }

    /**
     * Set our encryption type
     *
     * @param $type Type of encryption
     * @return self
     */
    public function setEncryption($type) {
        $this->encryption = $type;
        return $this;
    }

    /**
     * Set our hash
     *
     * @param $hash
     * @return self
     */
    public function setHash($hash) {
        $this->hash = $hash;
        return $this;
    }

    /**
     * Get our encrypted data
     *
     * @return String
     */
    public function get() {
        return $this->encrypted_data;
    }

    /**
     * Base 64 encode the data, ready for transit
     *
     * @return self
     */
    public function decode() {
        // Fix plus to space conversion issue
        $this->data = str_replace(' ','+',$this->data);

        // Do decoding
        $this->data = base64_decode($this->data);

        return $this;
    }

    /**
     * Perform our data encryption
     *
     * @return self
     */
    public function decrypt() {
        if($this->encryption == 'XOR')
            $this->encrypted_data = $this->simplexor();
        elseif($this->encryption == 'MCRYPT')
            $this->encrypted_data = $this->mcrypt();

        return $this;
    }


    /**
     * SimpleXor encryption algorithm
     *
     * return self
     */
    private function simplexor() {
        $KeyList = array();
        $output = "";

        // Convert $Key into array of ASCII values
        for($i = 0; $i < strlen($this->hash); $i++) {
            $KeyList[$i] = ord(substr($this->hash, $i, 1));
        }

        // Step through string a character at a time
        for($i = 0; $i < strlen($this->data); $i++) {
            $output.= chr(ord(substr($this->data, $i, 1)) ^ ($KeyList[$i % strlen($this->hash)]));
        }

        // Return the result
        return $output;
    }

    /**
     * Encrypt our data using PHP mcrypt and AES with PKCS5 padding
     *
     * @return string
     */
    private function mcrypt() {
        // HEX decoding
        $data = pack('H*', $this->data);

        // Decrypt string
        $output = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $this->hash, $data, MCRYPT_MODE_CBC, $this->hash);

        // Perform decryption with PHP's MCRYPT module
        $output = $this->removePKCS5Padding($output);

        return $output;
    }

    /**
     * PHP's mcrypt does not have built in PKCS5 Padding, so we use this
     *
     * @return string
     */
    private function removePKCS5Padding($decrypt) {
        $padChar = ord($decrypt[strlen($decrypt) - 1]);
        return substr($decrypt, 0, -$padChar);
    }
}
