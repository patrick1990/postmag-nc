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

namespace OCA\Postmag\Tests\Unit\Settings;

use OCA\Postmag\AppInfo\Application;
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
        
        $this->settings = new AdminSettings(Application::APP_ID, $this->service);
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