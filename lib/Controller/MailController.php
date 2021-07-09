<?php
declare(strict_types=1);

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

namespace OCA\Postmag\Controller;

use OCA\Postmag\Service\MailService;
use OCP\AppFramework\Controller;
use OCP\IRequest;

class MailController extends Controller {

    use Errors;

    private $userId;
    private $service;

    public function __construct($appName, IRequest $request, MailService $service, $UserId) {
        parent::__construct($appName, $request);
        $this->userId = $UserId;
        $this->service = $service;
    }

    /**
     * @NoAdminRequired
     *
     * @param int $id
     */
    public function sendTest(int $id) {
        return $this->handleMailSendTestException(function() use ($id) {
            $this->service->sendTest($id, $this->userId);
        });
    }

}