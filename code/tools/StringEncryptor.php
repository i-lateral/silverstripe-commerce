<?php

/**
 * Class designed to deal with encrypting strings sent to it, either using XOR
 * or AES encryption.
 *
 * To use this class you create it, then set the encryption type and then call
 * encrypt().
 *
 * EG:
 *
 * Using simple XOR encryption:
 *
 * $encrypt = StringEncryptor::create('encrypt this')
 *              ->setHash('hashcode')
 *              ->encrypt()
 *              ->get();
 *
 *
 * XOR encryption then base 64 encoded:
 *
 * $encrypt = StringEncryptor::create('encrypt this')
 *              ->setHash('hashcode')
 *              ->encrypt()
 *              ->encode()
 *              ->get();
 *
 * MCrypt AES encryption then base 64 encoded:
 *
 * $encrypt = StringEncryptor::create('encrypt this')
 *              ->setHash('hashcode')
 *              ->setEncryption('MCRYPT')
 *              ->encrypt()
 *              ->encode()
 *              ->get();
 *
 */

class StringEncryptor {

    /**
     * String that we are going to encrypt
     * @var String
     */
    private $data;

    /**
     * String that is being encrypted
     * @var String
     */
    private $encrypted_data;

    /**
     * Choose encryption type
     * @var String
     * @default XOR
     */
    private $encryption = 'XOR';

    /**
     * Hash used to encrypt our string
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
        return new StringEncryptor($string);
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
     * Perform our data encryption
     *
     * @return self
     */
    public function encrypt() {
        if($this->encryption == 'XOR')
            $this->encrypted_data = $this->simplexor();
        elseif($this->encryption == 'MCRYPT')
            $this->encrypted_data = $this->mcrypt();

        return $this;
    }

    /**
     * Base 64 encode the data, ready for transit
     *
     * @return self
     */
    public function encode() {
        // Encode data string
        $this->encrypted_data = base64_encode($this->encrypted_data);

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
     * @return self
     */
    private function mcrypt() {
        // add PKCS5 padding to the text to be encypted
        $strIn = $this->addPKCS5Padding();

        // perform encryption with PHP's MCRYPT module
        $output = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $this->hash, $strIn, MCRYPT_MODE_CBC, $this->hash);

        // perform hex encoding and return
        return bin2hex($output);
    }

    /**
     * PHP's mcrypt does not have built in PKCS5 Padding, so we use this
     */
    private function addPKCS5Padding() {
       $blocksize = 16;
       $padding = "";

       // Pad input to an even block size boundary
       $padlength = $blocksize - (strlen($this->data) % $blocksize);
       for($i = 1; $i <= $padlength; $i++) {
          $padding .= chr($padlength);
       }

       return $this->data . $padding;
    }
}
