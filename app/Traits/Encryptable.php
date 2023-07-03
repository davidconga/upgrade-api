<?php 

namespace App\Traits;

trait Encryptable
{
    /**
     * If the attribute is in the encryptable array
     * then decrypt it.
     *
     * @param  $key
     *
     * @return $value
     */
    public function getAttribute($key)
    {
        $value = parent::getAttribute($key);
        if (in_array($key, $this->encryptable) && $value !== '') {
            $value = $this->cusdecrypt($value, env('DB_SECRET'));
        }

        return $value;
    }
    /**
     * If the attribute is in the encryptable array
     * then encrypt it.
     *
     * @param $key
     * @param $value
     */
    public function setAttribute($key, $value)
    {
        if (in_array($key, $this->encryptable)) {
            $value = $this->cusencrypt($value,env('DB_SECRET'));
        }
        return parent::setAttribute($key, $value);
    }
    /**
     * When need to make sure that we iterate through
     * all the keys.
     *
     * @return array
     */
    public function attributesToArray()
    {
        $attributes = parent::attributesToArray();
        foreach ($this->encryptable as $key) {
            if (isset($attributes[$key])) {
                $attributes[$key] = $this->cusdecrypt($attributes[$key], env('DB_SECRET'));

            }
        }
        return $attributes;
    }

    /**
     * Returns an encrypted & utf8-encoded
     */
    protected function cusencrypt($pure_string, $passphrase) {
        
        $salt = 'f91f36afebd2879da69cccd0a13b353580164f620bba6696d3b7fc74a6c720081491402add94c646690a231b31d2d9a38bb6ad7b558fcfb7d43915c63e9ecffeacd3fab8e27798e69bfa5cc90f454c9d6a4f2f393594158233abd9a7aec13431953805ff78b94373eb7214049101c630db254fdb28abe7ecf8e878cebdb16ee9';
        $iv = '7f00157593ac8252';
        //on PHP7 can use random_bytes() istead openssl_random_pseudo_bytes()
        //or PHP5x see : https://github.com/paragonie/random_compat

        $iterations = 999;  
        $key = hash_pbkdf2("sha512", $passphrase, $salt, $iterations, 64);

        $encrypted_data = openssl_encrypt($pure_string, 'aes-256-cbc', hex2bin($key), OPENSSL_RAW_DATA, $iv);

        
        return base64_encode($encrypted_data);
    }

    /**
     * Returns decrypted original string
     */
    protected function cusdecrypt($encrypted_string, $passphrase) {
        
        $salt = 'f91f36afebd2879da69cccd0a13b353580164f620bba6696d3b7fc74a6c720081491402add94c646690a231b31d2d9a38bb6ad7b558fcfb7d43915c63e9ecffeacd3fab8e27798e69bfa5cc90f454c9d6a4f2f393594158233abd9a7aec13431953805ff78b94373eb7214049101c630db254fdb28abe7ecf8e878cebdb16ee9';
        $iv = '7f00157593ac8252';
        $ciphertext = base64_decode($encrypted_string);
        $iterations = 999; //same as js encrypting 

        $key = hash_pbkdf2("sha512", $passphrase, $salt, $iterations, 64);

        $decrypted= openssl_decrypt($ciphertext , 'aes-256-cbc', hex2bin($key), OPENSSL_RAW_DATA, $iv);

        return $decrypted;
    }
}