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

use OCA\Postmag\AppInfo\Application;
use OCA\Postmag\Db\Alias;
use OCA\Postmag\Db\User;
use OCA\Postmag\Service\ConfigService;
use OCA\Postmag\Share\Random;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\JSONResponse;
use Test\TestCase;

/**
 * @group DB
 */
class MailControllerTest extends TestCase {
    
    private $controller;
    private $aliasMapper;
    private $userMapper;
    private $userId = 'john';
    
    private $alias;
    private $user;
    private $config;
    private $serverConf;
    private $smtpPort;
    
    public function setUp(): void {
        parent::setUp();
        $app = new Application();
        $container = $app->getContainer();
        
        // replace user id
        $container->registerService('UserId', function ($c) {
            return $this->userId;
        });
        
        $this->controller = $container->get('OCA\Postmag\Controller\MailController');
        $this->aliasMapper = $container->get('OCA\Postmag\Db\AliasMapper');
        $this->userMapper = $container->get('OCA\Postmag\Db\UserMapper');

        // Get current config and serverConf
        $this->config = $container->get('OCA\Postmag\Controller\ConfigController')->getConf()->getData();
        $this->serverConf = $container->get('OCP\IConfig');
        $this->smtpPort = $this->serverConf->getSystemValue('mail_smtpport');

        // Create some alias and user for testing
        $this->user = $this->createUser($this->userId);
        $this->alias = $this->createAlias($this->userId, "alias1", "john@doe.com", "First alias", true);
    }
    
    private function createAlias(string $userId, string $aliasName, string $toMail, string $comment, bool $enabled): Alias {
        $now = new \DateTime('now');
        
        $alias = new Alias();
        $alias->setUserId($userId);
        $alias->setAliasId(Random::hexString(ConfigService::DEF_ALIAS_ID_LEN));
        $alias->setAliasName($aliasName);
        $alias->setToMail($toMail);
        $alias->setComment($comment);
        $alias->setEnabled($enabled);
        $alias->setCreated($now->getTimestamp());
        $alias->setLastModified($now->getTimestamp());
        
        return $this->aliasMapper->insert($alias);
    }

    private function createUser(string $userId): User {
        $user = new User();
        $user->setUserId($userId);
        $user->setUserAliasId(Random::hexString(ConfigService::DEF_ALIAS_ID_LEN));

        return $this->userMapper->insert($user);
    }
    
    public function tearDown(): void {
        $this->aliasMapper->delete($this->alias);
        $this->userMapper->delete($this->user);

        $this->serverConf->setSystemValue('mail_smtpport', $this->smtpPort);
        
        parent::tearDown();
    }

    public function testSendTest(): void {
        $ret = $this->controller->sendTest($this->alias->getId());

        $toMail = $this->alias->getAliasName()
                  ."."
                  .$this->alias->getAliasId()
                  ."."
                  .$this->user->getUserAliasId()
                  ."@"
                  .$this->config['domain'];

        $this->assertTrue($ret instanceof JSONResponse, 'Result should be a JSON response.');
        $this->assertSame(Http::STATUS_OK, $ret->getStatus(), 'HTTP status should be OK.');
        $this->assertSame(["recipient" => $toMail], $ret->getData(), 'Did not return the expected recipient.');
    }

    public function testSendTestNotFound(): void {
        $ret = $this->controller->sendTest($this->alias->getId()+1);

        $this->assertTrue($ret instanceof JSONResponse, 'Result should be a JSON response.');
        $this->assertSame(Http::STATUS_NOT_FOUND, $ret->getStatus(), 'HTTP status should be NOT_FOUND.');
        $this->assertSame("string", gettype($ret->getData()['message']), 'Data should hold a status message.');
    }

    public function testSendTestMailError(): void {
        // Change the SMTP config to provoke an error
        $this->serverConf->setSystemValue('mail_smtpport', strval(intval($this->smtpPort)+1));

        $ret = $this->controller->sendTest($this->alias->getId());

        $this->assertTrue($ret instanceof JSONResponse, 'Result should be a JSON response.');
        $this->assertSame(Http::STATUS_BAD_GATEWAY, $ret->getStatus(), 'HTTP status should be BAD_GATEWAY.');
        $this->assertSame("string", gettype($ret->getData()['message']), 'Data should hold a status message.');
    }
    
}