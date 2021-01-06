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
	    $user = $this->service->find($this->userId);
	    
		return new JSONResponse(array(
		    'user_id' => $user['user_id'],
		    'user_alias_id' => $user['user_alias_id'],
		    'email' => $email,
		    'email_set' => ($email == '') ? 'false' : 'true'
		));
	}

}
