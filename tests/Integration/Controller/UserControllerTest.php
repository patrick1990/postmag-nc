<?php
declare(strict_types=1);

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
        $this->assertSame('false', $ret->getData()['emailSet'], 'Did not return the expected email set flag');
        $this->assertSame($this->user->getUserAliasId(), $ret->getData()['userAliasId'], 'Did not return the expected user alias id');
    }
    
    public function testNewUserInfo(): void {
        // delete user john
        $this->mapper->delete($this->user);
        
        $ret = $this->controller->getInfo();
        
        $this->assertTrue($ret instanceof JSONResponse, 'Result should be a JSON response.');
        $this->assertSame(Http::STATUS_OK, $ret->getStatus(), 'HTTP status should be OK.');
        $this->assertSame('', $ret->getData()['email'], 'Did not return the expected email address');
        $this->assertSame('false', $ret->getData()['emailSet'], 'Did not return the expected email set flag');
        $this->assertSame(1, preg_match("/^[0-9a-f]*$/", $ret->getData()['userAliasId']), 'user alias is not a hexadecimal string.');
        $this->assertSame(ConfigService::DEF_USER_ALIAS_ID_LEN, strlen($ret->getData()['userAliasId']), 'user alias is of wrong length.');
    }
    
}