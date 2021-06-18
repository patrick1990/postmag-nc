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

namespace OCA\Postmag\Tests\Integration\Controller;

use Test\TestCase;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\App;
use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\Http;
use OCA\Postmag\Db\User;
use OCA\Postmag\Service\ConfigService;

/**
 * @group DB
 */
class UserControllerTest extends TestCase {
    
    private $controller;
    private $mapper;
    private $userId = 'john';
    
    private $user;
    private $config;
    
    public function setUp(): void {
        parent::setUp();
        $app = new App('postmag');
        $container = $app->getContainer();
        
        // replace user id
        $container->registerService('UserId', function ($c) {
            return $this->userId;
        });
        
        $this->controller = $container->get('OCA\Postmag\Controller\UserController');
        $this->mapper = $container->get('OCA\Postmag\Db\UserMapper');

        // Get current config
        $this->config = $container->get('OCA\Postmag\Controller\ConfigController')->getConf()->getData();

        // Create user alias id of john
        $user = new User();
        $user->setUserId($this->userId);
        $user->setUserAliasId('1a2b');
        $this->user = $this->mapper->insert($user);
    }
    
    public function tearDown(): void {
        // Delete user alias id of john if found
        $user = $this->mapper->findUser($this->userId);
        $this->mapper->delete($user);
        
        parent::tearDown();
    }
    
    public function testGetExistingUserInfo(): void {
        $ret = $this->controller->getInfo();
        
        $this->assertTrue($ret instanceof JSONResponse, 'Result should be a JSON response.');
        $this->assertSame(Http::STATUS_OK, $ret->getStatus(), 'HTTP status should be OK.');
        $this->assertSame('', $ret->getData()['email'], 'Did not return the expected email address');
        $this->assertSame('false', $ret->getData()['email_set'], 'Did not return the expected email set flag');
        $this->assertSame($this->userId, $ret->getData()['user_id'], 'Did not return the expected user alias id');
        $this->assertSame($this->user->getUserAliasId(), $ret->getData()['user_alias_id'], 'Did not return the expected user alias id');
    }
    
    public function testNewUserInfo(): void {
        // delete user john
        $this->mapper->delete($this->user);
        
        $ret = $this->controller->getInfo();
        
        $this->assertTrue($ret instanceof JSONResponse, 'Result should be a JSON response.');
        $this->assertSame(Http::STATUS_OK, $ret->getStatus(), 'HTTP status should be OK.');
        $this->assertSame('', $ret->getData()['email'], 'Did not return the expected email address');
        $this->assertSame('false', $ret->getData()['email_set'], 'Did not return the expected email set flag');
        $this->assertSame($this->userId, $ret->getData()['user_id'], 'Did not return the expected user alias id');
        $this->assertSame(1, preg_match("/^[0-9a-f]*$/", $ret->getData()['user_alias_id']), 'user alias is not a hexadecimal string.');
        $this->assertSame($this->config['userAliasIdLen'], strlen($ret->getData()['user_alias_id']), 'user alias is of wrong length.');
    }
    
}