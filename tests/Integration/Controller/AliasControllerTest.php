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
use OCA\Postmag\Service\ConfigService;
use OCA\Postmag\Share\Random;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\JSONResponse;
use Test\TestCase;

/**
 * @group DB
 */
class AliasControllerTest extends TestCase {
    
    private $controller;
    private $mapper;
    private $dateTimeFormatter;
    private $userId = 'john';
    
    private $aliases;
    private $config;
    
    public function setUp(): void {
        parent::setUp();
        $app = new Application();
        $container = $app->getContainer();
        
        // replace user id
        $container->registerService('UserId', function ($c) {
            return $this->userId;
        });
        
        $this->controller = $container->get('OCA\Postmag\Controller\AliasController');
        $this->mapper = $container->get('OCA\Postmag\Db\AliasMapper');
        $this->dateTimeFormatter = $container->get('OCP\IDateTimeFormatter');

        // Get current config
        $this->config = $container->get('OCA\Postmag\Controller\ConfigController')->getConf()->getData();

        // Create some aliases for testing
        $this->aliases = [];
        $this->aliases[] = $this->createAlias($this->userId, "alias1", "john@doe.com", "First alias", true);
        $this->aliases[] = $this->createAlias($this->userId, "alias2", "john@example.com", "Second alias", false);
        $this->aliases[] = $this->createAlias($this->userId, "alias3", "john.doe@domain.com", "Third alias", true);
        $this->aliases[] = $this->createAlias("jane", "alias1", "jane.doe@domain.com", "First alias", true);
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
        
        return $this->mapper->insert($alias);
    }
    
    public function tearDown(): void {
        foreach ($this->aliases as $alias) {
            $this->mapper->delete($alias);
        }
        
        parent::tearDown();
    }
    
    public function testIndex(): void {
        $ret = $this->controller->index(0, count($this->aliases));
        
        $this->assertTrue($ret instanceof JSONResponse, 'Result should be a JSON response.');
        $this->assertSame(Http::STATUS_OK, $ret->getStatus(), 'HTTP status should be OK.');
        
        foreach ($this->aliases as $expected) {
            $found = false;
            foreach ($ret->getData() as $actual) {
                if ($actual['id'] === $expected->getId()) {
                    $this->assertSame($expected->getUserId(), $actual['user_id'], strval($actual['id']).' has not the expected user id');
                    $this->assertSame($expected->getAliasId(), $actual['alias_id'], strval($actual['id']).' has not the expected alias id');
                    $this->assertSame($expected->getAliasName(), $actual['alias_name'], strval($actual['id']).' has not the expected alias name');
                    $this->assertSame($expected->getToMail(), $actual['to_mail'], strval($actual['id']).' has not the expected to mail');
                    $this->assertSame($expected->getComment(), $actual['comment'], strval($actual['id']).' has not the expected comment');
                    $this->assertSame($expected->getEnabled(), $actual['enabled'], strval($actual['id']).' has not the expected enabled state');
                    $this->assertSame(
                        $this->dateTimeFormatter->formatDateTime($expected->getCreated(), 'short', 'medium'),
                        $actual['created'],
                        strval($actual['id']).' has not the expected created timestamp'
                        );
                    $this->assertSame(
                        $this->dateTimeFormatter->formatDateTime($expected->getLastModified(), 'short', 'medium'),
                        $actual['last_modified'],
                        strval($actual['id']).' has not the expected last modified timestamp'
                        );
                    $this->assertSame($expected->getCreated(), $actual['created_utc'], strval($actual['id']).' has not the expected created utc timestamp');
                    $this->assertSame($expected->getLastModified(), $actual['last_modified_utc'], strval($actual['id']).' has not the expected last modified utc timestamp');
                    
                    $found = true;
                    break;
                }
            }

            if ($expected->getUserId() === $this->userId)
                $this->assertTrue($found, strval($expected->getId()).' was not returned for user '.$this->userId);
            else
                $this->assertFalse($found, strval($expected->getId()).' does not belong to user '.$this->userId);
        }
    }
    
