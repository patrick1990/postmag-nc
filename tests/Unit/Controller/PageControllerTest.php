<?php
declare (strict_types=1);

namespace OCA\Postmag\Tests\Unit\Controller;

use PHPUnit\Framework\TestCase;

use OCP\AppFramework\Http\TemplateResponse;

use OCA\Postmag\Controller\PageController;
use OCP\AppFramework\Http;


class PageControllerTest extends TestCase {
    
	private $controller;
	private $userId = 'john';

	public function setUp(): void {
		$request = $this->getMockBuilder('OCP\IRequest')->getMock();

		$this->controller = new PageController(
			'postmag', $request, $this->userId
		);
	}

	public function testIndex(): void {
		$ret = $this->controller->index();

		$this->assertTrue($ret instanceof TemplateResponse, 'Result should be a template response.');
		$this->assertSame(Http::STATUS_OK, $ret->getStatus(), 'HTTP status should be OK.');
		$this->assertSame('index', $ret->getTemplateName(), 'Template name has to be index.');
	}

}
