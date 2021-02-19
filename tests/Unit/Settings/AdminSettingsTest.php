<?php
declare(strict_types=1);

namespace OCA\Postmag\Tests\Unit\Settings;

use PHPUnit\Framework\TestCase;
use OCA\Postmag\Service\ConfigService;
use OCA\Postmag\Settings\AdminSettings;
use OCA\Postmag\Tests\Unit\Service\ConfigServiceTest;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Http;

class AdminSettingsTest extends TestCase {
    
    private $settings;
    private $service;
    
    public function setUp(): void {
        $this->service = $this->createMock(ConfigService::class);
        
        $this->settings = new AdminSettings('postmag', $this->service);
    }
    
    public function testGetSection() {
        $this->assertSame(
            'additional',
            $this->settings->getSection(),
            'Section is not additional.'
            );
    }
    
    public function testGetPriority() {
        $this->assertSame(
            100,
            $this->settings->getPriority(),
            'Priority is not 100.'
            );
    }
    
    public function testGetForm() {
        // Mocking
        $this->service->expects($this->once())
            ->method('getConf')
            ->willReturn(ConfigServiceTest::CONF_DEFAULTS);
        
        // Test method
        $ret = $this->settings->getForm();
        
        $this->assertTrue($ret instanceof TemplateResponse, 'Result should be a template response.');
        $this->assertSame(Http::STATUS_OK, $ret->getStatus(), 'HTTP status should be OK.');
        $this->assertSame('settingsAdmin', $ret->getTemplateName(), 'Template name has to be settings-admin.');
        $this->assertSame(ConfigServiceTest::CONF_DEFAULTS, $ret->getParams(), 'The template didnt get the right params.');
    }
    
}