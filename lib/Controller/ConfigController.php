<?php
namespace OCA\Postmag\Controller;

use OCP\IRequest;
use OCP\AppFramework\Controller;
use OCA\Postmag\Service\ConfigService;

class ConfigController extends Controller {
	private $userId;
	private $service;

	public function __construct($AppName, IRequest $request, ConfigService $service, $UserId){
		parent::__construct($AppName, $request);
		$this->userId = $UserId;
		$this->service = $service;
	}

	/**
	 * @param string $domain
	 */
	private function formatConf(string $domain) {
	    return array(
	        'domain' => $domain
	    );
	}
	
	/**
	 * @NoAdminRequired
	 */
	public function getConf() {
	    return $this->formatConf($this->service->getTargetDomain());
	}
	
	/**
	 * @param string $domain
	 */
	public function setConf(string $domain) {
	    $this->service->setTargetDomain($domain);
	    return $this->formatConf($domain);
	}

}
