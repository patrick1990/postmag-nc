<?php
declare(strict_types=1);

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
	    
		return new JSONResponse(array(
		    'email' => $email,
		    'emailSet' => ($email == '') ? 'false' : 'true',
		    'userAliasId' => $this->service->getUserAliasId($this->userId)
		));
	}

}
