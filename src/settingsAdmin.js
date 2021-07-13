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
import "@nextcloud/dialogs/styles/toast.scss";
import { postmagPutConfig } from "./endpoints";

$(function() {
	$("body").on("change",
		"input#postmagDomain, input#postmagUserAliasIdLen, input#postmagAliasIdLen, input#postmagReadyTime",
		function(e){
			const domain = $("input#postmagDomain");
			const userAliasIdLen = $("input#postmagUserAliasIdLen");
			const aliasIdLen = $("input#postmagAliasIdLen");
			const readyTime = $("input#postmagReadyTime");

			if(
				domain[0].validity.valid &&
				userAliasIdLen[0].validity.valid &&
				aliasIdLen[0].validity.valid &&
				readyTime[0].validity.valid
			){
				postmagPutConfig(domain.val(), userAliasIdLen.val(), aliasIdLen.val(), readyTime.val());
			}
		}
	)
})
