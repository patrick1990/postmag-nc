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
use OCA\Postmag\Db\AliasMapper;
use OCA\Postmag\Db\Alias;
use OCA\Postmag\Share\Random;
use OCP\IDateTimeFormatter;

class AliasService {
    
    use Errors;

    private $appName;
    private $config;
    private $dateTimeFormatter;
    private $mapper;
    private $confService;
    
    public function __construct($AppName,
                                IConfig $config,
                                IDateTimeFormatter $dateTimeFormatter,
                                AliasMapper $mapper,
                                ConfigService $confService)
    {
        $this->appName = $AppName;
        $this->config = $config;
        $this->dateTimeFormatter = $dateTimeFormatter;
        $this->mapper = $mapper;
        $this->confService = $confService;
    }

    public function find(int $id, string $userId): array {
        try {
            return $this->mapper->find($id, $userId)->serialize($this->dateTimeFormatter);
        }
        catch (\Exception $e) {
            $this->handleDbException($e);
        }
    }

    public function findAll(int $firstResult, int $maxResults, string $userId): array {
        if($maxResults <= 0 || $maxResults > ConfigService::MAX_ALIAS_RESULTS) {
            throw new Exceptions\ValueBoundException("Max results has to be positive and lesser than ".ConfigService::MAX_ALIAS_RESULTS);
        }

        $ret = $this->mapper->findAll($firstResult, $maxResults, $userId);
        array_walk($ret, function (&$value, $key) {
            $value = $value->serialize($this->dateTimeFormatter);
        });
        return $ret;
    }

    public function search(string $query, string $userId): array {
        $queryArr = preg_split('/\s+/', $query);

        $ret = array_filter(
            $this->mapper->findAll(null, null, $userId),
            function (Alias $alias) use ($queryArr): bool {
                foreach ($queryArr as $q) {
                    if (stripos($alias->getAliasName(), $q) !== false)
                        return true;
                    elseif (stripos($alias->getComment(), $q) !== false)
                        return true;
                }
                return false;
            }
        );

        array_walk($ret, function (&$value, $key) {
            $value = $value->serialize($this->dateTimeFormatter);
        });
        return array_values($ret);
    }
    
    private function checkParameters(string $aliasName = null,
                                     string $toMail = null,
                                     string $comment = null)
    {
        if($aliasName !== null) {
            // Check string length
            if(strlen($aliasName) > ConfigService::MAX_ALIAS_NAME_LEN) {
                throw new Exceptions\StringLengthException("Maximum allowed length of alias_name is ".ConfigService::MAX_ALIAS_NAME_LEN);
            }
            
            // Check string format
            if(preg_match("/".ConfigService::REGEX_ALIAS_NAME."/", $aliasName) !== 1) {
                throw new Exceptions\ValueFormatException("The alias name have to be of the right format");
            }
        }
        
        if($toMail !== null) {
            // Check string length
            if(strlen($toMail) > ConfigService::MAX_TO_MAIL_LEN) {
                throw new Exceptions\StringLengthException("Maximum allowed length of to_mail is ".ConfigService::MAX_TO_MAIL_LEN);
            }
            
            // Check string format
            if(preg_match("/".ConfigService::REGEX_EMAIL."/", $toMail) !== 1) {
                throw new Exceptions\ValueFormatException("The To mail address have to be a valid mail address");
            }
        }
        
        if($comment !== null) {
            // Check string length
            if(strlen($comment) > ConfigService::MAX_COMMENT_LEN) {
                throw new Exceptions\StringLengthException("Maximum allowed length of comment is ".ConfigService::MAX_COMMENT_LEN);
            }
        }
    }
    
    public function create(string $aliasName, string $toMail, string $comment, string $userId): array {
        $this->checkParameters($aliasName, $toMail, $comment);
        
        // Generate new alias id
        $aliasId = Random::hexString($this->confService->getAliasIdLen());
        while ($this->mapper->containsAliasId($aliasId, $userId, $aliasName)) {
            $aliasId = Random::hexString($this->confService->getAliasIdLen());
        }
        
        // Get DateTime
        $now = new \DateTime('now');
        
        $alias = new Alias();
        $alias->setUserId($userId);
        $alias->setAliasId($aliasId);
        $alias->setAliasName($aliasName);
        $alias->setToMail($toMail);
        $alias->setComment($comment);
        $alias->setEnabled(True);
        $alias->setCreated($now->getTimestamp());
        $alias->setLastModified($now->getTimestamp());

        $this->config->setAppValue($this->appName, 'lastModified', strval($now->getTimestamp()));
        return $this->mapper->insert($alias)->serialize($this->dateTimeFormatter);
    }
    
    public function update(int $id, string $toMail, string $comment, bool $enabled, string $userId): array {
        $this->checkParameters(null, $toMail, $comment);
        
        try {
            // Get DateTime
            $now = new \DateTime('now');
            
            $alias = $this->mapper->find($id, $userId);
            $alias->setToMail($toMail);
            $alias->setComment($comment);
            $alias->setEnabled($enabled);
            $alias->setLastModified($now->getTimestamp());

            $this->config->setAppValue($this->appName, 'lastModified', strval($now->getTimestamp()));
            return $this->mapper->update($alias)->serialize($this->dateTimeFormatter);
        }
        catch (\Exception $e) {
            $this->handleDbException($e);
        }
    }
    
    public function delete(int $id, string $userId): array {
        try {
            // Get DateTime
            $now = new \DateTime('now');

            $alias = $this->mapper->find($id, $userId);

            $this->config->setAppValue($this->appName, 'lastModified', strval($now->getTimestamp()));
            return $this->mapper->delete($alias)->serialize($this->dateTimeFormatter);
        }
        catch (\Exception $e) {
            $this->handleDbException($e);
        }
    }

    public function getLastModified(): string {
        return $this->config->getAppValue($this->appName, 'lastModified', '0');
    }
    
}