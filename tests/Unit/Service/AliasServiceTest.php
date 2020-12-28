<?php
declare(strict_types=1);

namespace OCA\Postmag\Tests\Unit\Service;

use PHPUnit\Framework\TestCase;
use OCP\IDateTimeFormatter;
use OCA\Postmag\Db\AliasMapper;
use OCA\Postmag\Service\ConfigService;
use OCA\Postmag\Service\AliasService;
use OCA\Postmag\Db\Alias;
use OCA\Postmag\Service\Exceptions\ValueFormatException;
use OCA\Postmag\Service\Exceptions\StringLengthException;
use OCP\AppFramework\Db\DoesNotExistException;

class AliasServiceTest extends TestCase {
    
    // Define test cases for input arguments
    private const ALIAS_NAME_TEST_CASES = [
        "allowed" => [
            "alias123",
            "hello",
            "b",
            "0"
        ],
        "notAllowed" => [
            "abc.def",
            ".",
            "ß",
            "allfälliges",
            "wayTooLongChosenAliasName"
        ]
    ];
    private const MAIL_TEST_CASES = [
        "allowed" => [
            "my@example.com",
            "john.doe@sub.domain.de",
            "hi@city.wien"
        ],
        "notAllowed" => [
            "a",
            "abc.def.com",
            ".@example.com",
            "hi@com",
            "my@",
            "abcdefghijklmnopqrstuvwxyzabcdefghijklmnabcdefghijklmnopqrstuvwxyzabcdefghijklmnabcdefghijklmnopqrstuvwxyzabcdefghijklmnabcdefghijklmnopqrstuvwxyzabcdefghijklmnabcdefghijklmnopqrstuvwxyzabcdefghijklmnabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqr@mydomain.com"
        ]
    ];
    private const COMMENT_TEST_CASES = [
        "allowed" => [
            "This is my comment",
            "Umlaute to go: ÄüÖß"
        ],
        "notAllowed" => [
            "abcdefghijklmnopqrstuvwxyzabcdefghijklmno"
        ]
    ];
    
    private $service;
    private $dateTimeFormatter;
    private $mapper;
    private $confService;
    
    private $aliases;
    
    public function setUp(): void {
        $this->dateTimeFormatter = $this->createMock(IDateTimeFormatter::class);
        $this->mapper = $this->createMock(AliasMapper::class);
        $this->confService = $this->createMock(ConfigService::class);
        
        $this->service = new AliasService(
            $this->dateTimeFormatter,
            $this->mapper,
            $this->confService
        );
        
        $this->aliases = [new Alias(), new Alias()];
        
        $this->aliases[0]->setId(234);
        $this->aliases[0]->setUserId('john');
        $this->aliases[0]->setAliasId('1a2b');
        $this->aliases[0]->setAliasName('alias');
        $this->aliases[0]->setToMail('john@doe.com');
        $this->aliases[0]->setComment('My Alias');
        $this->aliases[0]->setEnabled(true);
        $this->aliases[0]->setCreated(12345);
        $this->aliases[0]->setLastModified(23456);
        
        $this->aliases[1]->setId(236);
        $this->aliases[1]->setUserId('jane');
        $this->aliases[1]->setAliasId('2b3c');
        $this->aliases[1]->setAliasName('important');
        $this->aliases[1]->setToMail('jane@doe.com');
        $this->aliases[1]->setComment('Very important');
        $this->aliases[1]->setEnabled(true);
        $this->aliases[1]->setCreated(76543);
        $this->aliases[1]->setLastModified(87654);
        
        $this->dateTimeFormatter->expects($this->any())
            ->method('formatDateTime')
            ->with($this->anything(), 'short', 'medium')
            ->willReturnCallback(function($timestamp, $dateFormat, $timeFormat) {
                return $this->formatDTCallback($timestamp, $dateFormat, $timeFormat);
            });
            
    }
    
    public function formatDTCallback($timestamp, $dateFormat, $timeFormat) {
        if ($dateFormat === 'short' && $timeFormat === 'medium') {
            return 'unix-'.strval($timestamp);
        }
        return '';
    }
    
