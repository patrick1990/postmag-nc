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

use OCA\Postmag\AppInfo\Application;
use PHPUnit\Framework\TestCase;
use OCA\Postmag\Service\ConfigService;
use OCP\IConfig;
use OCA\Postmag\Service\Exceptions\ValueFormatException;
use OCA\Postmag\Service\Exceptions\ValueBoundException;

class ConfigServiceTest extends TestCase {
    
    public const CONF_DEFAULTS = [
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
        'commentLenMax' => ConfigService::MAX_COMMENT_LEN,
        'readyTime' => ConfigService::DEF_READY_TIME,
        'readyTimeMin' => ConfigService::MIN_READY_TIME
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
    private const READY_TIME_TEST_CASES = [
        "allowed" => [
            ConfigService::DEF_READY_TIME,
            ConfigService::MIN_READY_TIME,
            2*ConfigService::DEF_READY_TIME
        ],
        "notAllowed" => [
            ConfigService::MIN_READY_TIME - 1
        ]
    ];
    
    private $service;
    private $appName = Application::APP_ID;
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
        $this->config->expects($this->exactly(4))
            ->method('getAppValue')
            ->with(
                $this->appName,
                $this->logicalOr('targetDomain', 'userAliasIdLen', 'aliasIdLen', 'readyTime'),
                $this->logicalOr(ConfigService::DEF_DOMAIN, ConfigService::DEF_USER_ALIAS_ID_LEN, ConfigService::DEF_ALIAS_ID_LEN, ConfigService::DEF_READY_TIME)
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

    public function testSetReadyTimeAllowed(): void {
        foreach (self::READY_TIME_TEST_CASES["allowed"] as $testcase) {
            // Reset test
            $this->setUp();

            // Mocking
            $this->config->expects($this->once())
                ->method('setAppValue')
                ->with($this->appName, 'readyTime', $testcase);

            // Test method
            try {
                $this->service->setReadyTime($testcase);
            }
            catch (ValueBoundException $e) {
                $this->assertTrue(false, strval($testcase)." was not accepted as ready time.");
            }
        }
    }

    public function testSetReadyTimeNotAllowed(): void {
        foreach (self::READY_TIME_TEST_CASES["notAllowed"] as $testcase) {
            // Reset test
            $this->setUp();

            // Test method
            $caught = false;
            try {
                $this->service->setReadyTime($testcase);
            }
            catch (ValueBoundException $e) {
                $caught = true;
            }

            if (!$caught) {
                $this->assertTrue(false, strval($testcase)." was accepted as ready time.");
            }
        }

        // Prevent test of beeing useless in case of all length are rejected.
        $this->assertTrue(true);
    }
    
}