    public function testCreate(): void {
        $ret = $this->controller->create('alias4', 'john@mydomain.org', 'Fourth alias');
        $now = new \DateTime('now');
        
        // Add ret to aliases to clean it up in tearDown
        $alias = new Alias();
        $alias->setId($ret->getData()['id']);
        $this->aliases[] = $alias;
        
        $this->assertTrue($ret instanceof JSONResponse, 'Result should be a JSON response.');
        $this->assertSame(Http::STATUS_OK, $ret->getStatus(), 'HTTP status should be OK.');
        $this->assertSame($this->userId, $ret->getData()['user_id'], 'Alias has not the expected user id');
        $this->assertSame(1, preg_match("/^[0-9a-f]*$/", $ret->getData()['alias_id']), 'Alias id is not a hexadecimal string.');
        $this->assertSame($this->config['aliasIdLen'], strlen($ret->getData()['alias_id']), 'Alias id is of wrong length.');
        $this->assertSame('alias4', $ret->getData()['alias_name'], 'Alias has not the expected alias name');
        $this->assertSame('john@mydomain.org', $ret->getData()['to_mail'], 'Alias has not the expected to mail');
        $this->assertSame('Fourth alias', $ret->getData()['comment'], 'Alias has not the expected comment');
        $this->assertSame(true, $ret->getData()['enabled'], 'Alias has not the expected enabled state');
        $this->assertSame(
            $this->dateTimeFormatter->formatDateTime($ret->getData()['created_utc'], 'short', 'medium'),
            $ret->getData()['created'],
            'The formatted created timestamp has not the correct format'
            );
        $this->assertSame(
            $this->dateTimeFormatter->formatDateTime($ret->getData()['last_modified_utc'], 'short', 'medium'),
            $ret->getData()['last_modified'],
            'The formatted last modified timestamp has not the correct format'
            );
        $this->assertTrue(abs($now->getTimestamp() - $ret->getData()['created_utc']) < 10, 'Alias has not the expected created utc timestamp');
        $this->assertTrue(abs($now->getTimestamp() - $ret->getData()['last_modified_utc']) < 10, 'Alias has not the expected last modified utc timestamp');
    }

    public function testRead() {
        $ret = $this->controller->read($this->aliases[0]->getId());

        $this->assertTrue($ret instanceof JSONResponse, 'Result should be a JSON response.');
        $this->assertSame(Http::STATUS_OK, $ret->getStatus(), 'HTTP status should be OK.');
        $this->assertSame($this->userId, $ret->getData()['user_id'], 'Alias has not the expected user id');
        $this->assertSame($this->aliases[0]->getAliasId(), $ret->getData()['alias_id'], 'Alias has not the expected alias id');
        $this->assertSame($this->aliases[0]->getAliasName(), $ret->getData()['alias_name'], 'Alias has not the expected alias name');
        $this->assertSame($this->aliases[0]->getToMail(), $ret->getData()['to_mail'], 'Alias has not the expected to mail');
        $this->assertSame($this->aliases[0]->getComment(), $ret->getData()['comment'], 'Alias has not the expected comment');
        $this->assertSame($this->aliases[0]->getEnabled(), $ret->getData()['enabled'], 'Alias has not the expected enabled state');
        $this->assertSame(
            $this->dateTimeFormatter->formatDateTime($this->aliases[0]->getCreated(), 'short', 'medium'),
            $ret->getData()['created'],
            'Alias has not the expected created timestamp'
        );
        $this->assertSame(
            $this->dateTimeFormatter->formatDateTime($this->aliases[0]->getLastModified(), 'short', 'medium'),
            $ret->getData()['last_modified'],
            'Alias has not the expected last modified timestamp'
        );
        $this->assertSame($this->aliases[0]->getCreated(), $ret->getData()['created_utc'],'Alias has not the expected created utc timestamp');
        $this->assertSame($this->aliases[0]->getLastModified(), $ret->getData()['last_modified_utc'], 'Alias has not the expected last modified utc timestamp');
    }

    public function testReadNotFound() {
        $ret = $this->controller->read($this->aliases[3]->getId());

        $this->assertTrue($ret instanceof JSONResponse, 'Result should be a JSON response.');
        $this->assertSame(Http::STATUS_NOT_FOUND, $ret->getStatus(), 'HTTP status should be NOT FOUND.');
        $this->assertSame("string", gettype($ret->getData()['message']), 'Data should hold a status message.');
    }

