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

import $ from "jquery";
import { translate as t } from "@nextcloud/l10n";
import {showError, showSuccess} from "@nextcloud/dialogs";
import "@nextcloud/dialogs/dist/index.css";
import {
	postmagDeleteAlias,
	postmagGetAlias,
	postmagGetAliases,
	postmagGetConfig,
	postmagGetUserInfo,
	postmagPostAlias,
	postmagPutAlias, postmagPutSendTest
} from "./endpoints";
import {
	contentBaseLoaded,
	setActiveAlias,
	setAliasForm,
	templateAliasList,
	templateContentBase,
	templateDeleteForm,
	templateNewAlias,
	templateNoAliases,
	setNavigationCounters
} from "./templates";

async function getAllAliases() {
	let firstResult = 0;
	let maxResults = 30;
	let ret = await postmagGetAliases(firstResult, maxResults);

	while((ret.length % maxResults === 0) && (ret.length !== 0)) {
		firstResult = firstResult + maxResults;
		let part = await postmagGetAliases(firstResult, maxResults);
		if (part.length === 0)
			break;
		ret = ret.concat(part);
	}

	return ret;
}

$(async function() {
	// ==== INIT ====
	let aliasList = await getAllAliases();
	let userInfo = await postmagGetUserInfo();
	let config = await postmagGetConfig();
	let showEnabled = true;
	let showDisabled = true;

	setNavigationCounters(aliasList);
	if(aliasList.length === 0) {
		templateNoAliases();
	}
	else {
		templateContentBase();
		templateAliasList(aliasList, showEnabled,  showDisabled);

		// Set active alias
		const searchParams = new URLSearchParams(window.location.search);
		if(searchParams.has("id")) {
			// Postmag was queried with an id (for instance the user has used Nextcloud global search)
			setActiveAlias(parseInt(searchParams.get("id"), 10));
			for (let i in aliasList) {
				if (aliasList[i]["id"] === parseInt(searchParams.get("id"), 10)) {
					setAliasForm(aliasList[i], userInfo, config);
					break;
				}
			}
		}
		else {
			// Postmag was queried without an id (for instance the user just opened the app)
			setActiveAlias(aliasList[0]["id"]);
			setAliasForm(aliasList[0], userInfo, config);
		}
	}

	// ==== HANDLERS ====
	$("body").on("click",
		"#postmagAliasFilterAll, #postmagAliasFilterEnabled, #postmagAliasFilterDisabled",
		function(e){
			$("#postmagAliasFilterAll").removeClass("active");
			$("#postmagAliasFilterEnabled").removeClass("active");
			$("#postmagAliasFilterDisabled").removeClass("active");
			$("#" + e.target.id).addClass("active");

			if (e.target.id === "postmagAliasFilterAll") {
				showEnabled = true;
				showDisabled = true;
			}
			else if (e.target.id === "postmagAliasFilterEnabled") {
				showEnabled = true;
				showDisabled = false;
			}
			else if (e.target.id === "postmagAliasFilterDisabled") {
				showEnabled = false;
				showDisabled = true;
			}

			if (contentBaseLoaded()) {
				templateAliasList(aliasList, showEnabled,  showDisabled);
				if ($("input#postmagAliasFormId").val() !== undefined)
					setActiveAlias($("input#postmagAliasFormId").val());
				else
					setActiveAlias($("input#postmagDeleteFormId").val());
			}
		}
	);
	$("body").on("click",
		"div#postmagAliasListPlaceholder",
		async function(e) {
			let prefix = "postmagAliasId";
			let id = "";

			// Get id of clicked element
			if (e.target.id.substring(0, prefix.length) === prefix) {
				// Link was clicked
				id = e.target.id.substring(prefix.length + 1);
			}
			else {
				// A children was clicked
				id = $(e.target).parent("a")[0].id.substring(prefix.length + 1);
			}

			// Set list item
			templateNewAlias(false);
			setActiveAlias(id);
			setAliasForm(await postmagGetAlias(id), userInfo, config);
		}
	);
	$("body").on("click",
		"button#postmagNewAlias",
		async function(e){
			if(!contentBaseLoaded()) {
				templateContentBase();
			}
			templateNewAlias(true);
			setActiveAlias(-1);
			setAliasForm(undefined, userInfo, config);
		}
	);
	$("body").on("click",
		"#postmagAliasFormApply",
		async function(e) {
			let id = $("input#postmagAliasFormId");
			let enabled = $("input#postmagAliasFormEnabled");
			let aliasName = $("input#postmagAliasFormAliasName");
			let toMail = $("input#postmagAliasFormSendTo");
			let comment = $("input#postmagAliasFormComment");

			// Check validity
			if (!aliasName[0].validity.valid) {
				showError(t("postmag", "Please input a valid alias name."));
				return;
			}
			if (!toMail[0].validity.valid) {
				showError(t("postmag", "Please input a valid email address."));
				return;
			}
			if (!comment[0].validity.valid) {
				showError(t("postmag", "Please input a valid comment."));
				return;
			}

			// Everything ok. Write to database...
			let newAlias;
			if (id.val() < 0) {
				newAlias = await postmagPostAlias(aliasName.val(), toMail.val(), comment.val());
				templateNewAlias(false);
			}
			else {
				newAlias = await postmagPutAlias(id.val(), toMail.val(), comment.val(), enabled[0].checked);
			}

			aliasList = await getAllAliases();
			setNavigationCounters(aliasList);
			templateAliasList(aliasList, showEnabled,  showDisabled);
			setActiveAlias(newAlias["id"]);
			setAliasForm(newAlias, userInfo, config);
		}
	);
	$("body").on("click",
		"#postmagAliasFormDelete",
		function(e) {
			let id = $("input#postmagAliasFormId").val();
			let aliasHead = $("#postmagAliasFormHead").text();

			templateDeleteForm(id, aliasHead);
		}
	);
	$("body").on("click",
		"#postmagDeleteFormNo",
		async function(e) {
			let id = $("input#postmagDeleteFormId").val();

			setActiveAlias(id);
			setAliasForm(await postmagGetAlias(id), userInfo, config);
		}
	);
	$("body").on("click",
		"#postmagDeleteFormYes",
		async function(e) {
			let id = $("input#postmagDeleteFormId").val();

			// Delete alias
			await postmagDeleteAlias(id);

			// Reload alias list
			aliasList = await getAllAliases();
			setNavigationCounters(aliasList);

			if(aliasList.length === 0) {
				templateNoAliases();
			}
			else {
				templateAliasList(aliasList, showEnabled,  showDisabled);
				setActiveAlias(aliasList[0]["id"]);
				setAliasForm(aliasList[0], userInfo, config);
			}
		}
	);
	$("body").on("click",
		"#postmagAliasFormCopy",
		function(e) {
			navigator.clipboard.writeText($("#postmagAliasFormAlias").text());
			showSuccess(t("postmag", "Copied alias to clipboard!"));
		}
	);
	$("body").on("click",
		"#postmagAliasFormSendTest",
		function(e) {
			let id = $("input#postmagAliasFormId").val();
			postmagPutSendTest(id);
		}
	);
})
