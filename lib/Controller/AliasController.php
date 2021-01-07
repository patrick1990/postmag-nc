<?php
declare(strict_types=1);

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
	 */
	public function index() {
		return new JSONResponse($this->service->findAll($this->userId));
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
