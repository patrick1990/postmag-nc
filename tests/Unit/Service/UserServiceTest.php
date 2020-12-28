<?php
declare(strict_types=1);

namespace OCA\Postmag\Tests\Unit\Service;

use PHPUnit\Framework\TestCase;
use OCP\IConfig;
use OCA\Postmag\Db\UserMapper;
use OCA\Postmag\Service\ConfigService;
use OCA\Postmag\Service\UserService;
use OCA\Postmag\Db\User;
use OCP\AppFramework\Db\DoesNotExistException;

class UserServiceTest extends TestCase {
    
    private $service;
    private $config;
    private $mapper;
    private $confService;
    
    public function setUp(): void {
        $this->config = $this->createMock(IConfig::class);
        $this->mapper = $this->createMock(UserMapper::class);
        $this->confService = $this->createMock(ConfigService::class);
        
        $this->service = new UserService(
            $this->config,
            $this->mapper,
            $this->confService
        );
    }
    
    public function testGetUserEMail(): void {
        $userId = 'john';
        $email = 'john@doe.com';
        
        // Mocking
        $this->config->expects($this->once())
            ->method('getUserValue')
            ->with($userId, 'settings', 'email')
            ->willReturn($email);
        
        // Test method
        $ret = $this->service->getUserEMail($userId);
        
        $this->assertSame($email, $ret, 'Wrong mail address returned.');
    }
    
    public function testGetExistingUserAliasId(): void {
        $userId = 'john';
        $userAliasId = '1a2b';
        
        $user = new User();
        $user->setUserId($userId);
        $user->setUserAliasId($userAliasId);
        
        // Mocking
        $this->mapper->expects($this->once())
            ->method('findUser')
            ->with($userId)
            ->willReturn($user);
        
        // Test method
        $ret = $this->service->getUserAliasId($userId);
        
        $this->assertSame($userAliasId, $ret, 'Wrong user alias id returned');
    }
    
    public function testGetNewUserAliasId(): void {
        $userId = 'john';
        
        // Mocking
        $this->mapper->expects($this->once())
            ->method('findUser')
            ->with($userId)
            ->willThrowException(new DoesNotExistException('No record found.'));
            
        $this->confService->expects($this->any())
            ->method('getUserAliasIdLen')
            ->willReturn(ConfigService::DEF_USER_ALIAS_ID_LEN);
        
        $toggle = false;
        $this->mapper->expects($this->any())
            ->method('containsAliasId')
            ->withAnyParameters()
            ->willReturnCallback(function () use (&$toggle) {
                $toggle = ! $toggle;
                return $toggle;
            });
        
        // Test method
        $ret = $this->service->getUserAliasId($userId);
        
        $this->assertSame('string', gettype($ret), 'user alias is not of type string.');
        $this->assertSame(1, preg_match("/^[0-9a-f]*$/", $ret), 'user alias is not a hexadecimal string.');
        $this->assertSame(ConfigService::DEF_USER_ALIAS_ID_LEN, strlen($ret), 'user alias is of wrong length.');
    }
    
}