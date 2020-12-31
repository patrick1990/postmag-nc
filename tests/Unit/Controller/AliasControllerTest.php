<?php
declare(strict_types=1);

namespace OCA\Postmag\Tests\Unit\Controller;

use PHPUnit\Framework\TestCase;
use OCP\IRequest;
use OCA\Postmag\Service\AliasService;
use OCA\Postmag\Controller\AliasController;
use OCP\AppFramework\Http\JSONResponse;
use OCA\Postmag\Service\Exceptions\NotFoundException;
use OCP\AppFramework\Http;

class AliasControllerTest extends TestCase {
    
    private $controller;
    private $request;
    private $service;
    private $userId = 'john';
    
    private $aliases;
    
    public function setUp(): void {
        $this->request = $this->createMock(IRequest::class);
        $this->service = $this->createMock(AliasService::class);
        
        $this->controller = new AliasController('postmag', $this->request, $this->service, $this->userId);
        
        $this->aliases = [[], []];
        
        $this->aliases[0]['id'] = 234;
        $this->aliases[0]['user_id'] = 'john';
        $this->aliases[0]['alias_id'] = '1a2b';
        $this->aliases[0]['alias_name'] = 'alias';
        $this->aliases[0]['to_mail'] = 'john@doe.com';
        $this->aliases[0]['comment'] = 'My Alias';
        $this->aliases[0]['enabled'] = true;
        $this->aliases[0]['created'] = '2020-01-01 12:34:56';
        $this->aliases[0]['last_modified'] = '2020-02-02 12:34:56';
        
        $this->aliases[1]['id'] = 236;
        $this->aliases[1]['user_id'] = 'jane';
        $this->aliases[1]['alias_id'] = '2b3c';
        $this->aliases[1]['alias_name'] = 'important';
        $this->aliases[1]['to_mail'] = 'jane@doe.com';
        $this->aliases[1]['comment'] = 'Very important';
        $this->aliases[1]['enabled'] = true;
        $this->aliases[1]['created'] = '2020-05-12 12:34:56';
        $this->aliases[1]['last_modified'] = '2020-08-10 12:34:56';
    }
    
    public function testIndex() {
        // Mocking
        $findAll = function($userId) {
            foreach ($this->aliases as $alias) {
                if ($alias['user_id'] === $userId) {
                    $ret[] = $alias;
                }
                return $ret;
            }
        };
        
        $this->service->expects($this->once())
            ->method('findAll')
            ->willReturnCallback($findAll);
        
        // Test method
        $ret = $this->controller->index();
            
        $this->assertTrue($ret instanceof JSONResponse, 'Result should be a JSON response.');
        $this->assertSame(Http::STATUS_OK, $ret->getStatus(), 'HTTP status should be OK.');
        $this->assertSame($findAll($this->userId), $ret->getData(), 'Did not return the expected aliases.');
    }
    
    public function testCreate() {
        // Mocking
        $this->service->expects($this->once())
            ->method('create')
            ->with($this->aliases[0]["alias_name"],
                   $this->aliases[0]["to_mail"],
                   $this->aliases[0]["comment"],
                   $this->userId)
            ->willReturn($this->aliases[0]);
        
        // Test method
        $ret = $this->controller->create(
            $this->aliases[0]["alias_name"],
            $this->aliases[0]["to_mail"],
            $this->aliases[0]["comment"]
        );
        
        $this->assertTrue($ret instanceof JSONResponse, 'Result should be a JSON response.');
        $this->assertSame(Http::STATUS_OK, $ret->getStatus(), 'HTTP status should be OK.');
        $this->assertSame($this->aliases[0], $ret->getData(), 'Did not return the created alias.');
    }
    
    public function testUpdate() {
        // Mocking
        $this->service->expects($this->once())
            ->method('update')
            ->with($this->aliases[0]["id"],
                   $this->aliases[0]["to_mail"],
                   $this->aliases[0]["comment"],
                   $this->aliases[0]["enabled"],
                   $this->userId)
            ->willReturn($this->aliases[0]);
            
        // Test method
        $ret = $this->controller->update(
            $this->aliases[0]["id"],
            $this->aliases[0]["to_mail"],
            $this->aliases[0]["comment"],
            $this->aliases[0]["enabled"]
        );
        
        $this->assertTrue($ret instanceof JSONResponse, 'Result should be a JSON response.');
        $this->assertSame(Http::STATUS_OK, $ret->getStatus(), 'HTTP status should be OK.');
        $this->assertSame($this->aliases[0], $ret->getData(), 'Did not return the updated alias.');
    }
    
    public function testUpdateNotFound() {
        // Mocking
        $exceptionMsg = 'Id not found.';
        $this->service->expects($this->once())
            ->method('update')
            ->with($this->aliases[0]["id"],
                   $this->aliases[0]["to_mail"],
                   $this->aliases[0]["comment"],
                   $this->aliases[0]["enabled"],
                   $this->userId)
            ->willThrowException(new NotFoundException($exceptionMsg));
            
        // Test method
        $ret = $this->controller->update(
            $this->aliases[0]["id"],
            $this->aliases[0]["to_mail"],
            $this->aliases[0]["comment"],
            $this->aliases[0]["enabled"]
        );
        
        $this->assertTrue($ret instanceof JSONResponse, 'Result should be a JSON response.');
        $this->assertSame(Http::STATUS_NOT_FOUND, $ret->getStatus(), 'HTTP status should be NOT_FOUND.');
        $this->assertSame(['message' => $exceptionMsg], $ret->getData(), 'Did not return the exception message.');
    }
    
}