    public function testFindAll(): void {
        // Mocking
        $findAll = function($userId) {
            foreach ($this->aliases as $alias) {
                if ($alias->getUserId() === $userId) {
                    $ret[] = $alias;
                }
                return $ret;
            }
        };
        
        $this->mapper->expects($this->once())
            ->method('findAll')
            ->willReturnCallback($findAll);
        
        // Test method
        $ret = $this->service->findAll('john');
        
        $this->assertSame(1, count($ret), 'find all returns not the expected count of aliases');
        $this->assertSame($this->aliases[0]->getId(), $ret[0]['id'], 'not the expected id.');
        $this->assertSame($this->aliases[0]->getUserId(), $ret[0]['user_id'], 'not the expected user id.');
        $this->assertSame($this->aliases[0]->getAliasId(), $ret[0]['alias_id'], 'not the expected alias id.');
        $this->assertSame($this->aliases[0]->getAliasName(), $ret[0]['alias_name'], 'not the expected alias name.');
        $this->assertSame($this->aliases[0]->getToMail(), $ret[0]['to_mail'], 'not the expected to mail.');
        $this->assertSame($this->aliases[0]->getComment(), $ret[0]['comment'], 'not the expected comment.');
        $this->assertSame($this->aliases[0]->getEnabled(), $ret[0]['enabled'], 'not the expected enabled state.');
        $this->assertSame($this->formatDTCallback($this->aliases[0]->getCreated(), 'short', 'medium'), $ret[0]['created'], 'not the expected created timestamp.');
        $this->assertSame($this->formatDTCallback($this->aliases[0]->getLastModified(), 'short', 'medium'), $ret[0]['last_modified'], 'not the expected last modified timestamp.');
    }
    
    public function testCreate(): void {
        // Mocking
        $this->confService->expects($this->any())
            ->method('getAliasIdLen')
            ->willReturn(ConfigService::DEF_ALIAS_ID_LEN);
        
        $toggle = false;
        $this->mapper->expects($this->any())
            ->method('containsAliasId')
            ->withAnyParameters()
            ->willReturnCallback(function () use (&$toggle) {
                $toggle = ! $toggle;
                return $toggle;
            });
        
        $this->mapper->expects($this->once())
            ->method('insert')
            ->willReturnCallback(function($alias) {
                return $alias;
            });
            
        // Test method
        $ret = $this->service->create(
            $this->aliases[0]->getAliasName(),
            $this->aliases[0]->getToMail(),
            $this->aliases[0]->getComment(),
            $this->aliases[0]->getUserId()
            );
        
        // Check generated alias id
        $this->assertSame('string', gettype($ret['alias_id']), 'alias id is not of type string.');
        $this->assertSame(1, preg_match("/^[0-9a-f]*$/", $ret['alias_id']), 'alias id is not a hexadecimal string.');
        $this->assertSame(ConfigService::DEF_USER_ALIAS_ID_LEN, strlen($ret['alias_id']), 'alias id is of wrong length.');
        
        // Check timestamps
        // Unit tests are that fast, that this now timestamp is equal to the now timestamp in service->create. I'm sure...
        $now = $this->formatDTCallback((new \DateTime("now"))->getTimestamp(), 'short', 'medium');
        $this->assertSame($now, $ret['created'], 'created timestamp not set correctly.');
        $this->assertSame($now, $ret['last_modified'], 'last modified timestamp not set correctly.');
        
        // Check other data
        $this->assertSame($this->aliases[0]->getUserId(), $ret['user_id'], 'not the expected user id.');
        $this->assertSame($this->aliases[0]->getAliasName(), $ret['alias_name'], 'not the expected alias name.');
        $this->assertSame($this->aliases[0]->getToMail(), $ret['to_mail'], 'not the expected to mail.');
        $this->assertSame($this->aliases[0]->getComment(), $ret['comment'], 'not the expected comment.');
        $this->assertSame(true, $ret['enabled'], 'not the expected enabled state.');
    }
    
