<?php
namespace OCA\Postmag\Service;

use OCP\IConfig;

class UserService {
    
    private $config;
    
    public function __construct(IConfig $config) {
        $this->config = $config;
    }
    
    public function getUserEMail($userId) {
        return $this->config->getUserValue($userId, 'settings', 'email');
    }
}