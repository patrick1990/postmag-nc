<?php
declare(strict_types=1);

namespace OCA\Postmag\Tests\Unit\Service;

use PHPUnit\Framework\TestCase;
use OCA\Postmag\Service\ConfigService;
use OCP\IConfig;
use OCA\Postmag\Service\Exceptions\ValueFormatException;
use OCA\Postmag\Service\Exceptions\ValueBoundException;

class ConfigServiceTest extends TestCase {
    
    private const CONF_DEFAULTS = [
        'domain' => ConfigService::DEF_DOMAIN,
        'regexDomain' => ConfigService::REGEX_DOMAIN,
        'regexEMail' => ConfigService::REGEX_EMAIL,
        'regexAliasName' => ConfigService::REGEX_ALIAS_NAME,
        'userAliasIdLen' => ConfigService::DEF_ALIAS_ID_LEN,
        'userAliasIdLenMin' => ConfigService::MIN_USER_ALIAS_ID_LEN,
        'userAliasIdLenMax' => ConfigService::MAX_USER_ALIAS_ID_LEN,
        'aliasIdLen' => ConfigService::DEF_ALIAS_ID_LEN,
        'aliasIdLenMin' => ConfigService::MIN_ALIAS_ID_LEN,
        'aliasIdLenMax' => ConfigService::MAX_ALIAS_ID_LEN,
        'aliasNameLenMax' => ConfigService::MAX_ALIAS_NAME_LEN,
        'toMailLenMax' => ConfigService::MAX_TO_MAIL_LEN,
        'commentLenMax' => ConfigService::MAX_COMMENT_LEN
    ];
    
    private $service;
    private $appName = "postmag";
    private $config;
    
    public function setUp(): void {
        $this->config = $this->createMock(IConfig::class);
        
        $this->service = new ConfigService(
            $this->appName,
            $this->config
        );
    }
    
    public function testGetConfDefault(): void {
        // Mocking
        $this->config->expects($this->exactly(3))
            ->method('getAppValue')
            ->with(
                $this->appName,
                $this->logicalOr('targetDomain', 'userAliasIdLen', 'aliasIdLen'),
                $this->logicalOr(ConfigService::DEF_DOMAIN, ConfigService::DEF_USER_ALIAS_ID_LEN, ConfigService::DEF_ALIAS_ID_LEN)
            )
            ->willReturnCallback(function($appName, $key, $default) {
                return $default;
            });
        
        // Test method
        $defConf = $this->service->getConf();
        
        foreach (self::CONF_DEFAULTS as $key => $val) {
            $this->assertTrue(array_key_exists($key, $defConf), $key." doesn't exist in getConf.");
            $this->assertEquals($val, $defConf[$key], $key." doesn't equals to the expected default value.");
        }
    }
    
    public function testSetDomain(): void {
        $goodDomain = "sub.mydomain.com";
        $badDomain = ".com";
        
        // Mocking
        $this->config->expects($this->once())
            ->method('setAppValue')
            ->with($this->appName, 'targetDomain', $goodDomain);
        
        // Test method - good case
        $this->service->setTargetDomain($goodDomain);
        
        // Test method - bad case
        $this->expectException(ValueFormatException::class);
        $this->service->setTargetDomain($badDomain);
    }
    
    public function testSetUserAliasIdLen(): void {
        $goodLen = ConfigService::MIN_USER_ALIAS_ID_LEN;
        $badLen = ConfigService::MAX_USER_ALIAS_ID_LEN + 1;
        
        // Mocking
        $this->config->expects($this->once())
            ->method('setAppValue')
            ->with($this->appName, 'userAliasIdLen', $goodLen);
        
        // Test method - good case
        $this->service->setUserAliasIdLen($goodLen);
        
        // Test method - bad case
        $this->expectException(ValueBoundException::class);
        $this->service->setUserAliasIdLen($badLen);
    }
    
    public function testSetAliasIdLen(): void {
        $goodLen = ConfigService::MIN_ALIAS_ID_LEN;
        $badLen = ConfigService::MAX_ALIAS_ID_LEN + 1;
        
        // Mocking
        $this->config->expects($this->once())
            ->method('setAppValue')
            ->with($this->appName, 'aliasIdLen', $goodLen);
        
        // Test method - good case
        $this->service->setAliasIdLen($goodLen);
        
        // Test method - bad case
        $this->expectException(ValueBoundException::class);
        $this->service->setAliasIdLen($badLen);
    }
    
}