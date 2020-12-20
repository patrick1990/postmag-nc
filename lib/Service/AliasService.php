<?php
declare(strict_types=1);

namespace OCA\Postmag\Service;

use OCP\IConfig;
use OCA\Postmag\Db\AliasMapper;
use OCA\Postmag\Db\Alias;
use OCA\Postmag\Share\Random;
use OCP\IDateTimeFormatter;

class AliasService {
    
    use Errors;
    
    private $config;
    private $dateTimeFormatter;
    private $mapper;
    private $confService;
    
    public function __construct(IConfig $config,
                                IDateTimeFormatter $dateTimeFormatter,
                                AliasMapper $mapper,
                                ConfigService $confService)
    {
        $this->config = $config;
        $this->dateTimeFormatter = $dateTimeFormatter;
        $this->mapper = $mapper;
        $this->confService = $confService;
    }
    
    public function findAll(string $userId): array {
        $ret = $this->mapper->findAll($userId);
        array_walk($ret, function (&$value, $key) use ($this) {
            $value->serialize($this->dateTimeFormatter);
        });
        return $ret;
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
            if(preg_match($aliasName, ConfigService::REGEX_ALIAS_NAME) !== 1) {
                throw new Exceptions\ValueFormatException("The alias name have to be of the right format");
            }
        }
        
        if($toMail !== null) {
            // Check string length
            if(strlen($toMail) > ConfigService::MAX_TO_MAIL_LEN) {
                throw new Exceptions\StringLengthException("Maximum allowed length of to_mail is ".ConfigService::MAX_TO_MAIL_LEN);
            }
            
            // Check string format
            if(preg_match($toMail, ConfigService::REGEX_EMAIL) !== 1) {
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
    
    public function create(string $aliasName, string $toMail, string $comment, string $userId): Alias {
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
        $alias->setLastModifed($now->getTimestamp());
        
        return $this->mapper->insert($alias)->serialize($this->dateTimeFormatter);
    }
    
    public function update(int $id, string $toMail, string $comment, bool $enabled, string $userId): Alias {
        $this->checkParameters(null, $toMail, $comment);
        
        try {
            // Get DateTime
            $now = new \DateTime('now');
            
            $alias = $this->mapper->find($id, $userId);
            $alias->setToMail($toMail);
            $alias->setComment($comment);
            $alias->setEnabled($enabled);
            $alias->setLastModifed($now->getTimestamp());
            
            return $this->mapper->update($alias)->serialize($this->dateTimeFormatter);
        }
        catch (\Exception $e) {
            $this->handleDbException($e);
        }
    }
    
}