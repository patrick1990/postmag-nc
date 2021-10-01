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

namespace OCA\Postmag\Tests\Integration\Db;

use OCA\Postmag\AppInfo\Application;
use OCA\Postmag\Db\User;
use Test\TestCase;

/**
 * @group DB
 */
class UserMapperTest extends TestCase {
    
    private $mapper;
    private $userId = 'john';
    
    private $testUser;
    private $insertedUser;
    
    public function setUp(): void {
        parent::setUp();
        $app = new Application();
        $container = $app->getContainer();
        
        // replace user id
        $container->registerService('UserId', function ($c) {
            return $this->userId;
        });
        
        $this->mapper = $container->get('OCA\Postmag\Db\UserMapper');
        
        // Create test user
        $this->testUser = new User();
        $this->testUser->setUserId($this->userId);
        $this->testUser->setUserAliasId('2a9f');
        
        // Insert user into database
        $this->insertedUser = $this->mapper->insert($this->testUser);
    }
    
    public function tearDown(): void {
        // Clean up
        $this->mapper->delete($this->insertedUser);
        
        parent::tearDown();
    }
    
    public function testFindUser(): void {
        $ret = $this->mapper->findUser($this->userId);
        
        $this->assertTrue($ret instanceof User, 'Result should be a User entity.');
        $this->assertSame($this->insertedUser->getId(), $ret->getId(), 'Did not return the expected id.');
        $this->assertSame($this->testUser->getUserId(), $ret->getUserId(), 'Did not return the expected user id.');
        $this->assertSame($this->testUser->getUserAliasId(), $ret->getUserAliasId(), 'Did not return the expected user alias id.');
    }
    
    public function testContainsAliasId(): void {
        $this->assertTrue(
            $this->mapper->containsAliasId($this->testUser->getUserAliasId()),
            'User alias id was allready present but not found.'
            );
        $this->assertFalse(
            $this->mapper->containsAliasId(strrev($this->testUser->getUserAliasId())),
            'User alias id was not present but found.'
            );
    }
    
}