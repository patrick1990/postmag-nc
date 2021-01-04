<?php
declare(strict_types=1);

namespace OCA\Postmag\Tests\Unit\Controller;

use PHPUnit\Framework\TestCase;
use OCP\IRequest;
use OCA\Postmag\Controller\ConfigController;
use OCA\Postmag\Service\ConfigService;
use OCA\Postmag\Tests\Unit\Service\ConfigServiceTest;
use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\Http;
use OCA\Postmag\Service\Exceptions\ValueFormatException;
use OCA\Postmag\Service\Exceptions\ValueBoundException;

class ConfigControllerTest extends TestCase {
    
    private $controller;
    private $request;
    private $service;
    private $userId = 'john';
    
    public function setUp(): void {
        $this->request = $this->createMock(IRequest::class);
        $this->service = $this->createMock(ConfigService::class);
        
        $this->controller = new ConfigController('postmag', $this->request, $this->service, $this->userId);
    }
    
    public function testGetConf(): void {
        // Mocking
        $this->service->expects($this->once())
            ->method('getConf')
            ->willReturn(ConfigServiceTest::CONF_DEFAULTS);
        
        // Test method
        $ret = $this->controller->getConf();
        
        $this->assertTrue($ret instanceof JSONResponse, 'Result should be a JSON response.');
        $this->assertSame(Http::STATUS_OK, $ret->getStatus(), 'HTTP status should be OK.');
        $this->assertSame(ConfigServiceTest::CONF_DEFAULTS, $ret->getData(), 'Did not return the expected config array');
    }
    
    public function testSetConf(): void {
        $newDomain = 'mydomain.org';
        $newUserAliasIdLen = 6;
        $newAliasIdLen = 5;
        
        // Mocking
        $this->service->expects($this->once())
            ->method('setTargetDomain')
            ->with($newDomain);
        
        $this->service->expects($this->once())
            ->method('setUserAliasIdLen')
            ->with($newUserAliasIdLen);
        
        $this->service->expects($this->once())
            ->method('setAliasIdLen')
            ->with($newAliasIdLen);
        
            
        $getConf = function() use ($newDomain, $newUserAliasIdLen, $newAliasIdLen) {
            $ret = ConfigServiceTest::CONF_DEFAULTS;
            $ret['domain'] = $newDomain;
            $ret['userAliasIdLen'] = $newUserAliasIdLen;
            $ret['aliasIdLen'] = $newAliasIdLen;
            
            return $ret;
        };
        $this->service->expects($this->once())
            ->method('getConf')
            ->willReturnCallback($getConf);
        
        // Test method
        $ret = $this->controller->setConf($newDomain, $newUserAliasIdLen, $newAliasIdLen);
        
        $this->assertTrue($ret instanceof JSONResponse, 'Result should be a JSON response.');
        $this->assertSame(Http::STATUS_OK, $ret->getStatus(), 'HTTP status should be OK.');
        $this->assertSame($getConf(), $ret->getData(), 'Did not return the expected config array');
    }
    
    public function testSetConfDomainException(): void {
        $newDomain = '.org';
        $newUserAliasIdLen = 6;
        $newAliasIdLen = 5;
        $exceptionMsg = 'Domain not valid.';
        
        // Mocking
        $this->service->expects($this->any())
            ->method('setTargetDomain')
            ->with($newDomain)
            ->willThrowException(new ValueFormatException($exceptionMsg));
        
        $this->service->expects($this->any())
            ->method('setUserAliasIdLen')
            ->with($newUserAliasIdLen);
        
        $this->service->expects($this->any())
            ->method('setAliasIdLen')
            ->with($newAliasIdLen);
        
        // Test method
        $ret = $this->controller->setConf($newDomain, $newUserAliasIdLen, $newAliasIdLen);
        
        $this->assertTrue($ret instanceof JSONResponse, 'Result should be a JSON response.');
        $this->assertSame(Http::STATUS_BAD_REQUEST, $ret->getStatus(), 'HTTP status should be BAD_REQUEST.');
        $this->assertSame(['message' => $exceptionMsg], $ret->getData(), 'Did not return the exception message.');
    }
    
    public function testSetConfUserAliasIdLenException(): void {
        $newDomain = 'mydomain.org';
        $newUserAliasIdLen = 20;
        $newAliasIdLen = 5;
        $exceptionMsg = 'Length not valid.';
        
        // Mocking
        $this->service->expects($this->any())
            ->method('setTargetDomain')
            ->with($newDomain);
        
        $this->service->expects($this->any())
            ->method('setUserAliasIdLen')
            ->with($newUserAliasIdLen)
            ->willThrowException(new ValueBoundException($exceptionMsg));
        
        $this->service->expects($this->any())
            ->method('setAliasIdLen')
            ->with($newAliasIdLen);
        
        // Test method
        $ret = $this->controller->setConf($newDomain, $newUserAliasIdLen, $newAliasIdLen);
        
        $this->assertTrue($ret instanceof JSONResponse, 'Result should be a JSON response.');
        $this->assertSame(Http::STATUS_BAD_REQUEST, $ret->getStatus(), 'HTTP status should be BAD_REQUEST.');
        $this->assertSame(['message' => $exceptionMsg], $ret->getData(), 'Did not return the exception message.');
    }
    
    public function testSetConfAliasIdLenException(): void {
        $newDomain = 'mydomain.org';
        $newUserAliasIdLen = 6;
        $newAliasIdLen = 20;
        $exceptionMsg = 'Length not valid.';
        
        // Mocking
        $this->service->expects($this->any())
            ->method('setTargetDomain')
            ->with($newDomain);
        
        $this->service->expects($this->any())
            ->method('setUserAliasIdLen')
            ->with($newUserAliasIdLen);
        
        $this->service->expects($this->any())
            ->method('setAliasIdLen')
            ->with($newAliasIdLen)
            ->willThrowException(new ValueBoundException($exceptionMsg));
        
        // Test method
        $ret = $this->controller->setConf($newDomain, $newUserAliasIdLen, $newAliasIdLen);
        
        $this->assertTrue($ret instanceof JSONResponse, 'Result should be a JSON response.');
        $this->assertSame(Http::STATUS_BAD_REQUEST, $ret->getStatus(), 'HTTP status should be BAD_REQUEST.');
        $this->assertSame(['message' => $exceptionMsg], $ret->getData(), 'Did not return the exception message.');
    }
    
}