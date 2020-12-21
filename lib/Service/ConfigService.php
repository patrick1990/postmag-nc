<?php
declare(strict_types=1);

namespace OCA\Postmag\Service;

use OCP\IConfig;

class ConfigService {
    
    public const DEF_DOMAIN = 'example.com';
    public const REGEX_DOMAIN = '^([a-z0-9]+(-[a-z0-9]+)*\.)+[a-z]{2,}$';
    public const REGEX_EMAIL = '^([a-z0-9]+([-_][a-z0-9]+)*\.)+@([a-z0-9]+(-[a-z0-9]+)*\.)+[a-z]{2,}$';
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
    
    private $config;
    private $appName;
    
    public function __construct(IConfig $config, $appName) {
        $this->config = $config;
        $this->appName = $appName;
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
            'commentLenMax' => self::MAX_COMMENT_LEN
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
}