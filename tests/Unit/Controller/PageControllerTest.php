<?php
declare (strict_types=1);

/**
 * @author Patrick Greyson
 *
 * Postmag - Postfix mail alias generator for Nextcloud
 * Copyright (C) 2021
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

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
