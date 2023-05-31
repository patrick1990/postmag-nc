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

namespace OCA\Postmag\Tests\Integration\Search;

use InvalidArgumentException;
use OCA\Postmag\AppInfo\Application;
use OCA\Postmag\Db\Alias;
use OCA\Postmag\Service\ConfigService;
use OCA\Postmag\Share\Random;
use OCP\IImage;
use OCP\IUser;
use OCP\Search\ISearchQuery;
use OCP\UserInterface;
use Test\TestCase;

/**
 * @group DB
 */
class ProviderTest extends TestCase {

    private const DEFAULT_SEARCH_RESULT_LIMIT = 1;

    private $provider;
    private $mapper;
    private $urlGenerator;
    private $userId = 'john';

    private $aliases;

    public function setUp(): void {
        parent::setUp();
        $app = new Application();
        $container = $app->getContainer();

        $this->provider = $container->get('OCA\Postmag\Search\Provider');
        $this->mapper = $container->get('OCA\Postmag\Db\AliasMapper');
        $this->urlGenerator = $container->get('OCP\IURLGenerator');

        // Create some aliases for testing
        $this->aliases = [];
        $this->aliases[] = $this->createAlias($this->userId, "alias1", "john@doe.com", "First alias", true);
        $this->aliases[] = $this->createAlias($this->userId, "alias2", "john@example.com", "Second alias", false);
        $this->aliases[] = $this->createAlias($this->userId, "alias3", "john.doe@domain.com", "Third alias", true);
        $this->aliases[] = $this->createAlias("jane", "alias1", "jane.doe@domain.com", "First alias", true);
    }

    private function createAlias(string $userId, string $aliasName, string $toMail, string $comment, bool $enabled): Alias {
        $now = new \DateTime('now');

        $alias = new Alias();
        $alias->setUserId($userId);
        $alias->setAliasId(Random::hexString(ConfigService::DEF_ALIAS_ID_LEN));
        $alias->setAliasName($aliasName);
        $alias->setToMail($toMail);
        $alias->setComment($comment);
        $alias->setEnabled($enabled);
        $alias->setCreated($now->getTimestamp());
        $alias->setLastModified($now->getTimestamp());

        return $this->mapper->insert($alias);
    }

    public function tearDown(): void {
        foreach ($this->aliases as $alias) {
            $this->mapper->delete($alias);
        }

        parent::tearDown();
    }

    public function testSearchPage0(): void {
        $this->searchPage(0);
    }

    public function testSearchPage1(): void {
        $this->searchPage(1);
    }

    public function testSearchNoResult(): void {
        $ret = $this->provider->search(
            $this->user($this->userId),
            $this->searchQuery('other')
        )->jsonSerialize();

        $this->assertSame($this->provider->getName(), $ret['name'], 'search result holds the wrong name.');
        $this->assertTrue($ret['isPaginated'], 'search result should be paginated');
        $this->assertSame(0, count($ret['entries']), 'search should not yield any results');
        $this->assertSame(0, $ret['cursor'], 'cursor should be 0');
    }

    private function searchPage(int $page): void {
        $ret = $this->provider->search(
            $this->user($this->userId),
            $this->searchQuery('alias', self::DEFAULT_SEARCH_RESULT_LIMIT, ($page <= 0) ? null : $page)
        )->jsonSerialize();

        $this->assertSame($this->provider->getName(), $ret['name'], 'search result holds the wrong name.');
        $this->assertTrue($ret['isPaginated'], 'search result should be paginated');
        $this->assertSame(self::DEFAULT_SEARCH_RESULT_LIMIT, count($ret['entries']), 'wrong number of search results');
        $this->assertSame(
            $this->urlGenerator->imagePath(Application::APP_ID, "app-dark.svg"),
            $ret['entries'][0]->jsonSerialize()['thumbnailUrl'],
            'wrong thumbnail url result'
        );
        $this->assertSame(
            $this->aliases[$page]->getAliasName() . '.' . $this->aliases[$page]->getAliasId(),
            $ret['entries'][0]->jsonSerialize()['title'],
            'wrong title result'
        );
        $this->assertSame(
            $this->aliases[$page]->getComment(),
            $ret['entries'][0]->jsonSerialize()['subline'],
            'wrong subline result'
        );
        $this->assertSame(
            $this->urlGenerator->linkToRoute(Application::APP_ID . ".page.index",
                [
                    "id" => $this->aliases[$page]->getId()
                ]),
            $ret['entries'][0]->jsonSerialize()['resourceUrl'],
            'wrong resource url result'
        );
        $this->assertSame(self::DEFAULT_SEARCH_RESULT_LIMIT + $page, $ret['cursor'], 'cursor is not updated correctly');
    }

    private function searchQuery(
        string $term,
        int $limit = self::DEFAULT_SEARCH_RESULT_LIMIT,
        ?int $cursor = null
    ): ISearchQuery {
        return new class($term, $limit, $cursor) implements ISearchQuery {

            private $term;
            private $limit;
            private $cursor;

            public function __construct(string $term, int $limit, ?int $cursor) {
                $this->term = $term;
                $this->limit = $limit;
                $this->cursor = $cursor;
            }

            public function getTerm(): string {
                return $this->term;
            }

            public function getSortOrder(): int {
                return ISearchQuery::SORT_DATE_DESC;
            }

            public function getLimit(): int {
                return $this->limit;
            }

            public function getCursor() {
                return $this->cursor;
            }

            public function getRoute(): string { return ''; }
            public function getRouteParameters(): array { return []; }
        };
    }

    private function user(string $userId) {
        return new class($userId) implements IUser {

            private $userId;

            public function __construct(string $userId) {
                $this->userId = $userId;
            }

            public function getUID() {
                return $this->userId;
            }

            public function getDisplayName() { return ''; }
            public function setDisplayName($displayName) { return true; }
            public function getLastLogin() { return 0; }
            public function updateLastLoginTimestamp() {}
            public function delete() { return true; }
            public function setPassword($password, $recoveryPassword = null) { return true; }
            public function getHome() { return ''; }
            public function getBackendClassName() { return ''; }
            public function getBackend() {}
            public function canChangeAvatar() { return false; }
            public function canChangePassword() { return false; }
            public function canChangeDisplayName() { return false; }
            public function isEnabled() { return true; }
            public function setEnabled(bool $enabled = true) {}
            public function getEMailAddress() { return ''; }
            public function getAvatarImage($size) { return null; }
            public function getCloudId() { return ''; }
            public function setEMailAddress($mailAddress) {}
            public function getQuota() { return ''; }
            public function setQuota($quota) {}
            public function getSystemEMailAddress(): ?string { return null; }
            public function getPrimaryEMailAddress(): ?string { return null; }
            public function setSystemEMailAddress(string $mailAddress): void {}
            public function setPrimaryEMailAddress(string $mailAddress): void {}
            public function getManagerUids(): array { return []; }
            public function setManagerUids(array $uids): void {}
        };
    }

}