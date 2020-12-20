<?php
declare(strict_types=1);

namespace OCA\Postmag\Controller;

use OCP\IRequest;
use OCP\AppFramework\Controller;
use OCA\Postmag\Service\UserService;

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
	    
		return array(
		    'email' => $email,
		    'emailSet' => ($email == '') ? 'false' : 'true',
		    'userAlias' => $this->service->getUserAliasId($this->userId)
		);
	}

}
