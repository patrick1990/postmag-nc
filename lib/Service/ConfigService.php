<?php
namespace OCA\Postmag\Service;

use OCP\IConfig;

class ConfigService {
    
    public const DEF_DOMAIN = 'example.com';
    
    public const MIN_USER_ALIAS_ID_LEN = 2;
    public const MAX_USER_ALIAS_ID_LEN = 10;
    public const DEF_USER_ALIAS_ID_LEN = 4;
    
    public const MIN_ALIAS_ID_LEN = 2;
    public const MAX_ALIAS_ID_LEN = 10;
    public const DEF_ALIAS_ID_LEN = 4;
    
    private $config;
    private $appName;
    
    public function __construct(IConfig $config, $appName) {
        $this->config = $config;
        $this->appName = $appName;
    }
    
    public function getConf(): array {
        return array(
            'domain' => $this->getTargetDomain(),
            'userAliasIdLen' => $this->getUserAliasIdLen(),
            'aliasIdLen' => $this->getAliasIdLen()
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
        $domainRegex = "^([a-z0-9]+(-[a-z0-9]+)*\.)+[a-z]{2,}$";
        if(preg_match($domainRegex, $domain) !== 1)
            throw new ValueFormatException("the configured domain have to be a domain");
        
        $this->config->setAppValue($this->appName, 'targetDomain', $domain);
    }
    
    /**
     * @param int $len
     */
    public function setUserAliasIdLen(int $len) {
        if($len < self::MIN_USER_ALIAS_ID_LEN || $len > self::MAX_USER_ALIAS_ID_LEN)
            throw new ValueBoundException("user alias id length has to be between ".self::MIN_USER_ALIAS_ID_LEN." and ".self::MAX_USER_ALIAS_ID_LEN);
        
        $this->config->setAppValue($this->appName, 'userAliasIdLen', $len);
    }
    
    /**
     * @param int $len
     */
    public function setAliasIdLen(int $len) {
        if($len < self::MIN_ALIAS_ID_LEN || $len > self::MAX_ALIAS_ID_LEN)
            throw new ValueBoundException("alias id length has to be between ".self::MIN_USER_ALIAS_ID_LEN." and ".self::MAX_USER_ALIAS_ID_LEN);
            
        $this->config->setAppValue($this->appName, 'aliasIdLen', $len);
    }
}