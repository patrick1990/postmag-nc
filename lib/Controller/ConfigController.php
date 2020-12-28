<?php
declare(strict_types=1);

namespace OCA\Postmag\Controller;

use OCP\IRequest;
use OCP\AppFramework\Controller;
use OCA\Postmag\Service\ConfigService;
use OCP\AppFramework\Http\JSONResponse;

class ConfigController extends Controller {
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
	 */
	public function setConf(string $domain, int $userAliasIdLen, int $aliasIdLen) {
	    $this->service->setTargetDomain($domain);
	    $this->service->setUserAliasIdLen($userAliasIdLen);
	    $this->service->setAliasIdLen($aliasIdLen);
	    return new JSONResponse($this->service->getConf());
	}

}
