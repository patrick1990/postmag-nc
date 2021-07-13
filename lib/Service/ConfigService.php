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

class ConfigService {
    
    public const DEF_DOMAIN = 'example.com';
    public const REGEX_DOMAIN = '^([a-z0-9]+(-[a-z0-9]+)*\.)+[a-z]{2,}$';
    public const REGEX_EMAIL = '^[a-z0-9]+([-_\.][a-z0-9]+)*@([a-z0-9]+(-[a-z0-9]+)*\.)+[a-z]{2,}$';
    public const REGEX_ALIAS_NAME = '^[a-z0-9]+$';
    
    public const MIN_USER_ALIAS_ID_LEN = 2;
    public const MAX_USER_ALIAS_ID_LEN = 8;
    public const DEF_USER_ALIAS_ID_LEN = 4;
    
    public const MIN_ALIAS_ID_LEN = 2;
    public const MAX_ALIAS_ID_LEN = 8;
    public const DEF_ALIAS_ID_LEN = 4;
    
    public const MAX_ALIAS_NAME_LEN = 20;
    public const MAX_TO_MAIL_LEN = 256;
    public const MAX_COMMENT_LEN = 40;
    public const MAX_ALIAS_RESULTS = 30;

    public const MIN_READY_TIME = 0;
    public const DEF_READY_TIME = 60;
    
    private $config;
    private $appName;
    
    public function __construct($AppName, IConfig $config) {
        $this->config = $config;
        $this->appName = $AppName;
    }
    
    public function getConf(): array {
        return array(
            'domain' => $this->getTargetDomain(),
            'regexDomain' => self::REGEX_DOMAIN,
            'regexEMail' => self::REGEX_EMAIL,
            'regexAliasName' => self::REGEX_ALIAS_NAME,
            'userAliasIdLen' => $this->getUserAliasIdLen(),
            'userAliasIdLenMin' => self::MIN_USER_ALIAS_ID_LEN,
            'userAliasIdLenMax' => self::MAX_USER_ALIAS_ID_LEN,
            'aliasIdLen' => $this->getAliasIdLen(),
            'aliasIdLenMin' => self::MIN_ALIAS_ID_LEN,
            'aliasIdLenMax' => self::MAX_ALIAS_ID_LEN,
            'aliasNameLenMax' => self::MAX_ALIAS_NAME_LEN,
            'toMailLenMax' => self::MAX_TO_MAIL_LEN,
            'commentLenMax' => self::MAX_COMMENT_LEN,
            'maxAliasResults' => self::MAX_ALIAS_RESULTS,
            'readyTime' => $this->getReadyTime(),
            'readyTimeMin' => self::MIN_READY_TIME
        );
    }
    
    public function getTargetDomain(): string {
        return $this->config->getAppValue($this->appName, 'targetDomain', self::DEF_DOMAIN);
    }
    
    public function getUserAliasIdLen(): int {
        return (int)$this->config->getAppValue($this->appName, 'userAliasIdLen', self::DEF_USER_ALIAS_ID_LEN);
    }
    
    public function getAliasIdLen(): int {
        return (int)$this->config->getAppValue($this->appName, 'aliasIdLen', self::DEF_ALIAS_ID_LEN);
    }

    public function getReadyTime(): int {
        return (int)$this->config->getAppValue($this->appName, 'readyTime', self::DEF_READY_TIME);
    }
    
    /**
     * @param string $domain
     */
    public function setTargetDomain(string $domain) {
        // Check if $domain is indeed a domain
        if(preg_match("/".self::REGEX_DOMAIN."/", $domain) !== 1)
            throw new Exceptions\ValueFormatException("The configured domain have to be a domain");
        
        $this->config->setAppValue($this->appName, 'targetDomain', $domain);
    }
    
    /**
     * @param int $len
     */
    public function setUserAliasIdLen(int $len) {
        if($len < self::MIN_USER_ALIAS_ID_LEN || $len > self::MAX_USER_ALIAS_ID_LEN)
            throw new Exceptions\ValueBoundException("User alias id length has to be between ".self::MIN_USER_ALIAS_ID_LEN." and ".self::MAX_USER_ALIAS_ID_LEN);
        
        $this->config->setAppValue($this->appName, 'userAliasIdLen', $len);
    }
    
    /**
     * @param int $len
     */
    public function setAliasIdLen(int $len) {
        if($len < self::MIN_ALIAS_ID_LEN || $len > self::MAX_ALIAS_ID_LEN)
            throw new Exceptions\ValueBoundException("Alias id length has to be between ".self::MIN_USER_ALIAS_ID_LEN." and ".self::MAX_USER_ALIAS_ID_LEN);
            
        $this->config->setAppValue($this->appName, 'aliasIdLen', $len);
    }

    /**
     * @param int $readyTime
     */
    public function setReadyTime(int $readyTime) {
        if($readyTime < self::MIN_READY_TIME)
            throw new Exceptions\ValueBoundException("Ready time has to be not lower than ".self::MIN_READY_TIME);

        $this->config->setAppValue($this->appName, 'readyTime', $readyTime);
    }
}