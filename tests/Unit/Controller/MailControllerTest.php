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

namespace OCA\Postmag\Tests\Unit\Controller;

use OCA\Postmag\Controller\MailController;
use OCA\Postmag\Service\Exceptions\MailException;
use OCA\Postmag\Service\Exceptions\MailRecipientException;
use OCA\Postmag\Service\Exceptions\NotFoundException;
use OCA\Postmag\Service\MailService;
use PHPUnit\Framework\TestCase;
use OCP\IRequest;
use OCA\Postmag\Controller\UserController;
use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\Http;

class MailControllerTest extends TestCase {
    
    private $controller;
    private $request;
    private $service;
    private $userId = 'john';
    
    public function setUp(): void {
        $this->request = $this->createMock(IRequest::class);
        $this->service = $this->createMock(MailService::class);
        
        $this->controller = new MailController('postmag', $this->request, $this->service, $this->userId);
    }
    
    public function testSendTest(): void {
        // Mocking
        $id = 123;
        $toMail = ['recipient' => 'abc@domain.com'];

        $this->service->expects($this->once())
            ->method("sendTest")
            ->with($id, $this->userId)
            ->willReturn($toMail);

        // Test method
        $ret = $this->controller->sendTest($id);

        $this->assertTrue($ret instanceof JSONResponse, 'Result should be a JSON response.');
        $this->assertSame(Http::STATUS_OK, $ret->getStatus(), 'HTTP status should be OK.');
        $this->assertSame($toMail, $ret->getData(), 'Did not return the expected recipient.');
    }

    public function testSendTestNotFound(): void {
        // Mocking
        $id = 123;
        $exceptionMsg = "Not found.";

        $this->service->expects($this->once())
            ->method("sendTest")
            ->with($id, $this->userId)
            ->willThrowException(new NotFoundException($exceptionMsg));

        // Test method
        $ret = $this->controller->sendTest($id);

        $this->assertTrue($ret instanceof JSONResponse, 'Result should be a JSON response.');
        $this->assertSame(Http::STATUS_NOT_FOUND, $ret->getStatus(), 'HTTP status should be NOT_FOUND.');
        $this->assertSame(["message" => $exceptionMsg], $ret->getData(), 'Did not return the exception message.');
    }

    public function testSendTestRecipientError(): void {
        // Mocking
        $id = 123;
        $exceptionMsg = "Recipient error.";

        $this->service->expects($this->once())
            ->method("sendTest")
            ->with($id, $this->userId)
            ->willThrowException(new MailRecipientException($exceptionMsg));

        // Test method
        $ret = $this->controller->sendTest($id);

        $this->assertTrue($ret instanceof JSONResponse, 'Result should be a JSON response.');
        $this->assertSame(Http::STATUS_BAD_GATEWAY, $ret->getStatus(), 'HTTP status should be BAD_GATEWAY.');
        $this->assertSame(["message" => $exceptionMsg], $ret->getData(), 'Did not return the exception message.');
    }

    public function testSendTestMailError(): void {
        // Mocking
        $id = 123;
        $exceptionMsg = "Recipient error.";

        $this->service->expects($this->once())
            ->method("sendTest")
            ->with($id, $this->userId)
            ->willThrowException(new MailException($exceptionMsg));

        // Test method
        $ret = $this->controller->sendTest($id);

        $this->assertTrue($ret instanceof JSONResponse, 'Result should be a JSON response.');
        $this->assertSame(Http::STATUS_BAD_GATEWAY, $ret->getStatus(), 'HTTP status should be BAD_GATEWAY.');
        $this->assertSame(["message" => $exceptionMsg], $ret->getData(), 'Did not return the exception message.');
    }
    
}