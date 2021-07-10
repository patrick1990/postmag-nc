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

namespace OCA\Postmag\Tests\Unit\Service;

use OC\Mail\EMailTemplate;
use OCA\Postmag\Service\AliasService;
use OCA\Postmag\Service\ConfigService;
use OCA\Postmag\Service\Exceptions\MailException;
use OCA\Postmag\Service\Exceptions\MailRecipientException;
use OCA\Postmag\Service\Exceptions\NotFoundException;
use OCA\Postmag\Service\Exceptions\UnexpectedDatabaseResponseException;
use OCA\Postmag\Service\MailService;
use OCP\IL10N;
use OCP\Mail\IEMailTemplate;
use OCP\Mail\IMailer;
use OCP\Mail\IMessage;
use PHPUnit\Framework\TestCase;
use OCA\Postmag\Service\UserService;
use OCA\Postmag\Db\Alias;

class MailServiceTest extends TestCase {
    
    private $service;
    private $l;
    private $mailer;
    private $aliasService;
    private $userService;
    private $configService;
    
    private $aliases;
    
    public function setUp(): void {
        $this->l = $this->createMock(IL10N::class);
        $this->mailer = $this->createMock(IMailer::class);
        $this->aliasService = $this->createMock(AliasService::class);
        $this->userService = $this->createMock(UserService::class);
        $this->configService = $this->createMock(ConfigService::class);
        
        $this->service = new MailService(
            $this->l,
            $this->mailer,
            $this->aliasService,
            $this->userService,
            $this->configService
        );
        
        $this->aliases = [[]];

        $this->aliases[0]['id'] = 234;
        $this->aliases[0]['user_id'] = 'john';
        $this->aliases[0]['alias_id'] = '1a2b';
        $this->aliases[0]['alias_name'] = 'alias';
        $this->aliases[0]['to_mail'] = 'john@doe.com';
        $this->aliases[0]['comment'] = 'My Alias';
        $this->aliases[0]['enabled'] = true;
        $this->aliases[0]['created'] = '2020-01-01 12:34:56';
        $this->aliases[0]['last_modified'] = '2020-02-02 12:34:56';
    }

    public function testSendTest(): void {
        $userAliasId = "abcd";
        $targetDomain = "domain.com";

        //Mocking
        $this->aliasService->expects($this->once())
            ->method('find')
            ->with($this->aliases[0]['id'], $this->aliases[0]['user_id'])
            ->willReturn($this->aliases[0]);

        $this->userService->expects($this->once())
            ->method('getUserAliasId')
            ->with($this->aliases[0]['user_id'])
            ->willReturn($userAliasId);

        $this->configService->expects($this->once())
            ->method('getTargetDomain')
            ->willReturn($targetDomain);

        $this->l->expects($this->any())
            ->method('t')
            ->willReturnCallback(function($msg) {
                return $msg;
            });

        $template = $this->createMock(IEMailTemplate::class);
        $template->expects($this->any())
            ->method($this->anything())
            ->willReturnSelf();
        $this->mailer->expects($this->once())
            ->method('createEMailTemplate')
            ->willReturn($template);

        $message = $this->createMock(IMessage::class);
        $message->expects($this->any())
            ->method($this->anything())
            ->willReturnSelf();
        $this->mailer->expects($this->once())
            ->method('createMessage')
            ->willReturn($message);

        $this->mailer->expects($this->once())
            ->method('send')
            ->willReturn([]);

        // Test method
        $ret = $this->service->sendTest($this->aliases[0]['id'], $this->aliases[0]['user_id']);
        $toMail = $this->aliases[0]['alias_name']
                  ."."
                  .$this->aliases[0]['alias_id']
                  ."."
                  .$userAliasId
                  ."@"
                  .$targetDomain;

        $this->assertSame($toMail, $ret["recipient"], "not the expected recipient.");
    }

    public function testSendTestNotFound(): void {
        //Mocking
        $this->aliasService->expects($this->once())
            ->method('find')
            ->with($this->aliases[0]['id'], $this->aliases[0]['user_id'])
            ->willThrowException(new NotFoundException());

        // Test method
        $this->expectException(UnexpectedDatabaseResponseException::class);
        $this->service->sendTest($this->aliases[0]['id'], $this->aliases[0]['user_id']);
    }

    public function testSendTestRecipientError(): void {
        $userAliasId = "abcd";
        $targetDomain = "domain.com";

        //Mocking
        $this->aliasService->expects($this->once())
            ->method('find')
            ->with($this->aliases[0]['id'], $this->aliases[0]['user_id'])
            ->willReturn($this->aliases[0]);

        $this->userService->expects($this->once())
            ->method('getUserAliasId')
            ->with($this->aliases[0]['user_id'])
            ->willReturn($userAliasId);

        $this->configService->expects($this->once())
            ->method('getTargetDomain')
            ->willReturn($targetDomain);

        $this->l->expects($this->any())
            ->method('t')
            ->willReturnCallback(function($msg) {
                return $msg;
            });

        $template = $this->createMock(IEMailTemplate::class);
        $template->expects($this->any())
            ->method($this->anything())
            ->willReturnSelf();
        $this->mailer->expects($this->once())
            ->method('createEMailTemplate')
            ->willReturn($template);

        $message = $this->createMock(IMessage::class);
        $message->expects($this->any())
            ->method($this->anything())
            ->willReturnSelf();
        $this->mailer->expects($this->once())
            ->method('createMessage')
            ->willReturn($message);

        $this->mailer->expects($this->once())
            ->method('send')
            ->willReturn(['abc@domain.com']);

        // Test method
        $this->expectException(MailRecipientException::class);
        $this->service->sendTest($this->aliases[0]['id'], $this->aliases[0]['user_id']);
    }

    public function testSendTestServerError(): void {
        $userAliasId = "abcd";
        $targetDomain = "domain.com";

        //Mocking
        $this->aliasService->expects($this->once())
            ->method('find')
            ->with($this->aliases[0]['id'], $this->aliases[0]['user_id'])
            ->willReturn($this->aliases[0]);

        $this->userService->expects($this->once())
            ->method('getUserAliasId')
            ->with($this->aliases[0]['user_id'])
            ->willReturn($userAliasId);

        $this->configService->expects($this->once())
            ->method('getTargetDomain')
            ->willReturn($targetDomain);

        $this->l->expects($this->any())
            ->method('t')
            ->willReturnCallback(function($msg) {
                return $msg;
            });

        $template = $this->createMock(IEMailTemplate::class);
        $template->expects($this->any())
            ->method($this->anything())
            ->willReturnSelf();
        $this->mailer->expects($this->once())
            ->method('createEMailTemplate')
            ->willReturn($template);

        $message = $this->createMock(IMessage::class);
        $message->expects($this->any())
            ->method($this->anything())
            ->willReturnSelf();
        $this->mailer->expects($this->once())
            ->method('createMessage')
            ->willReturn($message);

        $this->mailer->expects($this->once())
            ->method('send')
            ->willThrowException(new \Exception("Server error"));

        // Test method
        $this->expectException(MailException::class);
        $this->service->sendTest($this->aliases[0]['id'], $this->aliases[0]['user_id']);
    }
    
}