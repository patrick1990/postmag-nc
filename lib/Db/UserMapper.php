<?php
namespace OCA\Postmag\Db;

use OCP\AppFramework\Db\QBMapper;
use OCP\IDBConnection;
use OCP\AppFramework\Db\DoesNotExistException;

class UserMapper extends QBMapper {
    
    public function __construct(IDBConnection $db) {
        parent::__construct($db, 'postmag_user', User::class);
    }
    
    public function findUser(string $userId): User {
        $qb = $this->db->getQueryBuilder();
        
        $qb->select('*')
            ->from($this->getTableName())
            ->where($qb->expr()->eq('user_id', $qb->createNamedParameter($userId)));
        
        return $this->findEntity($qb);
    }
    
    public function containsAliasId(string $userAliasId): bool {
        $qb = $this->db->getQueryBuilder();
        
        $qb->select('*')
            ->from($this->getTableName())
            ->where($qb->expr()->eq('user_alias_id', $qb->createNamedParameter($userAliasId)));
        
        try {
            $this->findEntity($qb);
            
            return true;
        }
        catch(DoesNotExistException $e) {
            return false;
        }
    }
    
}