<?php
namespace OCA\Postmag\Share;

class Random {
    
    public static function hexString(int $len): string {
        $base = bin2hex(random_bytes(ceil($len/2)));
        return substr($base, 0, $len);
    }
    
}