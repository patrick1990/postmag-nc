<?php
namespace OCA\Postmag\Service;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;

trait Errors {
    
    protected function handleDbException(\Exception $e) {
        if ($e instanceof DoesNotExistException) {
            throw new Exceptions\NotFoundException($e->getMessage());
        }
        elseif ($e instanceof MultipleObjectsReturnedException) {
            throw new Exceptions\UnexpectedDatabaseResponseException($e->getMessage());
        }
        else {
            throw $e;
        }
    }
    
}