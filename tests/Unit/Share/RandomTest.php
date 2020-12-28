<?php
declare(strict_types=1);

namespace OCA\Postmag\Tests\Unit\Share;

use PHPUnit\Framework\TestCase;
use OCA\Postmag\Share\Random;

class RandomTest extends TestCase {
    
    private $hexStrLen = 7;
    
    public function testHexString(): void {
        $str = Random::hexString($this->hexStrLen);
        
        $this->assertSame('string', gettype($str), 'hexString is not of type string.');
        $this->assertSame(1, preg_match("/^[0-9a-f]*$/", $str), 'hexString is not a hexadecimal string.');
        $this->assertSame($this->hexStrLen, strlen($str), 'hexString is of wrong length.');
    }
    
}