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

namespace OCA\Postmag\Search;

use OCA\Postmag\AppInfo\Application;
use OCP\IL10N;
use OCP\IURLGenerator;
use OCP\IUser;
use OCP\Search\IProvider;
use OCP\Search\ISearchQuery;
use OCP\Search\SearchResult;
use OCP\Search\SearchResultEntry;

class Provider implements IProvider {

    private IL10N $l;
    private IURLGenerator $urlGenerator;

    public function __construct(IL10N $l, IURLGenerator $urlGenerator) {
        $this->l = $l;
        $this->urlGenerator = $urlGenerator;
    }

    public function getId(): string {
        return Application::APP_ID;
    }

    public function getName(): string {
        return $this->l->t("Postmag");
    }

    public function getOrder(string $route, array $routeParameters): int {
        if (strpos($route, Application::APP_ID . ".") === 0) {
            // Postmag is active. Prefer my results.
            return -1;
        }
        return 55;
    }

    public function search(IUser $user, ISearchQuery $query): SearchResult {
        return SearchResult::complete(
            $this->getName(),
            [
                new SearchResultEntry(
                    $this->urlGenerator->imagePath(Application::APP_ID, "app-dark.svg"),
                    "Postmag Test",
                    "Subline",
                    $this->urlGenerator->linkToRoute(Application::APP_ID . ".page.index")
                )
            ]
        );
    }
}