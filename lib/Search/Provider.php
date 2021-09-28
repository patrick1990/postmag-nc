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
use OCA\Postmag\Service\AliasService;
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
    private AliasService $aliasService;

    public function __construct(IL10N $l,
                                IURLGenerator $urlGenerator,
                                AliasService $aliasService)
    {
        $this->l = $l;
        $this->urlGenerator = $urlGenerator;
        $this->aliasService = $aliasService;
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
        $offset = ($query->getCursor() ?? 0);
        $limit = $query->getLimit();

        $results = array_slice($this->aliasService->search($query->getTerm(), $user->getUID()), $offset, $limit);

        return SearchResult::paginated(
            $this->getName(),
            array_map(
                function (array $res) {
                    return new SearchResultEntry(
                        $this->urlGenerator->imagePath(Application::APP_ID, "app-dark.svg"),
                        $res['alias_name'] . "." . $res['alias_id'],
                        $res['comment'],
                        $this->urlGenerator->linkToRoute(Application::APP_ID . ".page.index",
                            [
                                "id" => $res['id']
                            ])
                    );
                },
                $results
            ),
            $offset + count($results)
        );
    }
}