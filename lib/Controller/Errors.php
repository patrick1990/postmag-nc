<?php
declare(strict_types=1);

namespace OCA\Postmag\Controller;

use Closure;
use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\Http;
use OCA\Postmag\Service\Exceptions\UnexpectedDatabaseResponseException;
use OCA\Postmag\Service\Exceptions\ValueFormatException;
use OCA\Postmag\Service\Exceptions\ValueBoundException;

trait Errors {
    
    protected function handleNotFound(Closure $callback): JSONResponse{
        try {
            return new JSONResponse($callback());
        }
        catch(UnexpectedDatabaseResponseException $e) {
            $message = ['message' => $e->getMessage()];
            return new JSONResponse($message, Http::STATUS_NOT_FOUND);
        }
    }
    
    protected function handleConfigException(Closure $setCallback, Closure $getCallback): JSONResponse {
        try {
            $setCallback();
            return new JSONResponse($getCallback());
        }
        catch(ValueFormatException | ValueBoundException $e) {
            $message = ['message' => $e->getMessage()];
            return new JSONResponse($message, Http::STATUS_BAD_REQUEST);
        }
    }
    
}