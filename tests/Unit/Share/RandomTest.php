<?php
declare(strict_types=1);

/**
 * @author Patrick Greyson
 *
 * Postmag - Postfix mail alias generator for Nextcloud
 * Copyright (C) 2021
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

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