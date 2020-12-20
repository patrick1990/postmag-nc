<?php
declare(strict_types=1);

namespace OCA\Postmag\Db;

use OCP\AppFramework\Db\Entity;

class User extends Entity {
    
    protected $userId;
    protected $userAliasId;
    
    public function __construct() {
        $this->addType('id', 'int');
        $this->addType('user_id', 'string');
        $this->addType('user_alias_id', 'string');
    }
    
    public function serialize(): array {
        return [
            'id' => $this->id,
            'user_id' => $this->userId,
            'user_alias_id' => $this->userAliasId
        ];
    }
    
}
