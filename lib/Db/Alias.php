<?php
declare(strict_types=1);

namespace OCA\Postmag\Db;

use OCP\AppFramework\Db\Entity;
use OCP\IDateTimeFormatter;

class Alias extends Entity {
    
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
        $this->addType('userId', 'string');
        $this->addType('aliasId', 'string');
        $this->addType('aliasName', 'string');
        $this->addType('toMail', 'string');
        $this->addType('comment', 'string');
        $this->addType('enabled', 'bool');
        $this->addType('created', 'int');
        $this->addType('lastModified', 'int');
    }
    
    public function serialize(IDateTimeFormatter $formatter): array {
        return [
            'id' => $this->id,
            'user_id' => $this->userId,
            'alias_id' => $this->aliasId,
            'alias_name' => $this->aliasName,
            'to_mail' => $this->toMail,
            'comment' => $this->comment,
            'enabled' => $this->enabled,
            'created' => $formatter->formatDateTime($this->created, 'short', 'medium'),
            'last_modified' => $formatter->formatDateTime($this->lastModified, 'short', 'medium'),
        ];
    }
    
}
