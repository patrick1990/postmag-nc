<?php
namespace OCA\Postmag\Service;

use OCP\IConfig;

class ConfigService {
    
    private $config;
    private $appName;
    
    public function __construct(IConfig $config, $appName) {
        $this->config = $config;
        $this->appName = $appName;
    }
    
    public function getTargetDomain() {
        return $this->config->getAppValue($this->appName, 'targetDomain', 'example.com');
    }
    
    public function setTargetDomain(string $domain) {
        $this->config->setAppValue($this->appName, 'targetDomain', $domain);
    }
}