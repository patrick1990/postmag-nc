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

use OCP\IRequest;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JSONResponse;
use OCA\Postmag\Service\AliasService;

class AliasController extends Controller {
    
    use Errors;
    
	private $userId;
	private $service;

	public function __construct($AppName, IRequest $request, AliasService $service, $UserId){
		parent::__construct($AppName, $request);
		$this->userId = $UserId;
		$this->service = $service;
	}
	
	/**
	 * @NoAdminRequired
     *
     * @param int $firstResult
     * @param int $maxResults
	 */
	public function index(int $firstResult, int $maxResults) {
	    return $this->handleAliasIndexException(function () use ($firstResult, $maxResults) {
	        return $this->service->findAll($firstResult, $maxResults, $this->userId);
        });
	}
	
	/**
	 * @NoAdminRequired
	 * 
	 * @param string $aliasName
	 * @param string $toMail
	 * @param string $comment
	 */
	public function create(string $aliasName, string $toMail, string $comment) {
	    return $this->handleAliasCreateException(function () use ($aliasName, $toMail, $comment) {
	        return $this->service->create($aliasName, $toMail, $comment, $this->userId);
	    });
	}

    /**
     * @NoAdminRequired
     *
     * @param int $id
     */
	public function read(int $id) {
	    return $this->handleAliasReadException(function() use ($id) {
	        return $this->service->find($id, $this->userId);
        });
    }
	
	/**
	 * @NoAdminRequired
	 * 
	 * @param int $id
	 * @param string $toMail
	 * @param string $comment
	 * @param bool $enabled
	 */
	public function update(int $id, string $toMail, string $comment, bool $enabled) {
	    return $this->handleAliasUpdateException(function () use ($id, $toMail, $comment, $enabled) {
	        return $this->service->update($id, $toMail, $comment, $enabled, $this->userId);
	    });
	}
	
	/**
	 * @NoAdminRequired
	 *
	 * @param int $id
	 */
	public function delete(int $id) {
	    return $this->handleAliasDeleteException(function () use ($id) {
	        return $this->service->delete($id, $this->userId);
	    });
	}

}
