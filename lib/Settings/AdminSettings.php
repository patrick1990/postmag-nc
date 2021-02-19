<?php
declare(strict_types=1);

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