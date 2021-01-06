<?php
declare(strict_types=1);

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
    private $userService;
    private $aliasMapper;
    private $userMapper;
    
    private $users;
    private $aliases;
    
    public function setUp(): void {
        parent::setUp();
        $app = new App('postmag');
        $container = $app->getContainer();
        
        $this->service = $container->get('OCA\Postmag\Service\CommandService');
        $this->userService = $container->get('OCA\Postmag\Service\UserService');
        $this->aliasMapper = $container->get('OCA\Postmag\Db\AliasMapper');
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
    
    public function tearDown(): void {
        // Clean up the database
        foreach ($this->aliases as $alias) {
            $this->aliasMapper->delete($alias);
        }
        foreach ($this->users as $user) {
            $this->userMapper->delete($user);
        }
        
        parent::tearDown();
    }
    
    public function testGetLastModified(): void {
        $this->assertSame(
            strval(end($this->aliases)->getLastModified()),
            $this->service->getLastModified(),
            'Expected the last added alias to be the last modified entry'
            );
        
        // Formatted
        $this->assertSame(
            (new \DateTime())->setTimestamp(end($this->aliases)->getLastModified())->format('Y-m-d_H:i:s'),
            $this->service->getLastModified(true),
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
        $created = 0;
        $lastModified = 0;
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
                    $created = intval(substr($line, 11, $sep-11));
                    $lastModified = intval(substr($line, $sep+12, strlen($line)-$sep-12));
                    
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
                    if (($alias->getCreated() === $created) &&
                        ($alias->getLastModified() === $lastModified) &&
                        ($alias->getEnabled() === $enabled) &&
                        ($alias->getAliasName() === $aliasName) &&
                        ($alias->getAliasId() === $aliasId) &&
                        ($this->userService->getUserAliasId($alias->getUserId()) === $userAliasId) &&
                        ($alias->getToMail() === $toMail))
                    {
                        $marker[$key] = true;
                    }
                }
                
                // Reset everything
                $created = 0;
                $lastModified = 0;
                $enabled = false;
                $aliasName = '';
                $aliasId = '';
                $userAliasId = '';
                $toMail = '';
            }
        }
        
        // Check marker array
        foreach ($marker as $key => $value) {
            $this->assertTrue($value, $this->aliases[$key]->getAliasName().' was not found in the alias file');
        }
    }
    
}