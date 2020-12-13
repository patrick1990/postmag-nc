<?php
namespace OCA\Postmag\Controller;

use Closure;
use OCP\AppFramework\Http\JSONResponse;
use OC\AppFramework\Http;

trait Errors {
    
    protected function handleNotFound(Closure $callback){
        try {
            return new JSONResponse($callback());
        }
        catch(\OCA\Postmag\Service\Exceptions\UnexpectedDatabaseResponseException $e) {
            $message = ['message' => $e->getMessage()];
            return new JSONResponse($message, Http::STATUS_NOT_FOUND);
        }
    }
    
}