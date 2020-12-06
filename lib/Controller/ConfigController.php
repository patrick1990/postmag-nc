<?php
namespace OCA\Postmag\Controller;

use OCP\IRequest;
use OCP\AppFramework\Controller;

class ConfigController extends Controller {
	private $userId;

	public function __construct($AppName, IRequest $request, $UserId){
		parent::__construct($AppName, $request);
		$this->userId = $UserId;
	}

	/**
	 * TODO: save and load domain via endpoint (domain should not be baked into code, should it :P?)
	 * 
	 * @NoAdminRequired
	 */
	public function getconf() {
		return array(
		    "domain" => 'example.com'
		);
	}
	
	/**
	 *
	 */
	public function setconf() {
	    // empty for now
	}

}
