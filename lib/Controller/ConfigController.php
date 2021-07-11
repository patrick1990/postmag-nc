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
use OCA\Postmag\Service\ConfigService;
use OCP\AppFramework\Http\JSONResponse;

class ConfigController extends Controller {
    
    use Errors;
    
	private $userId;
	private $service;

	public function __construct($AppName, IRequest $request, ConfigService $service, $UserId){
		parent::__construct($AppName, $request);
		$this->userId = $UserId;
		$this->service = $service;
	}
	
	/**
	 * @NoAdminRequired
	 */
	public function getConf() {
	    return new JSONResponse($this->service->getConf());
	}
	
	/**
	 * @param string $domain
     * @param int $userAliasIdLen
     * @param int $aliasIdLen
     * @param int $readyTime
	 */
	public function setConf(string $domain, int $userAliasIdLen, int $aliasIdLen, int $readyTime) {
	    return $this->handleConfigException(
	        function () use ($domain, $userAliasIdLen, $aliasIdLen, $readyTime) {
	            $this->service->setTargetDomain($domain);
	            $this->service->setUserAliasIdLen($userAliasIdLen);
	            $this->service->setAliasIdLen($aliasIdLen);
	            $this->service->setReadyTime($readyTime);
	        },
	        function () {
	            return $this->service->getConf();
	        });
	}

}