    public function testUpdate() {
        $newToMail = 'john@abc.com';
        $newComment = 'New mail address';
        $newEnabled = false;
        
        $ret = $this->controller->update($this->aliases[0]->getId(), $newToMail, $newComment, $newEnabled);
        $now = new \DateTime('now');
        
        $this->assertTrue($ret instanceof JSONResponse, 'Result should be a JSON response.');
        $this->assertSame(Http::STATUS_OK, $ret->getStatus(), 'HTTP status should be OK.');
        $this->assertSame($this->userId, $ret->getData()['user_id'], 'Alias has not the expected user id');
        $this->assertSame($this->aliases[0]->getAliasId(), $ret->getData()['alias_id'], 'Alias has not the expected alias id');
        $this->assertSame($this->aliases[0]->getAliasName(), $ret->getData()['alias_name'], 'Alias has not the expected alias name');
        $this->assertSame($newToMail, $ret->getData()['to_mail'], 'Alias has not the expected to mail');
        $this->assertSame($newComment, $ret->getData()['comment'], 'Alias has not the expected comment');
        $this->assertSame($newEnabled, $ret->getData()['enabled'], 'Alias has not the expected enabled state');
        $this->assertSame(
            $this->dateTimeFormatter->formatDateTime($ret->getData()['created_utc'], 'short', 'medium'),
            $ret->getData()['created'],
            'The formatted created timestamp has not the correct format'
            );
        $this->assertSame(
            $this->dateTimeFormatter->formatDateTime($ret->getData()['last_modified_utc'], 'short', 'medium'),
            $ret->getData()['last_modified'],
            'The formatted last modified timestamp has not the correct format'
            );
        $this->assertSame($this->aliases[0]->getCreated(), $ret->getData()['created_utc'], 'Alias has not the expected created utc timestamp');
        $this->assertTrue(abs($now->getTimestamp() - $ret->getData()['last_modified_utc']) < 10, 'Alias has not the expected last modified utc timestamp');
    }

    public function testUpdateNotFound() {
        $newToMail = 'john@abc.com';
        $newComment = 'New mail address';
        $newEnabled = false;

        $ret = $this->controller->update($this->aliases[3]->getId(), $newToMail, $newComment, $newEnabled);

        $this->assertTrue($ret instanceof JSONResponse, 'Result should be a JSON response.');
        $this->assertSame(Http::STATUS_NOT_FOUND, $ret->getStatus(), 'HTTP status should be NOT FOUND.');
        $this->assertSame("string", gettype($ret->getData()['message']), 'Data should hold a status message.');
    }
    
    public function testDelete() {
        $ret = $this->controller->delete($this->aliases[0]->getId());
        
        // Remove alias for clean tearDown
        $alias = array_shift($this->aliases);
        
        $this->assertTrue($ret instanceof JSONResponse, 'Result should be a JSON response.');
        $this->assertSame(Http::STATUS_OK, $ret->getStatus(), 'HTTP status should be OK.');
        $this->assertSame($this->userId, $ret->getData()['user_id'], 'Alias has not the expected user id');
        $this->assertSame($alias->getAliasId(), $ret->getData()['alias_id'], 'Alias has not the expected alias id');
        $this->assertSame($alias->getAliasName(), $ret->getData()['alias_name'], 'Alias has not the expected alias name');
        $this->assertSame($alias->getToMail(), $ret->getData()['to_mail'], 'Alias has not the expected to mail');
        $this->assertSame($alias->getComment(), $ret->getData()['comment'], 'Alias has not the expected comment');
        $this->assertSame($alias->getEnabled(), $ret->getData()['enabled'], 'Alias has not the expected enabled state');
        $this->assertSame(
            $this->dateTimeFormatter->formatDateTime($alias->getCreated(), 'short', 'medium'),
            $ret->getData()['created'],
            'Alias has not the expected created timestamp'
            );
        $this->assertSame(
            $this->dateTimeFormatter->formatDateTime($alias->getLastModified(), 'short', 'medium'),
            $ret->getData()['last_modified'],
            'Alias has not the expected last modified timestamp'
            );
        $this->assertSame($alias->getCreated(), $ret->getData()['created_utc'], 'Alias has not the expected created utc timestamp');
        $this->assertSame($alias->getLastModified(), $ret->getData()['last_modified_utc'], 'Alias has not the expected last modified utc timestamp');
    }

    public function testDeleteNotFound() {
        $ret = $this->controller->delete($this->aliases[3]->getId());

        $this->assertTrue($ret instanceof JSONResponse, 'Result should be a JSON response.');
        $this->assertSame(Http::STATUS_NOT_FOUND, $ret->getStatus(), 'HTTP status should be NOT FOUND.');
        $this->assertSame("string", gettype($ret->getData()['message']), 'Data should hold a status message.');
    }
    
}