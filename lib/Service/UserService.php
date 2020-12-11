<?php
namespace OCA\Postmag\Service;

use OCP\IConfig;
use OCA\Postmag\Db\UserMapper;
use OCA\Postmag\Db\User;

class UserService {
    
    private $config;
    private $mapper;
    
    public function __construct(IConfig $config, UserMapper $mapper) {
        $this->config = $config;
        $this->mapper = $mapper;
    }
    
    public function getUserEMail(string $userId): string {
        return $this->config->getUserValue($userId, 'settings', 'email');
    }
    
    public function getUserAlias(string $userId): string {
        try {
            $user = $this->mapper->find($userId);
        }
        catch(\OCP\AppFramework\Db\DoesNotExistException $e) {
            $user = new User();
            $user->setUserId($userId);
            $user->setUserAlias('abcd'); // TODO: Generate random string
        }
        
        return $user->getUserAlias();
    }
    
}