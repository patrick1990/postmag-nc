<?php
declare(strict_types=1);

namespace OCA\Postmag\Tests\Integration\Controller;

use Test\TestCase;
use OCP\AppFramework\App;
use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\Http;
use OCA\Postmag\Tests\Unit\Service\ConfigServiceTest;

class ConfigControllerTest extends TestCase {
    
    private $controller;
    private $userId = 'john';
    
    private $confCache;
    
    public function setUp(): void {
        parent::setUp();
        $app = new App('postmag');
        $container = $app->getContainer();
        
        // replace user id
        $container->registerService('UserId', function ($c) {
            return $this->userId;
        });
        
        $this->controller = $container->query('OCA\Postmag\Controller\ConfigController');
        
        $this->confCache = $this->controller->getConf()->getData();
    }
    
    public function tearDown(): void {
        // Reset config
        $this->controller->setConf(
            $this->confCache['domain'],
            $this->confCache['userAliasIdLen'],
            $this->confCache['aliasIdLen']
            );
    }
    
    public function testGetConf(): void {
        $ret = $this->controller->getConf();
        
        $this->assertTrue($ret instanceof JSONResponse, 'Result should be a JSON response.');
        $this->assertSame(Http::STATUS_OK, $ret->getStatus(), 'HTTP status should be OK.');
        $this->assertSame($this->confCache, $ret->getData(), 'Did not return the expected config array');
        
        foreach (array_keys(ConfigServiceTest::CONF_DEFAULTS) as $config) {
            $this->assertTrue(array_key_exists($config, $ret->getData()), $config." doesn't exist in getConf.");
        }
    }
    
    public function testSetConf(): void {
        $newDomain = 'mydomain.org';
        $newUserAliasIdLen = 6;
        $newAliasIdLen = 5;
        
        $ret = $this->controller->setConf($newDomain, $newUserAliasIdLen, $newAliasIdLen);
        
        $this->assertTrue($ret instanceof JSONResponse, 'Result should be a JSON response.');
        $this->assertSame(Http::STATUS_OK, $ret->getStatus(), 'HTTP status should be OK.');
        $this->assertSame($newDomain, $ret->getData()['domain'], 'Did not return the expected domain.');
        $this->assertSame($newUserAliasIdLen, $ret->getData()['userAliasIdLen'], 'Did not return the expected user alias id len.');
        $this->assertSame($newAliasIdLen, $ret->getData()['aliasIdLen'], 'Did not return the expected alias id len.');
    }
    
}