    public function testUpdate() {
        // Mocking
        $this->mapper->expects($this->once())
            ->method('find')
            ->with($this->aliases[0]->getId(), $this->aliases[0]->getUserId())
            ->willReturn($this->aliases[0]);
        
        $this->mapper->expects($this->once())
            ->method('update')
            ->willReturnCallback(function($alias) {
                return $alias;
            });
            
        // Test method
        $ret = $this->service->update(
            $this->aliases[0]->getId(),
            $this->aliases[1]->getToMail(),
            $this->aliases[1]->getComment(),
            false,
            $this->aliases[0]->getUserId()
            );
        
        // Check timestamps
        // Unit tests are that fast, that this now timestamp is equal to the now timestamp in service->create. I'm sure...
        $now = $this->formatDTCallback((new \DateTime("now"))->getTimestamp(), 'short', 'medium');
        $this->assertSame($this->formatDTCallback($this->aliases[0]->getCreated(), 'short', 'medium'), $ret['created'], 'created timestamp was changed.');
        $this->assertSame($now, $ret['last_modified'], 'last modified timestamp not set correctly.');
        
        // Check other data
        $this->assertSame($this->aliases[0]->getUserId(), $ret['user_id'], 'not the expected user id.');
        $this->assertSame($this->aliases[0]->getAliasName(), $ret['alias_name'], 'not the expected alias name.');
        $this->assertSame($this->aliases[1]->getToMail(), $ret['to_mail'], 'not the expected to mail.');
        $this->assertSame($this->aliases[1]->getComment(), $ret['comment'], 'not the expected comment.');
        $this->assertSame(false, $ret['enabled'], 'not the expected enabled state.');
    }
    
    public function testUpdateNotFound(): void {
        // Mocking
        $this->mapper->expects($this->once())
            ->method('find')
            ->with($this->aliases[0]->getId(), $this->aliases[0]->getUserId())
            ->willThrowException(new DoesNotExistException("Does not exists."));
        
        // Test method
        $caught = false;
        try {
            $this->service->update(
                $this->aliases[0]->getId(),
                $this->aliases[1]->getToMail(),
                $this->aliases[1]->getComment(),
                false,
                $this->aliases[0]->getUserId()
                );
        }
        catch (\OCA\Postmag\Service\Exceptions\NotFoundException $e) {
            $caught = true;
        }
        
        if (!$caught) {
            $this->assertTrue(false, "Not found exception of database was not handled.");
        }
    }
    
    public function testAliasNameAllowed(): void {
        foreach (self::ALIAS_NAME_TEST_CASES["allowed"] as $testcase) {
            // Reset test
            $this->setUp();
            
            // Mocking
            $this->confService->expects($this->any())
                ->method('getAliasIdLen')
                ->willReturn(ConfigService::DEF_ALIAS_ID_LEN);
                
            $this->mapper->expects($this->any())
                ->method('containsAliasId')
                ->withAnyParameters()
                ->willReturn(false);
            
            $this->mapper->expects($this->once())
                ->method('insert')
                ->willReturnCallback(function($alias) {
                    return $alias;
                });
            
            // Test method
            try {
                $this->service->create(
                    $testcase,
                    $this->aliases[0]->getToMail(),
                    $this->aliases[0]->getComment(),
                    $this->aliases[0]->getUserId()
                    );
            }
            catch (ValueFormatException $e) {
                $this->assertTrue(false, strval($testcase)." was not accepted as alias name.");
            }
            catch (StringLengthException $e) {
                $this->assertTrue(false, strval($testcase)." was a too long alias name.");
            }
        }
    }
    
    public function testAliasNameNotAllowed(): void {
        foreach (self::ALIAS_NAME_TEST_CASES["notAllowed"] as $testcase) {
            // Reset test
            $this->setUp();
            
            // Mocking
            $this->confService->expects($this->any())
                ->method('getAliasIdLen')
                ->willReturn(ConfigService::DEF_ALIAS_ID_LEN);
            
            $this->mapper->expects($this->any())
                ->method('containsAliasId')
                ->withAnyParameters()
                ->willReturn(false);
            
            $this->mapper->expects($this->any())
                ->method('insert')
                ->willReturnCallback(function($alias) {
                    return $alias;
                });
            
            // Test method
            $caught = false;
            try {
                $this->service->create(
                    $testcase,
                    $this->aliases[0]->getToMail(),
                    $this->aliases[0]->getComment(),
                    $this->aliases[0]->getUserId()
                    );
            }
            catch (ValueFormatException $e) {
                $caught = true;
            }
            catch (StringLengthException $e) {
                $caught = true;
            }
            
            if (!$caught) {
                $this->assertTrue(false, strval($testcase)." was accepted as alias name.");
            }
        }
        
        // Prevent test of beeing useless in case of all length are rejected.
        $this->assertTrue(true);
    }
    
