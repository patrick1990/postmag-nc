import $ from "jquery";
import { translate as t } from "@nextcloud/l10n";
import { generateUrl } from "@nextcloud/router";
import axios from "@nextcloud/axios";
import { showSuccess, showError } from "@nextcloud/dialogs";
import "@nextcloud/dialogs/styles/toast.scss";

function setPostmagSettings(domain, userAliasIdLen, aliasIdLen) {
	let url = generateUrl('apps/postmag/config');
	let req = {
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

$(function() {
	$("body").on("change",
		"input#postmagDomain, input#postmagUserAliasIdLen, input#postmagAliasIdLen",
		function(e){
			const domain = $("input#postmagDomain");
			const userAliasIdLen = $("input#postmagUserAliasIdLen");
			const aliasIdLen = $("input#postmagAliasIdLen");

			if(
				domain[0].validity.valid &&
				userAliasIdLen[0].validity.valid &&
				aliasIdLen[0].validity.valid
			){
				setPostmagSettings(domain.val(), userAliasIdLen.val(), aliasIdLen.val());
			}
		}
	)
})
