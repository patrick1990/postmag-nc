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

namespace OCA\Postmag\Tests\Integration\Service;

use Test\TestCase;
use OCP\AppFramework\App;
use OCA\Postmag\Db\Alias;
use OCA\Postmag\Share\Random;
use OCA\Postmag\Service\ConfigService;
use OCA\Postmag\Db\User;

/**
 * @group DB
 */
class CommandServiceTest extends TestCase {
    
    private $service;
    private $formatter;
    private $aliasService;
    private $userService;
    private $userMapper;
    
    private $users;
    private $aliases;
    
    public function setUp(): void {
        parent::setUp();
        $app = new App('postmag');
        $container = $app->getContainer();
        
        $this->service = $container->get('OCA\Postmag\Service\CommandService');
        $this->formatter = $container->get('OCP\IDateTimeFormatter');
        $this->aliasService = $container->get('OCA\Postmag\Service\AliasService');
        $this->userService = $container->get('OCA\Postmag\Service\UserService');
        $this->userMapper = $container->get('OCA\Postmag\Db\UserMapper');
        
        // Fill something in the database
        $this->users = [];
        $this->users[] = $this->createUser('john');
        $this->users[] = $this->createUser('jane');
        
        $this->aliases = [];
        $this->aliases[] = $this->createAlias('john', 'alias1', 'john@doe.com', 'My first alias', true);
        $this->aliases[] = $this->createAlias('jane', 'alias2', 'jane@doe.com', 'My first alias', false);
        
        // Wait a second to have a clear last modified entry
        sleep(1);
        $this->aliases[] = $this->createAlias('jane', 'alias3', 'jane@domain.org', 'My second alias', true);
    }
    
    private function createUser(string $userId): User {
        $user = new User();
        $user->setUserId($userId);
        $user->setUserAliasId(Random::hexString(ConfigService::DEF_USER_ALIAS_ID_LEN));
        
        return $this->userMapper->insert($user);
    }
    
    private function createAlias(string $userId, string $aliasName, string $toMail, string $comment, bool $enabled): array {
        $alias = $this->aliasService->create($aliasName, $toMail, $comment, $userId);
        
        return $this->aliasService->update($alias['id'], $toMail, $comment, $enabled, $userId);;
    }
    
    public function tearDown(): void {
        // Clean up the database
        foreach ($this->aliases as $alias) {
            $this->aliasService->delete($alias['id'], $alias['user_id']);
        }
        foreach ($this->users as $user) {
            $this->userMapper->delete($user);
        }
        
        parent::tearDown();
    }
    
    public function testGetLastModified(): void {
        $this->assertSame(
            end($this->aliases)['last_modified'],
            $this->formatter->formatDateTime($this->service->getLastModified(), 'short', 'medium'),
            'Expected the last added alias to be the last modified entry'
            );
        
        // Formatted
        $this->assertSame(
            end($this->aliases)['last_modified'],
            $this->formatter->formatDateTime(
                strval(\DateTime::createFromFormat(
                    'Y-m-d_H:i:s',
                    $this->service->getLastModified(true))->getTimestamp()
                ),
                'short',
                'medium'
            ),
            'Expected the last added alias to be the last modified entry (formatted reply)'
            );
    }
    
    public function testFormatPostfixAliasFile(): void {
        $ret = $this->service->formatPostfixAliasFile();
        
        // Define marker array; index will be checked if the alias was found in the genereated alias file
        $marker = array_fill(0, count($this->aliases), false);
        
        // Flag that indicates if we are expecting a timestamp line (first line) or an alias line (second line)
        $expectAlias = false;
        
        // Value caches
        $created = '';
        $lastModified = '';
        $enabled = false;
        $aliasName = '';
        $aliasId = '';
        $userAliasId = '';
        $toMail = '';
        
        foreach ($ret as $line) {
            if (!$expectAlias) {
                // Found timestamp line
                if (substr($line, 0, 10) === '# Created:') {
                    $sep = strpos($line, ',');
                    $created = $this->formatter->formatDateTime(substr($line, 11, $sep-11), 'short', 'medium');
                    $lastModified = $this->formatter->formatDateTime(substr($line, $sep+12, strlen($line)-$sep-12), 'short', 'medium');
                    
                    $expectAlias = true;
                    continue;
                }
            }
            
            if ($expectAlias) {
                // alias line has to be next line!
                $expectAlias = false;
                
                // parse alias line
                $enabled = ($line[0] !== '#');
                if (!$enabled)
                    $line = substr($line, 2);
                
                $aliasName = substr($line, 0, strpos($line, '.'));
                $line = substr($line, strpos($line, '.')+1);
                
                $aliasId = substr($line, 0, strpos($line, '.'));
                $line = substr($line, strpos($line, '.')+1);
                
                $userAliasId = substr($line, 0, strpos($line, ':'));
                $line = substr($line, strpos($line, ':')+2);
                
                $toMail = $line;
                
                // Mark it, if this is one of the test cases
                foreach ($this->aliases as $key => $alias) {
                    if (($alias['created'] === $created) &&
                        ($alias['last_modified'] === $lastModified) &&
                        ($alias['enabled'] === $enabled) &&
                        ($alias['alias_name'] === $aliasName) &&
                        ($alias['alias_id'] === $aliasId) &&
                        ($this->userService->getUserAliasId($alias['user_id']) === $userAliasId) &&
                        ($alias['to_mail'] === $toMail))
                    {
                        $marker[$key] = true;
                    }
                }
                
                // Reset everything
                $created = '';
                $lastModified = '';
                $enabled = false;
                $aliasName = '';
                $aliasId = '';
                $userAliasId = '';
                $toMail = '';
            }
        }
        
        // Check marker array
        foreach ($marker as $key => $value) {
            $this->assertTrue($value, $this->aliases[$key]['alias_name'].' was not found in the alias file');
        }
    }
    
}