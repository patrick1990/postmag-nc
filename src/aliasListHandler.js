import $ from "jquery";
import { translate as t } from "@nextcloud/l10n";
import {showError, showSuccess} from "@nextcloud/dialogs";
import "@nextcloud/dialogs/styles/toast.scss";
import {postmagGetAliases, postmagGetConfig, postmagGetUserInfo, postmagPostAlias, postmagPutAlias} from "./endpoints";
import {
	contentBaseLoaded, setActiveAlias, setAliasForm,
	templateAliasList,
	templateContentBase,
	templateNewAlias,
	templateNoAliases
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

	if(aliasList.length === 0) {
		templateNoAliases();
	}
	else {
		templateContentBase(config);
		templateAliasList(aliasList, showEnabled,  showDisabled);
		setActiveAlias(aliasList[0]["id"]);
		setAliasForm(aliasList[0], userInfo, config);
	}

	// ==== HANDLERS ====
	$("body").on("click",
		"button#postmagNewAlias",
		async function(e){
			if(!contentBaseLoaded()) {
				templateContentBase(config);
			}
			templateNewAlias(true);
			setActiveAlias(-1);
			setAliasForm(undefined, userInfo, config);
		}
	);
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
				setActiveAlias($("input#postmagAliasFormId").val());
			}
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
			templateAliasList(aliasList, showEnabled,  showDisabled);
			setActiveAlias(newAlias["id"]);
			setAliasForm(newAlias, userInfo, config);
		}
	);
	$("body").on("click",
		"#postmagAliasFormDelete",
		function(e) {
			return;
		}
	);
	$("body").on("click",
		"#postmagAliasFormCopy",
		function(e) {
			navigator.clipboard.writeText($("#postmagAliasFormAlias").text());
			showSuccess(t("postmag", "Copied alias to clipboard!"));
		}
	);
})
