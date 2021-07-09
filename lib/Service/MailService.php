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

namespace OCA\Postmag\Service;

use OCP\IL10N;
use OCP\Mail\IMailer;

class MailService {

    use Errors;

    private $l;
    private $mailer;
    private $aliasService;
    private $userService;
    private $configService;

    public function __construct(IL10N $l,
                                IMailer $mailer,
                                AliasService $aliasService,
                                UserService $userService,
                                ConfigService $configService)
    {
        $this->l = $l;
        $this->mailer = $mailer;
        $this->aliasService = $aliasService;
        $this->userService = $userService;
        $this->configService = $configService;
    }

    public function sendTest(int $id, string $userId): array {
        // Get alias
        $alias = $this->aliasService->find($id, $userId);
        $toMail = $alias['alias_name']
                  ."."
                  .$alias['alias_id']
                  ."."
                  .$this->userService->getUserAliasId($userId)
                  ."@"
                  .$this->configService->getTargetDomain();

        // Write template for test message
        $template = $this->mailer->createEMailTemplate('postmag.sendtest');
        $template->setSubject($this->l->t('Postmag test message'));
        $template->addHeader();
        $template->addHeading($this->l->t('Test message'));
        $template->addBodyText($this->l->t('This is a test message for your Postmag alias %1$s.', [$toMail]));
        $template->addBodyText($this->l->t('If you have received it, your alias works as expected.'));
        $template->addBodyText($this->l->t('If you don\'t know why you have received this message, contact your administrator.'));
        $template->addFooter($this->l->t('Postmag - Your postfix mail alias generator.'));

        // Create test message
        $message = $this->mailer->createMessage();
        $message->setTo([$toMail]);
        $message->useTemplate($template);

        // Send test mail
        try {
            $errors = $this->mailer->send($message);
            if (!empty($errors)) {
                throw new Exceptions\MailRecipientException('Email could not be sent to some recipients.');
            }
        }
        catch (\Exception $e) {
            throw new Exceptions\MailException($e->getMessage());
        }

        return array('recipient' => $toMail);
    }
}