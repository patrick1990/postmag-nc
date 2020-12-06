<?php
namespace OCA\Postmag\Controller;

use OCP\IRequest;
use OCP\AppFramework\Controller;

class AliasController extends Controller {
	private $userId;

	public function __construct($AppName, IRequest $request, $UserId){
		parent::__construct($AppName, $request);
		$this->userId = $UserId;
	}

	/**
	 * @NoAdminRequired
	 */
	public function index() {
		// empty for now
	}
	
	/**
	 * @NoAdminRequired
	 */
	public function create() {
	    // empty for now
	}
	
	/**
	 * @NoAdminRequired
	 */
	public function update() {
	    // empty for now
	}
	
	/**
	 * @NoAdminRequired
	 */
	public function destroy() {
	    // empty for now
	}

}
