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

namespace OCA\Postmag\Tests\Unit\Search;

use OCA\Postmag\AppInfo\Application;
use OCA\Postmag\Search\Provider;
use OCA\Postmag\Service\AliasService;
use OCP\IL10N;
use OCP\IURLGenerator;
use OCP\IUser;
use OCP\Search\ISearchQuery;
use PHPUnit\Framework\TestCase;

class ProviderTest extends TestCase {

    private $provider;
    private $l;
    private $urlGenerator;
    private $aliasService;

    private $aliases;

    public function setUp(): void {
        $this->l = $this->createMock(IL10N::class);
        $this->urlGenerator = $this->createMock(IURLGenerator::class);
        $this->aliasService = $this->createMock(AliasService::class);

        $this->provider = new Provider(
            $this->l,
            $this->urlGenerator,
            $this->aliasService
        );

        $this->aliases = [[], []];

        $this->aliases[0]['id'] = 234;
        $this->aliases[0]['user_id'] = 'john';
        $this->aliases[0]['alias_id'] = '1a2b';
        $this->aliases[0]['alias_name'] = 'alias';
        $this->aliases[0]['to_mail'] = 'john@doe.com';
        $this->aliases[0]['comment'] = 'My Alias';
        $this->aliases[0]['enabled'] = true;
        $this->aliases[0]['created'] = '2020-01-01 12:34:56';
        $this->aliases[0]['last_modified'] = '2020-02-02 12:34:56';
        $this->aliases[0]['created_utc'] = 1577878496;
        $this->aliases[0]['last_modified_utc'] = 1580643296;

        $this->aliases[1]['id'] = 236;
        $this->aliases[1]['user_id'] = 'john';
        $this->aliases[1]['alias_id'] = '2b3c';
        $this->aliases[1]['alias_name'] = 'important';
        $this->aliases[1]['to_mail'] = 'john@doe.com';
        $this->aliases[1]['comment'] = 'Important alias';
        $this->aliases[1]['enabled'] = true;
        $this->aliases[1]['created'] = '2020-05-12 12:34:56';
        $this->aliases[1]['last_modified'] = '2020-08-10 12:34:56';
        $this->aliases[1]['created_utc'] = 1589279696;
        $this->aliases[1]['last_modified_utc'] = 1597055696;
    }

    public function testGetId(): void {
        $this->assertSame(Application::APP_ID, $this->provider->getId(), 'id should be the app id');
    }

    public function testGetName(): void {
        // Mocking
        $name = "Postmag";

        $this->l->expects($this->once())
            ->method('t')
            ->with($name)
            ->willReturn($name);

        // Test method
        $ret = $this->provider->getName();

        $this->assertSame($name, $ret, 'name should be '.$name);
    }

    public function testGetOrder(): void {
        $this->assertSame(
            -1,
            $this->provider->getOrder(Application::APP_ID . ".page.index", []),
            'order should be -1 if postmag is active'
        );

        $this->assertGreaterThan(
            50,
            $this->provider->getOrder("other.page.index", []),
            'order should be greater than 50 if postmag is not active'
        );
    }

    private function searchPage(int $page): void {
        // Mocking
        $term = 'alias';
        $uid = 'john';
        $limit = 1;
        $image = 'image-path';
        $name = 'Postmag';

        $user = $this->createMock(IUser::class);
        $user->expects($this->once())
            ->method('getUID')
            ->willReturn($uid);

        $query = $this->createMock(ISearchQuery::class);
        $query->expects($this->once())
            ->method('getCursor')
            ->willReturn(($page <= 0) ? null : $page);
        $query->expects($this->once())
            ->method('getLimit')
            ->willReturn($limit);
        $query->expects($this->once())
            ->method('getTerm')
            ->willReturn($term);

        $this->aliasService->expects($this->once())
            ->method('search')
            ->with($term, $uid)
            ->willReturn($this->aliases);

        $this->urlGenerator->expects($this->any())
            ->method('imagePath')
            ->with(Application::APP_ID, 'app-dark.svg')
            ->willReturn($image);
        $this->urlGenerator->expects($this->any())
            ->method('linkToRoute')
            ->willReturnCallback(function ($route, $arguments) {
                return strval($arguments["id"]);
            });

        $this->l->expects($this->once())
            ->method('t')
            ->with($name)
            ->willReturn($name);

        // Test method
        $ret = $this->provider->search($user, $query)->jsonSerialize();

        $this->assertSame($name, $ret['name'], 'name of search result wrong');
        $this->assertTrue($ret['isPaginated'], 'search result not paginated');
        $this->assertSame($limit, count($ret['entries']), 'wrong number of search results');
        $this->assertSame(
            $image,
            $ret['entries'][0]->jsonSerialize()['thumbnailUrl'],
            'wrong thumbnail url result'
        );
        $this->assertSame(
            $this->aliases[$page]['alias_name'] . '.' . $this->aliases[$page]['alias_id'],
            $ret['entries'][0]->jsonSerialize()['title'],
            'wrong title result'
        );
        $this->assertSame(
            $this->aliases[$page]['comment'],
            $ret['entries'][0]->jsonSerialize()['subline'],
            'wrong subline result'
        );
        $this->assertSame(
            strval($this->aliases[$page]['id']),
            $ret['entries'][0]->jsonSerialize()['resourceUrl'],
            'wrong resource url result'
        );
        $this->assertSame($limit + $page, $ret['cursor'], 'cursor of search result wrong');
    }

    public function testSearchPage0(): void {
        $this->searchPage(0);
    }

    public function testSearchPage1(): void {
        $this->searchPage(1);
    }

}