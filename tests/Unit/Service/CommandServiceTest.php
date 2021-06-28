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

namespace OCA\Postmag\Tests\Unit\Service;

use OCA\Postmag\Service\AliasService;
use PHPUnit\Framework\TestCase;
use OCA\Postmag\Db\AliasMapper;
use OCA\Postmag\Service\UserService;
use OCA\Postmag\Service\CommandService;
use OCA\Postmag\Db\Alias;

class CommandServiceTest extends TestCase {
    
    private $service;
    private $mapper;
    private $aliasService;
    private $userService;
    
    private $aliases;
    
    public function setUp(): void {
        $this->mapper = $this->createMock(AliasMapper::class);
        $this->aliasService = $this->createMock(AliasService::class);
        $this->userService = $this->createMock(UserService::class);
        
        $this->service = new CommandService(
            $this->mapper,
            $this->aliasService,
            $this->userService
        );
        
        $this->aliases = [new Alias(), new Alias(), new Alias()];
        
        $this->aliases[0]->setUserId('john');
        $this->aliases[0]->setAliasId('1a2b');
        $this->aliases[0]->setAliasName('alias');
        $this->aliases[0]->setToMail('john@doe.com');
        $this->aliases[0]->setComment('My Alias New');
        $this->aliases[0]->setEnabled(true);
        $this->aliases[0]->setCreated(12345);
        $this->aliases[0]->setLastModified(23456);
        
        $this->aliases[1]->setUserId('john');
        $this->aliases[1]->setAliasId('0123');
        $this->aliases[1]->setAliasName('alias');
        $this->aliases[1]->setToMail('john@doe.com');
        $this->aliases[1]->setComment('My Alias');
        $this->aliases[1]->setEnabled(false);
        $this->aliases[1]->setCreated(12300);
        $this->aliases[1]->setLastModified(12340);
        
        $this->aliases[2]->setUserId('jane');
        $this->aliases[2]->setAliasId('2b3c');
        $this->aliases[2]->setAliasName('important');
        $this->aliases[2]->setToMail('jane@doe.com');
        $this->aliases[2]->setComment('Very important');
        $this->aliases[2]->setEnabled(true);
        $this->aliases[2]->setCreated(76543);
        $this->aliases[2]->setLastModified(87654);
    }
    
    public function testFormatPostfixAliasFile(): void {
        // Mocking
        $this->mapper->expects($this->once())
            ->method('findAll')
            ->with(null)
            ->willReturn($this->aliases);
        
        $getUserAliasId = function($userId) {
            if ($userId === 'john') {
                return '5432';
            }
            elseif ($userId === 'jane') {
                return 'cdef';
            }
            else {
                return '';
            }
        };
            
        $this->userService->expects($this->any())
            ->method('getUserAliasId')
            ->with($this->logicalOr('john', 'jane'))
            ->willReturnCallback($getUserAliasId);
        
        // Test method
        $ret = $this->service->formatPostfixAliasFile();
        
        $i = 0;
        foreach ($ret as $line) {
            if (($line === "") || ($line[0] === "#")) {
                // Skip disabled alias
                if ($line === "# ".$this->aliases[$i]->getAliasName()
                                ."."
                                .$this->aliases[$i]->getAliasId()
                                ."."
                                .$getUserAliasId($this->aliases[$i]->getUserId())
                                .": "
                                .$this->aliases[$i]->getToMail())
                {
                    $i = $i + 1;
                }
                
                // Skip comments
                continue;
            }
            
            
           
            $this->assertSame(
                $this->aliases[$i]->getAliasName()
                ."."
                .$this->aliases[$i]->getAliasId()
                ."."
                .$getUserAliasId($this->aliases[$i]->getUserId())
                .": "
                .$this->aliases[$i]->getToMail(),
                $line,
                "aliases are not formatted correctly");
            $i = $i + 1;
        }
    }
    
    public function testGetLastModified(): void {
        // Mocking
        $this->aliasService->expects($this->exactly(2))
            ->method('getLastModified')
            ->willReturn(strval($this->aliases[0]->getLastModified()));
        
        // Test method - unix time
        $ret = $this->service->getLastModified();
        
        $this->assertSame(
            $this->aliases[0]->getLastModified(),
            intval($ret), 
            "last modified date is not returned as correct unix time."
        );
        
        // Test method - formatted
        $ret = $this->service->getLastModified(true);
        
        $this->assertSame(
            (new \DateTime())->setTimestamp($this->aliases[0]->getLastModified())->format('Y-m-d_H:i:s'),
            $ret,
            "last modified date is not returned as correct formatted time."
        );
    }
    
}