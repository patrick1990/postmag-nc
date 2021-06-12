import $ from "jquery";
import {showSuccess} from "@nextcloud/dialogs";
import "@nextcloud/dialogs/styles/toast.scss";
import {postmagGetAliases, postmagGetConfig} from "./endpoints";
import {templateAliasList, templateContentBase, templateNoAliases} from "./templates";

async function getAllAliases() {
	let firstResult = 0;
	let maxResults = 30;
	let ret = await postmagGetAliases(firstResult, maxResults);

	while(ret.length % maxResults === 0) {
		firstResult = firstResult + maxResults;
		let part = await postmagGetAliases(firstResult, maxResults);
		if (part.length === 0)
			break;
		ret = ret.concat(part);
	}

	return ret;
}

$(async function() {
	// ==== HANDLERS ====
	$("body").on("click",
		"button#postmagNewAlias",
		async function(e){
			let aliasList = await getAllAliases();
			if(aliasList.length === 0) {
				$("#app-content").html(templateContentBase(postmagGetConfig()));
			}
			$("#postmagAppContentList").html(templateAliasList(aliasList, true, -1, true, true));
		}
	);
	$("body").on("click",
		"#postmagAliasFilterAll, #postmagAliasFilterEnabled, #postmagAliasFilterDisabled",
		function(e){
			$("#postmagAliasFilterAll").removeClass("active");
			$("#postmagAliasFilterEnabled").removeClass("active");
			$("#postmagAliasFilterDisabled").removeClass("active");

			$("#" + e.target.id).addClass("active");
		}
	);

	// ==== INIT ====
	let aliasList = await getAllAliases();
	if(aliasList.length === 0) {
		$("#app-content").html(templateNoAliases());
	}
})
