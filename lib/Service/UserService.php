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