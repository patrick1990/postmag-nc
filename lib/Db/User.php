<?php
namespace OCA\Postmag\Db;

use JsonSerializable;
use OCP\AppFramework\Db\Entity;

class User extends Entity implements JsonSerializable {
    
    protected $userId;
    protected $userAlias;
    
    public function __construct() {
        $this->addType('id', 'int');
        $this->addType('user_id', 'string');
        $this->addType('user_alias_id', 'string');
    }
    
    public function jsonSerialize() {
        return [
            'id' => $this->id,
            'user_id' => $this->userId,
            'user_alias_id' => $this->userAlias
        ];
    }
    
}