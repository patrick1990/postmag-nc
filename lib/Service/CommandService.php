<?php
declare(strict_types=1);

namespace OCA\Postmag\Service;

use OCA\Postmag\Db\AliasMapper;

class CommandService {
    
    private $mapper;
    private $userService;
    
    public function __construct(AliasMapper $mapper, UserService $userService) {
        $this->mapper = $mapper;
        $this->userService = $userService;
    }
    
    public function formatPostfixAliasFile(): iterable {
        $aliases = $this->mapper->findAll(null);
        
        // add comments
        $ret[] = "######################";
        $ret[] = "# Postmag Alias File #";
        $ret[] = "######################";
        $ret[] = "";
        $ret[] = "# This file was autogenerated by Postmag for Nextcloud.";
        $ret[] = "# https://github.com/patrick1990/postmag-nc";
        $ret[] = "";
        
        // add aliases
        foreach ($aliases as $alias) {
            // Add created and last modified time stamp
            $ret[] = "# Created: "
                        .strval($alias->getCreated())
                        .", Modified: "
                        .strval($alias->getLastModified());
            
            // Add alias
            $ret[] = ($alias->getEnabled() ? "" : "# ")
                        .$alias->getAliasName()
                        ."."
                        .$alias->getAliasId()
                        ."."
                        .$this->userService->getUserAliasId($alias->getUserId())
                        .": "
                        .$alias->getToMail();
        }
        
        return $ret;
    }
    
    public function getLastModified(bool $formatted = false): string {
        try {
            $lastModified = $this->mapper->findLastModified(null)->getLastModified();
        }
        catch(\OCP\AppFramework\Db\DoesNotExistException $e) {
            $lastModified = 0;
        }
        
        if ($formatted) {
            return (new \DateTime())->setTimestamp($lastModified)->format('Y-m-d_H:i:s');
        }
        
        return strval($lastModified);
    }
    
}