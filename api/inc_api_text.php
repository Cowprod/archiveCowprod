<?php

function encrypt($data, $key)
{
    return cryptor::encrypt($key, (string) $data);
}

function decrypt($data, $key)
{
    return cryptor::decrypt($key, (string) $data);
}

class cryptor
{
    private static $salt = 'salt';
    private static $hashlength = 30;

    public static function encrypt($secret, $plaintext)
    {
        $key = self::create_32bit_password($secret);
        $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);

        $ciphertext = bin2hex(
            sodium_crypto_secretbox($plaintext, $nonce, $key)
        );

        $nonceHex = bin2hex($nonce);
        $hash = self::create_hash($ciphertext . $nonceHex);

        return $ciphertext . $nonceHex . $hash;
    }

    public static function decrypt($secret, $ciphertext)
    {
        $hash = substr($ciphertext, -self::$hashlength);
        $ciphertext = substr($ciphertext, 0, -self::$hashlength);
        $hashOnTheFly = self::create_hash($ciphertext);

        if ($hash !== $hashOnTheFly) {
            return 'error';
        }

        $nonceHex = substr($ciphertext, -48);
        $ciphertext = substr($ciphertext, 0, -48);
        $nonce = hex2bin($nonceHex);

        if ($nonce === false) {
            return 'error';
        }

        $plaintext = sodium_crypto_secretbox_open(
            hex2bin($ciphertext),
            $nonce,
            self::create_32bit_password($secret)
        );

        return $plaintext === false ? 'error' : $plaintext;
    }

    private static function create_32bit_password($secret)
    {
        return substr(bin2hex(sodium_crypto_generichash($secret . self::$salt)), 0, 32);
    }

    private static function create_hash($ciphertextAndNonce)
    {
        return substr(bin2hex(sodium_crypto_generichash($ciphertextAndNonce)), 0, self::$hashlength);
    }
}

function decryptId($sEncryptedId, string $sEncryptKey): int
{
    $sDecryptedId = decrypt((string) $sEncryptedId, $sEncryptKey);

    if ($sDecryptedId === 'error' || !ctype_digit((string) $sDecryptedId) || (int) $sDecryptedId <= 0) {
        throw new RuntimeException('Identifiant invalide');
    }

    return (int) $sDecryptedId;
}
