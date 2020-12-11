<?php
namespace OCA\Postmag\Db;

use OCP\AppFramework\Db\QBMapper;
use OCP\IDBConnection;

class UserMapper extends QBMapper {
    
    public function __construct(IDBConnection $db) {
        parent::__construct($db, 'postmag_user', User::class);
    }
    
    public function find(string $userId) {
        $qb = $this->db->getQueryBuilder();
        
        $qb->select('*')
            ->from($this->getTableName())
            ->where($qb->expr()->eq('user_id', $qb->createNamedParameter($userId)));
        
        return $this->findEntity($qb);
    }
    
}