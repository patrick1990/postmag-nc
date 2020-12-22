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
    
    // Define test cases for settings
    private const DOMAIN_TEST_CASES = [
        "allowed" => [
            "example.com",
            "sub.domain.de",
            "abc.def.ghijk"
        ],
        "notAllowed" => [
            "",
            ".",
            "domain",
            ".com"
        ]
    ];
    private const USER_ALIAS_ID_LEN_TEST_CASES = [
        "allowed" => [
            ConfigService::MIN_USER_ALIAS_ID_LEN,
            ConfigService::DEF_USER_ALIAS_ID_LEN,
            ConfigService::MAX_USER_ALIAS_ID_LEN
        ],
        "notAllowed" => [
            ConfigService::MIN_USER_ALIAS_ID_LEN - 1,
            ConfigService::MAX_USER_ALIAS_ID_LEN + 1
        ]
    ];
    private const ALIAS_ID_LEN_TEST_CASES = [
        "allowed" => [
            ConfigService::MIN_ALIAS_ID_LEN,
            ConfigService::DEF_ALIAS_ID_LEN,
            ConfigService::MAX_ALIAS_ID_LEN
        ],
        "notAllowed" => [
            ConfigService::MIN_ALIAS_ID_LEN - 1,
            ConfigService::MAX_ALIAS_ID_LEN + 1
        ]
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
            $this->assertSame($val, $defConf[$key], $key." doesn't equals to the expected default value.");
        }
    }
    
    public function testSetDomainAllowed(): void {
        foreach (self::DOMAIN_TEST_CASES["allowed"] as $testcase) {
            // Reset test
            $this->setUp();
            
            // Mocking
            $this->config->expects($this->once())
                ->method('setAppValue')
                ->with($this->appName, 'targetDomain', $testcase);
            
            // Test method
            try {
                $this->service->setTargetDomain($testcase);
            }
            catch (ValueFormatException $e) {
                $this->assertTrue(false, strval($testcase)." was not accepted as domain.");
            }
        }
    }
    
    public function testSetDomainNotAllowed(): void {
        foreach (self::DOMAIN_TEST_CASES["notAllowed"] as $testcase) {
            // Reset test
            $this->setUp();
            
            // Test method
            $caught = false;
            try {
                $this->service->setTargetDomain($testcase);
            }
            catch (ValueFormatException $e) {
                $caught = true;
            }
            
            if (!$caught) {
                $this->assertTrue(false, strval($testcase)." was accepted as domain.");
            }
        }
        
        // Prevent test of beeing useless in case of all length are rejected.
        $this->assertTrue(true);
    }
    
    public function testSetUserAliasIdLenAllowed(): void {
        foreach (self::USER_ALIAS_ID_LEN_TEST_CASES["allowed"] as $testcase) {
            // Reset test
            $this->setUp();
            
            // Mocking
            $this->config->expects($this->once())
                ->method('setAppValue')
                ->with($this->appName, 'userAliasIdLen', $testcase);
            
            // Test method
            try {
                $this->service->setUserAliasIdLen($testcase);
            }
            catch (ValueBoundException $e) {
                $this->assertTrue(false, strval($testcase)." was not accepted as user alias id length.");
            }
        }
    }
    
    public function testSetUserAliasIdLenNotAllowed(): void {
        foreach (self::USER_ALIAS_ID_LEN_TEST_CASES["notAllowed"] as $testcase) {
            // Reset test
            $this->setUp();
            
            // Test method
            $caught = false;
            try {
                $this->service->setUserAliasIdLen($testcase);
            }
            catch (ValueBoundException $e) {
                $caught = true;
            }
            
            if (!$caught) {
                $this->assertTrue(false, strval($testcase)." was accepted as user alias id length.");
            }
        }
        
        // Prevent test of beeing useless in case of all length are rejected.
        $this->assertTrue(true);
    }
    
    public function testSetAliasIdLenAllowed(): void {
        foreach (self::ALIAS_ID_LEN_TEST_CASES["allowed"] as $testcase) {
            // Reset test
            $this->setUp();
            
            // Mocking
            $this->config->expects($this->once())
                ->method('setAppValue')
                ->with($this->appName, 'aliasIdLen', $testcase);
            
            // Test method
            try {
                $this->service->setAliasIdLen($testcase);
            }
            catch (ValueBoundException $e) {
                $this->assertTrue(false, strval($testcase)." was not accepted as alias id length.");
            }
        }
    }
    
    public function testSetAliasIdLenNotAllowed(): void {
        foreach (self::ALIAS_ID_LEN_TEST_CASES["notAllowed"] as $testcase) {
            // Reset test
            $this->setUp();
            
            // Test method
            $caught = false;
            try {
                $this->service->setAliasIdLen($testcase);
            }
            catch (ValueBoundException $e) {
                $caught = true;
            }
            
            if (!$caught) {
                $this->assertTrue(false, strval($testcase)." was accepted as alias id length.");
            }
        }
        
        // Prevent test of beeing useless in case of all length are rejected.
        $this->assertTrue(true);
    }
    
}