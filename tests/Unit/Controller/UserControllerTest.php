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

use OCA\Postmag\AppInfo\Application;
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
        
        $this->controller = new UserController(Application::APP_ID, $this->request, $this->service, $this->userId);
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