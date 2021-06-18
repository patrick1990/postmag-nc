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

import {generateUrl} from "@nextcloud/router";
import axios from "@nextcloud/axios";
import {showError, showSuccess} from "@nextcloud/dialogs";
import {translate as t} from "@nextcloud/l10n";

export async function postmagGetConfig() {
    const url = generateUrl('apps/postmag/config');

    let config = await axios.get(url).catch(function (error) {
        if(error.response) {
            showError(t("postmag", "Error on Postmag config load ({status}).", {status: error.response.status}));
        }
        else {
            showError(t("postmag", "Error on Postmag config load (unknown error)."));
        }
    });

    // In case of error return undefined
    if (config === undefined)
        return undefined;
    else
        return config.data;
}

export function postmagPutConfig(domain, userAliasIdLen, aliasIdLen) {
    const url = generateUrl('apps/postmag/config');
    const req = {
        domain: domain,
        userAliasIdLen: userAliasIdLen,
        aliasIdLen: aliasIdLen
    };

    axios.put(url, req)
        .then(function(response) {
            showSuccess(t("postmag", "Postmag settings changed successfully!"));
        })
        .catch(function (error) {
            if(error.response) {
                showError(t("postmag", "Error on Postmag settings change ({status}).", {status: error.response.status}));
            }
            else {
                showError(t("postmag", "Error on Postmag settings change (unknown error)."));
            }
        });
}

export async function postmagGetUserInfo() {
    const url = generateUrl('apps/postmag/userinfo');

    let userinfo = await axios.get(url).catch(function (error) {
        if(error.response) {
            showError(t("postmag", "Error on Postmag user info load ({status}).", {status: error.response.status}));
        }
        else {
            showError(t("postmag", "Error on Postmag user info load (unknown error)."));
        }
    });

    // In case of error return undefined
    if (userinfo === undefined)
        return undefined;
    else
        return userinfo.data;
}

export async function postmagGetAliases(firstResult, maxResults = 20) {
    const url = generateUrl('apps/postmag/alias' +
        '?firstResult=' + firstResult.toString()  +
        '&maxResults=' + maxResults.toString());

    let aliasList = await axios.get(url).catch(function (error) {
        if(error.response) {
            showError(t("postmag", "Error on Postmag alias load ({status}).", {status: error.response.status}));
        }
        else {
            showError(t("postmag", "Error on Postmag alias load (unknown error)."));
        }
    });

    // In case of error return an empty list.
    if (aliasList === undefined)
        return [];
    else
        return aliasList.data;
}

export async function postmagPostAlias(aliasName, toMail, comment) {
    const url = generateUrl('apps/postmag/alias');
    const req = {
        aliasName: aliasName,
        toMail: toMail,
        comment: comment
    };

    let alias = await axios.post(url, req).catch(function (error) {
            if(error.response) {
                showError(t("postmag", "Error on alias creation ({status}).", {status: error.response.status}));
            }
            else {
                showError(t("postmag", "Error on alias creation (unknown error)."));
            }
        });

    // In case of error return undefined
    if (alias === undefined)
        return undefined;
    else
        return alias.data;
}

export async function postmagGetAlias(id) {
    const url = generateUrl('apps/postmag/alias/' + id.toString());

    let alias = await axios.get(url).catch(function (error) {
            if(error.response) {
                showError(t("postmag", "Error on alias read ({status}).", {status: error.response.status}));
            }
            else {
                showError(t("postmag", "Error on alias read (unknown error)."));
            }
        });

    // In case of error return undefined
    if (alias === undefined)
        return undefined;
    else
        return alias.data;
}

export async function postmagPutAlias(id, toMail, comment, enabled) {
    const url = generateUrl('apps/postmag/alias/' + id.toString());
    const req = {
        toMail: toMail,
        comment: comment,
        enabled: enabled
    };

    let alias = await axios.put(url, req).catch(function (error) {
            if(error.response) {
                showError(t("postmag", "Error on alias update ({status}).", {status: error.response.status}));
            }
            else {
                showError(t("postmag", "Error on alias update (unknown error)."));
            }
        });

    // In case of error return undefined
    if (alias === undefined)
        return undefined;
    else
        return alias.data;
}

export async function postmagDeleteAlias(id) {
    const url = generateUrl('apps/postmag/alias/' + id.toString());

    let alias = await axios.delete(url).catch(function (error) {
            if(error.response) {
                showError(t("postmag", "Error on alias delete ({status}).", {status: error.response.status}));
            }
            else {
                showError(t("postmag", "Error on alias delete (unknown error)."));
            }
        });

    // In case of error return undefined
    if (alias === undefined)
        return undefined;
    else
        return alias.data;
}