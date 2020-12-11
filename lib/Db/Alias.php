<?php
namespace OCA\Postmag\Db;

use JsonSerializable;
use OCP\AppFramework\Db\Entity;

class Alias extends Entity implements JsonSerializable {
    
    protected $userId;
    protected $aliasId;
    protected $aliasName;
    protected $toMail;
    protected $comment;
    protected $enabled;
    protected $created;
    protected $lastModified;
    
    public function __construct() {
        $this->addType('id', 'int');
        $this->addType('user_id', 'string');
        $this->addType('alias_id', 'string');
        $this->addType('alias_name', 'string');
        $this->addType('to_mail', 'string');
        $this->addType('comment', 'string');
        $this->addType('enabled', 'bool');
        $this->addType('created', 'int');
        $this->addType('last_modified', 'int');
    }
    
    public function jsonSerialize(): array {
        return [
            'id' => $this->id,
            'user_id' => $this->userId,
            'alias_id' => $this->aliasId,
            'alias_name' => $this->aliasName,
            'to_mail' => $this->toMail,
            'comment' => $this->comment,
            'enabled' => $this->enabled,
            'created' => $this->created,
            'last_modified' => $this->lastModified,
        ];
    }
    
    public function getUserId(): string {
        return $this->userId;
    }
    
    public function setUserId(string $userId) {
        $this->userId = $userId;
    }
    
    public function getAliasId(): string {
        return $this->aliasId;
    }
    
    public function setAliasId(string $aliasId) {
        $this->aliasId = $aliasId;
    }
    
    public function getAliasName(): string {
        return $this->aliasName;
    }
    
    public function setAliasName(string $aliasName) {
        $this->aliasName = $aliasName;
    }
    
    public function getToMail(): string {
        return $this->toMail;
    }
    
    public function setToMail(string $toMail) {
        $this->toMail = $toMail;
    }
    
    public function getComment(): string {
        return $this->comment;
    }
    
    public function setComment(string $comment) {
        $this->comment = $comment;
    }
    
    public function getEnabled(): bool {
        return $this->enabled;
    }
    
    public function setEnabled(bool $enabled) {
        $this->enabled = $enabled;
    }
    
    public function getCreated(): \DateTime {
        $created = new \DateTime();
        $created->setTimestamp($this->created);
        return $created;
    }
    
    public function setCreated(\DateTime $created) {
        $this->created = $created->getTimestamp();
    }
    
    public function getLastModified(): \DateTime {
        $lastModified = new \DateTime();
        $lastModified->setTimestamp($this->lastModified);
        return $lastModified;
    }
    
    public function setLastModifed(\DateTime $lastModified) {
        $this->lastModified = $lastModified->getTimestamp();
    }
    
}
