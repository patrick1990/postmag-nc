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

use OCP\AppFramework\Db\QBMapper;
use OCP\IDBConnection;
use OCP\AppFramework\Db\DoesNotExistException;

class AliasMapper extends QBMapper {
    
    public function __construct(IDBConnection $db) {
        parent::__construct($db, 'postmag_alias', Alias::class);
    }
    
    public function find(int $id, string $userId): Alias {
        $qb = $this->db->getQueryBuilder();
        
        $qb->select('*')
            ->from($this->getTableName())
            ->where($qb->expr()->eq('id', $qb->createNamedParameter($id)))
            ->andWhere($qb->expr()->eq('user_id', $qb->createNamedParameter($userId)));
        
        return $this->findEntity($qb);
    }
    
    public function findAll(?int $firstResult, ?int $maxResults, ?string $userId): array {
        $qb = $this->db->getQueryBuilder();
        
        $qb->select('*')
            ->from($this->getTableName());
        
        if ($userId !== null) {
            # return aliases of selected user
            $qb->where($qb->expr()->eq('user_id', $qb->createNamedParameter($userId)));
        }

        $qb->orderBy('alias_name')
            ->addOrderBy('alias_id')
            ->addOrderBy('user_id');

        if ($firstResult !== null && $maxResults !== null) {
            $qb->setFirstResult($firstResult)
                ->setMaxResults($maxResults);
        }

        return $this->findEntities($qb);
    }
    
    public function containsAliasId(string $aliasId, string $userId, string $aliasName): bool {
        $qb = $this->db->getQueryBuilder();
        
        $qb->select('*')
            ->from($this->getTableName())
            ->where($qb->expr()->eq('alias_id', $qb->createNamedParameter($aliasId)))
            ->andWhere($qb->expr()->eq('user_id', $qb->createNamedParameter($userId)))
            ->andWhere($qb->expr()->eq('alias_name', $qb->createNamedParameter($aliasName)));
        
        try {
            $this->findEntity($qb);
            
            return true;
        }
        catch(DoesNotExistException $e) {
            return false;
        }
    }
    
}