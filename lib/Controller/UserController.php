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
use OCA\Postmag\Service\UserService;
use OCP\AppFramework\Http\JSONResponse;

class UserController extends Controller {
	private $userId;
	private $service;

	public function __construct($AppName, IRequest $request, UserService $service, $UserId){
		parent::__construct($AppName, $request);
		$this->userId = $UserId;
		$this->service = $service;
	}

	/**
	 * @NoAdminRequired
	 */
	public function getInfo() {
	    $email = $this->service->getUserEMail($this->userId);
	    $user = $this->service->find($this->userId);
	    
		return new JSONResponse(array(
		    'user_id' => $user['user_id'],
		    'user_alias_id' => $user['user_alias_id'],
		    'email' => $email,
		    'email_set' => ($email == '') ? 'false' : 'true'
		));
	}

}
