import $ from "jquery";
import { translate as t } from "@nextcloud/l10n";
import { generateUrl } from "@nextcloud/router";
import axios from "@nextcloud/axios";
import { showSuccess, showError } from "@nextcloud/dialogs";
import "@nextcloud/dialogs/styles/toast.scss";
import { postmagPutConfig } from "./endpoints";

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
				postmagPutConfig(domain.val(), userAliasIdLen.val(), aliasIdLen.val());
			}
		}
	)
})
