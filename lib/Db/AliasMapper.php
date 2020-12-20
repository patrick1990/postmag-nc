<?php
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
    
    public function findAll(string $userId, bool $enabled = null): array {
        $qb = $this->db->getQueryBuilder();
        
        $qb->select('*')
            ->from($this->getTableName());
        
        if ($userId !== null) {
            # return aliases of selected user
            $qb->where($qb->expr()->eq('user_id', $qb->createNamedParameter($userId)));
        }
        
        if ($enabled !== null) {
            # return aliases depending on enable state
            if($userId === null) {
                $qb->where($qb->expr()->eq('enabled', $qb->createNamedParameter($enabled)));
            }
            else {
                $qb->andWhere($qb->expr()->eq('enabled', $qb->createNamedParameter($enabled)));
            }
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