    public function testMailAllowed(): void {
        foreach (self::MAIL_TEST_CASES["allowed"] as $testcase) {
            // Reset test
            $this->setUp();
            
            // Mocking
            $this->confService->expects($this->any())
                ->method('getAliasIdLen')
                ->willReturn(ConfigService::DEF_ALIAS_ID_LEN);
            
            $this->mapper->expects($this->any())
                ->method('containsAliasId')
                ->withAnyParameters()
                ->willReturn(false);
            
            $this->mapper->expects($this->once())
                ->method('insert')
                ->willReturnCallback(function($alias) {
                    return $alias;
                });
                
            // Test method
            try {
                $this->service->create(
                    $this->aliases[0]->getAliasName(),
                    $testcase,
                    $this->aliases[0]->getComment(),
                    $this->aliases[0]->getUserId()
                    );
            }
            catch (ValueFormatException $e) {
                $this->assertTrue(false, strval($testcase)." was not accepted as mail address.");
            }
            catch (StringLengthException $e) {
                $this->assertTrue(false, strval($testcase)." was a too long mail address.");
            }
        }
    }
    
    public function testMailNotAllowed(): void {
        foreach (self::MAIL_TEST_CASES["notAllowed"] as $testcase) {
            // Reset test
            $this->setUp();
            
            // Mocking
            $this->confService->expects($this->any())
                ->method('getAliasIdLen')
                ->willReturn(ConfigService::DEF_ALIAS_ID_LEN);
            
            $this->mapper->expects($this->any())
                ->method('containsAliasId')
                ->withAnyParameters()
                ->willReturn(false);
            
            $this->mapper->expects($this->any())
                ->method('insert')
                ->willReturnCallback(function($alias) {
                    return $alias;
                });
            
            // Test method
            $caught = false;
            try {
                $this->service->create(
                    $this->aliases[0]->getAliasName(),
                    $testcase,
                    $this->aliases[0]->getComment(),
                    $this->aliases[0]->getUserId()
                    );
            }
            catch (ValueFormatException $e) {
                $caught = true;
            }
            catch (StringLengthException $e) {
                $caught = true;
            }
            
            if (!$caught) {
                $this->assertTrue(false, strval($testcase)." was accepted as mail address.");
            }
        }
        
        // Prevent test of beeing useless in case of all length are rejected.
        $this->assertTrue(true);
    }
    
    public function testCommentAllowed(): void {
        foreach (self::COMMENT_TEST_CASES["allowed"] as $testcase) {
            // Reset test
            $this->setUp();
            
            // Mocking
            $this->confService->expects($this->any())
                ->method('getAliasIdLen')
                ->willReturn(ConfigService::DEF_ALIAS_ID_LEN);
            
            $this->mapper->expects($this->any())
                ->method('containsAliasId')
                ->withAnyParameters()
                ->willReturn(false);
            
            $this->mapper->expects($this->once())
                ->method('insert')
                ->willReturnCallback(function($alias) {
                    return $alias;
                });
                
            // Test method
            try {
                $this->service->create(
                    $this->aliases[0]->getAliasName(),
                    $this->aliases[0]->getToMail(),
                    $testcase,
                    $this->aliases[0]->getUserId()
                    );
            }
            catch (ValueFormatException $e) {
                $this->assertTrue(false, strval($testcase)." was not accepted as comment.");
            }
            catch (StringLengthException $e) {
                $this->assertTrue(false, strval($testcase)." was a too long comment.");
            }
        }
    }
    
    public function testCommentNotAllowed(): void {
        foreach (self::COMMENT_TEST_CASES["notAllowed"] as $testcase) {
            // Reset test
            $this->setUp();
            
            // Mocking
            $this->confService->expects($this->any())
                ->method('getAliasIdLen')
                ->willReturn(ConfigService::DEF_ALIAS_ID_LEN);
            
            $this->mapper->expects($this->any())
                ->method('containsAliasId')
                ->withAnyParameters()
                ->willReturn(false);
            
            $this->mapper->expects($this->any())
                ->method('insert')
                ->willReturnCallback(function($alias) {
                    return $alias;
                });
            
            // Test method
            $caught = false;
            try {
                $this->service->create(
                    $this->aliases[0]->getAliasName(),
                    $this->aliases[0]->getToMail(),
                    $testcase,
                    $this->aliases[0]->getUserId()
                    );
            }
            catch (ValueFormatException $e) {
                $caught = true;
            }
            catch (StringLengthException $e) {
                $caught = true;
            }
            
            if (!$caught) {
                $this->assertTrue(false, strval($testcase)." was accepted as comment.");
            }
        }
        
        // Prevent test of beeing useless in case of all length are rejected.
        $this->assertTrue(true);
    }
    
}
