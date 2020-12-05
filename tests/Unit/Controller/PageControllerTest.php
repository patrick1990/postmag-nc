<?php

namespace OCA\Postmag\Tests\Unit\Controller;

use PHPUnit\Framework\TestCase;

use OCP\AppFramework\Http\TemplateResponse;

use OCA\Postmag\Controller\PageController;


class PageControllerTest extends TestCase {
	private $controller;
	private $userId = 'john';

	public function setUp() {
		$request = $this->getMockBuilder('OCP\IRequest')->getMock();

		$this->controller = new PageController(
			'postmag', $request, $this->userId
		);
	}

	public function testIndex() {
		$result = $this->controller->index();

		$this->assertEquals('index', $result->getTemplateName());
		$this->assertTrue($result instanceof TemplateResponse);
	}

}
