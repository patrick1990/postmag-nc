<?php
declare(strict_types=1);

namespace OCA\Postmag\Tests\Integration\Db;

use Test\TestCase;
use OCP\AppFramework\App;
use OCA\Postmag\Db\User;
use OCA\Postmag\Share\Random;
use OCA\Postmag\Service\ConfigService;

/**
 * @group DB
 */
class UserMapperTest extends TestCase {
    
    private $mapper;
    private $userId = 'john';
    
    private $user;
    
    public function setUp(): void {
        parent::setUp();
        $app = new App('postmag');
        $container = $app->getContainer();
        
        // replace user id
        $container->registerService('UserId', function ($c) {
            return $this->userId;
        });
        
        $this->mapper = $container->query('OCA\Postmag\Db\UserMapper');
        
        $this->user = new User();
        $this->user->setUserId($this->userId);
        $this->user->setUserAliasId(Random::hexString(ConfigService::DEF_USER_ALIAS_ID_LEN));
    }
    
    public function testFindUser(): void {
        // Insert user
        $insertedUser = $this->mapper->insert($this->user);
        
        // Test method
        $ret = $this->mapper->findUser($this->userId);
        
        $this->assertTrue($ret instanceof User, 'Result should be a User entity.');
        $this->assertSame($insertedUser->getId(), $ret->getId(), 'Did not return the expected id.');
        $this->assertSame($this->user->getUserId(), $ret->getUserId(), 'Did not return the expected user id.');
        $this->assertSame($this->user->getUserAliasId(), $ret->getUserAliasId(), 'Did not return the expected user alias id.');
        
        // Clean up
        $this->mapper->delete($insertedUser);
    }
    
    public function testContainsAliasId(): void {
        // Insert user
        $insertedUser = $this->mapper->insert($this->user);
        
        // Test method
        $this->assertTrue(
            $this->mapper->containsAliasId($this->user->getUserAliasId()),
            'User alias id was allready present but not found.'
            );
        $this->assertFalse(
            $this->mapper->containsAliasId(strrev($this->user->getUserAliasId())),
            'User alias id was not present but found.'
            );
        
        // Clean up
        $this->mapper->delete($insertedUser);
    }
    
}