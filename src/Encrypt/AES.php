<?php
namespace Yurun\Proxy\Encrypt;

class AES
{
    public static function encrypt($data, $key, $iv = '0000000000000000')
    {
		return openssl_encrypt ($data, 'aes-256-cbc', $key, 0, $iv);
    }

    public static function decrypt($data, $key, $iv = '0000000000000000')
    {
		return openssl_decrypt ($data, 'aes-256-cbc', $key, 0, $iv);
    }

}
