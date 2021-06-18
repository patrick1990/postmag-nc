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

namespace OCA\Postmag\Settings;

use OCP\Settings\ISettings;
use OCP\AppFramework\Http\TemplateResponse;
use OCA\Postmag\Service\ConfigService;

class AdminSettings implements ISettings {
    
    private $appName;
    private $service;
    
    public function __construct($AppName, ConfigService $service) {
        $this->appName = $AppName;
        $this->service = $service;
    }
    
    public function getForm(): TemplateResponse {
        return new TemplateResponse($this->appName, 'settingsAdmin', $this->service->getConf());
    }
    
    public function getSection(): string {
        return 'additional';
    }
    
    public function getPriority(): int {
        return 100;
    }
    
}