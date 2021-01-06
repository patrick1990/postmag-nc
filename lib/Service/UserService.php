<?php
declare(strict_types=1);

namespace OCA\Postmag\Service;

use OCP\IConfig;
use OCA\Postmag\Db\UserMapper;
use OCA\Postmag\Db\User;
use OCA\Postmag\Share\Random;

class UserService {
    
    private $config;
    private $mapper;
    private $confService;
    
    public function __construct(IConfig $config, UserMapper $mapper, ConfigService $confService) {
        $this->config = $config;
        $this->mapper = $mapper;
        $this->confService = $confService;
    }
    
    public function getUserEMail(string $userId): string {
        return $this->config->getUserValue($userId, 'settings', 'email');
    }
    
    public function find(string $userId): array {
        try {
            return $this->mapper->findUser($userId)->serialize();
        }
        catch(\OCP\AppFramework\Db\DoesNotExistException $e) {
            // Generate new user alias id
            $userAliasId = Random::hexString($this->confService->getUserAliasIdLen());
            while ($this->mapper->containsAliasId($userAliasId)) {
                $userAliasId = Random::hexString($this->confService->getUserAliasIdLen());
            }
            
            $user = new User();
            $user->setUserId($userId);
            $user->setUserAliasId($userAliasId);
            return $this->mapper->insert($user)->serialize();
        }
    }
    
    public function getUserAliasId(string $userId): string {
        return $this->find($userId)['user_alias_id'];
    }
    
}