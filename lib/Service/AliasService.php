<?php
namespace OCA\Postmag\Service;

use OCP\IConfig;
use OCA\Postmag\Db\AliasMapper;
use OCA\Postmag\Db\Alias;
use OCA\Postmag\Share\Random;
use OC\DateTimeFormatter;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;

class AliasService {
    
    use Errors;
    
    private $config;
    private $dtFormater;
    private $mapper;
    private $confService;
    
    public function __construct(IConfig $config,
                                DateTimeFormatter $dtFormater,
                                AliasMapper $mapper,
                                ConfigService $confService)
    {
        $this->config = $config;
        $this->dtFormater = $dtFormater;
        $this->mapper = $mapper;
        $this->confService = $confService;
    }
    
    public function findAll(string $userId): array {
        return $this->mapper->findAll($userId);
    }
    
    private function checkParameters(string $aliasName = null,
                                     string $toMail = null,
                                     string $comment = null)
    {
        if($aliasName !== null) {
            if(strlen($aliasName) > ConfigService::MAX_ALIAS_NAME_LEN) {
                throw new Exceptions\StringLengthException("Maximum allowed length of alias_name is ".ConfigService::MAX_ALIAS_NAME_LEN);
            }
        }
        if($toMail !== null) {
            if(strlen($toMail) > ConfigService::MAX_TO_MAIL_LEN) {
                throw new Exceptions\StringLengthException("Maximum allowed length of to_mail is ".ConfigService::MAX_TO_MAIL_LEN);
            }
        }
        if($comment !== null) {
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
        $now = $this->dtFormater->getDateTime(null);
        
        $alias = new Alias();
        $alias->setUserId($userId);
        $alias->setAliasId($aliasId);
        $alias->setAliasName($aliasName);
        $alias->setToMail($toMail);
        $alias->setComment($comment);
        $alias->setEnabled(True);
        $alias->setCreatedDT($now);
        $alias->setLastModifedDT($now);
        
        return $this->mapper->insert($alias);;
    }
    
    public function update(int $id, string $toMail, string $comment, bool $enabled, string $userId): Alias {
        $this->checkParameters(null, $toMail, $comment);
        
        try {
            // Get DateTime
            $now = $this->dtFormater->getDateTime(null);
            
            $alias = $this->mapper->find($id, $userId);
            $alias->setToMail($toMail);
            $alias->setComment($comment);
            $alias->setEnabled($enabled);
            $alias->setLastModifedDT($now);
            
            return $this->mapper->update($alias);
        }
        catch (\Exception $e) {
            $this->handleDbException($e);
        }
    }
    
}