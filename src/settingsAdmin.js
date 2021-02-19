import { translate as t } from "@nextcloud/l10n";
import { generateUrl } from "@nextcloud/router";
import axios from "@nextcloud/axios";
import { showSuccess, showError } from "@nextcloud/dialogs";
import "@nextcloud/dialogs/styles/toast.scss"

function setPostmagSettings(domain, userAliasIdLen, aliasIdLen) {
	let url = generateUrl('apps/postmag/config');
	let req = {
		domain: domain,
		userAliasIdLen: userAliasIdLen,
		aliasIdLen: aliasIdLen
	};

	/*$.ajax({
		type: 'PUT',
		url: url,
		data: req,
		async: true
	}).done(function (response) {

	})*/
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

$(document).ready(function() {
	$("body").on("change",
		"input#postmagDomain, input#postmagUserAliasIdLen, input#postmagAliasIdLen",
		function(e){
			const domain = $("input#postmagDomain").val();
			const userAliasIdLen = $("input#postmagUserAliasIdLen").val();
			const aliasIdLen = $("input#postmagAliasIdLen").val();

			setPostmagSettings(domain, userAliasIdLen, aliasIdLen);
		}
	)
})
