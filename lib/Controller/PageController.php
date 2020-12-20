<?php
declare(strict_types=1);

namespace OCA\Postmag\Controller;

use OCP\IRequest;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Controller;

class PageController extends Controller {
	private $userId;

	public function __construct($AppName, IRequest $request, $UserId){
		parent::__construct($AppName, $request);
		$this->userId = $UserId;
	}

	// TODO: Implement Unit- and Integration-Tests
	// TODO: Implement Frontend
	// TODO: Implement REST API
	
	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function index() {
		return new TemplateResponse('postmag', 'index');  // templates/index.php
	}

}
