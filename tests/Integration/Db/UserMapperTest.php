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
    
    private $testUser;
    private $insertedUser;
    
    public function setUp(): void {
        parent::setUp();
        $app = new App('postmag');
        $container = $app->getContainer();
        
        // replace user id
        $container->registerService('UserId', function ($c) {
            return $this->userId;
        });
        
        $this->mapper = $container->query('OCA\Postmag\Db\UserMapper');
        
        // Create test user
        $this->testUser = new User();
        $this->testUser->setUserId($this->userId);
        $this->testUser->setUserAliasId(Random::hexString(ConfigService::DEF_USER_ALIAS_ID_LEN));
        
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