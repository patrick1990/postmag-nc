<?php
declare(strict_types=1);

namespace OCA\Postmag\Tests\Unit\Controller;

use PHPUnit\Framework\TestCase;
use OCP\IRequest;
use OCA\Postmag\Service\UserService;
use OCA\Postmag\Controller\UserController;
use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\Http;

class UserControllerTest extends TestCase {
    
    private $controller;
    private $request;
    private $service;
    private $userId = 'john';
    
    public function setUp(): void {
        $this->request = $this->createMock(IRequest::class);
        $this->service = $this->createMock(UserService::class);
        
        $this->controller = new UserController('postmag', $this->request, $this->service, $this->userId);
    }
    
    public function testGetInfoMailSet(): void {
        // Mocking
        $this->service->expects($this->once())
            ->method('getUserEMail')
            ->with($this->userId)
            ->willReturn('john@doe.com');
        
        $this->service->expects($this->once())
            ->method('find')
            ->with($this->userId)
            ->willReturn([
                'id' => 123,
                'user_id' => $this->userId,
                'user_alias_id' => '1a2b'
            ]);
        
        // Test method
        $ret = $this->controller->getInfo();
        
        $this->assertTrue($ret instanceof JSONResponse, 'Result should be a JSON response.');
        $this->assertSame(Http::STATUS_OK, $ret->getStatus(), 'HTTP status should be OK.');
        $this->assertSame('john@doe.com', $ret->getData()['email'], 'Returned an unexpected mail address.');
        $this->assertSame('true', $ret->getData()['email_set'], 'Set flag should be true.');
        $this->assertSame($this->userId, $ret->getData()['user_id'], 'Returned an unexpected user id.');
        $this->assertSame('1a2b', $ret->getData()['user_alias_id'], 'Returned an unexpected user alias id.');
    }
    
    public function testGetInfoMailUnset(): void {
        // Mocking
        $this->service->expects($this->once())
            ->method('getUserEMail')
            ->with($this->userId)
            ->willReturn('');
        
            $this->service->expects($this->once())
                ->method('find')
                ->with($this->userId)
                ->willReturn([
                    'id' => 123,
                    'user_id' => $this->userId,
                    'user_alias_id' => '1a2b'
                ]);
        
        // Test method
        $ret = $this->controller->getInfo();
        
        $this->assertTrue($ret instanceof JSONResponse, 'Result should be a JSON response.');
        $this->assertSame(Http::STATUS_OK, $ret->getStatus(), 'HTTP status should be OK.');
        $this->assertSame('', $ret->getData()['email'], 'Returned an unexpected mail address.');
        $this->assertSame('false', $ret->getData()['email_set'], 'Set flag should be false.');
        $this->assertSame($this->userId, $ret->getData()['user_id'], 'Returned an unexpected user id.');
        $this->assertSame('1a2b', $ret->getData()['user_alias_id'], 'Returned an unexpected user alias id.');
    }
    
}