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
