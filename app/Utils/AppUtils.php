<?php

namespace App\Utils;

use Illuminate\Support\Facades\Crypt;

class AppUtils {
    public static function generateOTP(int $length): string {
        $generator = "1234567890abcdefghijklmnopqrstuvwxyz";
        $result = "";

        for ($i = 1; $i <= $length; $i++) {
            $result .= substr($generator, (rand() % (strlen($generator))), 1);
        }

        return $result;
    }

    /**
     * Encrypt a message
     *
     * @param string $message - message to encrypt
     * @return string
     */
    public static function safeEncrypt(string $message): string {
        return Crypt::encryptString($message);
    }

    /**
     * Decrypt a message
     *
     * @param string $encrypted - message encrypted with safeEncrypt()
     * @return string|null
     */
    public static function safeDecrypt(string $encrypted): string {
        return Crypt::decryptString($encrypted);
    }
}
