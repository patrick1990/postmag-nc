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

namespace OCA\Postmag\Controller;

use Closure;
use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\Http;
use OCA\Postmag\Service\Exceptions\UnexpectedDatabaseResponseException;
use OCA\Postmag\Service\Exceptions\ValueFormatException;
use OCA\Postmag\Service\Exceptions\ValueBoundException;
use OCA\Postmag\Service\Exceptions\StringLengthException;

trait Errors {
    
    protected function handleServiceException(
        array $caughtExceptions,
        Closure $retCallback,
        ?Closure $preCallback = null
        ): JSONResponse
        {
            try {
                // Run pre callback if set
                if ($preCallback !== null) {
                    $preCallback();
                }
                
                return new JSONResponse($retCallback());
            }
            catch(\Exception $e) {
                foreach ($caughtExceptions as $exception => $httpStatus) {
                    if ($e instanceof $exception) {
                        $message = ['message' => $e->getMessage()];
                        return new JSONResponse($message, $httpStatus);
                    }
                }
                
                // Throw exception if it is not handled
                throw $e;
            }
    }

    protected function handleAliasIndexException(Closure $callback): JSONResponse {
        return $this->handleServiceException(
            [
                ValueBoundException::class => Http::STATUS_BAD_REQUEST
            ],
            $callback
        );
    }

    protected function handleAliasCreateException(Closure $callback): JSONResponse {
        return $this->handleServiceException(
            [
                StringLengthException::class => Http::STATUS_BAD_REQUEST,
                ValueFormatException::class => Http::STATUS_BAD_REQUEST
            ],
            $callback
        );
    }

    protected function handleAliasReadException(Closure $callback): JSONResponse {
        return $this->handleServiceException(
            [
                UnexpectedDatabaseResponseException::class => Http::STATUS_NOT_FOUND
            ],
            $callback
        );
    }
    
    protected function handleAliasUpdateException(Closure $callback): JSONResponse {
        return $this->handleServiceException(
            [
                UnexpectedDatabaseResponseException::class => Http::STATUS_NOT_FOUND,
                StringLengthException::class => Http::STATUS_BAD_REQUEST,
                ValueFormatException::class => Http::STATUS_BAD_REQUEST
            ],
            $callback
        );
    }
    
    protected function handleAliasDeleteException(Closure $callback): JSONResponse {
        return $this->handleServiceException(
            [
                UnexpectedDatabaseResponseException::class => Http::STATUS_NOT_FOUND
            ],
            $callback
        );
    }
    
    protected function handleConfigException(Closure $setCallback, Closure $getCallback): JSONResponse {
        return $this->handleServiceException(
            [
                ValueFormatException::class => Http::STATUS_BAD_REQUEST,
                ValueBoundException::class => Http::STATUS_BAD_REQUEST
            ],
            $getCallback,
            $setCallback
        );
    }
    
}