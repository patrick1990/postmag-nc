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
import {translate as t, translatePlural as n} from "@nextcloud/l10n";

export function templateNoAliases() {
    $("#app-content").html('<div class="section">' +
        '<h2>' + t("postmag", "Welcome to Postmag!") + '</h2>' +
        '<h3>' + t("postmag", "Your postfix mail alias generator.") + '</h3>' +
        '<p>' +
        t("postmag", "You don't have any mail aliases yet.") + '<br>' +
        t("postmag", "Go on by pressing the new alias button.") +
        '</p>' +
        '</div>');
}

function templateAliasForm(config) {
    $("#postmagAppContentDetail").html('<div class="section">' +
        '<h2 id="postmagAliasFormHead"></h2>' +
        '<hr>' +
        '<p id="postmagAliasFormAlias" class="postmag-space-above"></p>' +
        '<div class="postmag-space-above">' +
        '<button id="postmagAliasFormCopy" type="button">' + t("postmag", "Copy alias")+ '</button>' +
        '<button id="postmagAliasFormSendTest" type="button">' + t("postmag", "Send test message") + '</button>' +
        '<button id="postmagAliasFormDelete" type="button">' + t("postmag", "Delete alias") + '</button>' +
        '<input id="postmagAliasFormId" type="hidden">' +
        '</div>' +
        '<div class="postmag-container-section postmag-space-above">' +
        '<div class="postmag-label-section"><label for="postmagAliasFormEnabled">' + t("postmag", "Enabled") + '</label></div>' +
        '<div class="postmag-field-section"><input id="postmagAliasFormEnabled" type="checkbox"></div>' +
        '<div style="clear: both;"></div>' +
        '<div class="postmag-label-section">' + t("postmag", "Created") + '</div>' +
        '<div class="postmag-field-section"><div id="postmagAliasFormCreated"></div>' +
        '</div><div style="clear: both;"></div>' +
        '<div class="postmag-label-section">' + t("postmag", "Last Modified") + '</div>' +
        '<div class="postmag-field-section"><div id="postmagAliasFormLastModified"></div>' +
        '</div><div style="clear: both;"></div>' +
        '<div class="postmag-label-section">' + t("postmag", "Ready") + '</div>' +
        '<div class="postmag-field-section"><div id="postmagAliasFormReadyMessage"></div>' +
        '</div><div style="clear: both;"></div>' +
        '<div class="postmag-label-section"><label for="postmagAliasFormAliasName">' + t("postmag", "Alias name") + '</label></div>' +
        '<div class="postmag-field-section"><input id="postmagAliasFormAliasName" type="text" pattern="' + config["regexAliasName"] + '" maxlength="' + config["aliasNameLenMax"] + '">' +
        '</div><div style="clear: both;"></div>' +
        '<div class="postmag-label-section"><label for="postmagAliasFormSendTo">' + t("postmag", "Send to address") + '</label></div>' +
        '<div class="postmag-field-section"><input id="postmagAliasFormSendTo" type="text" pattern="' + config["regexEMail"] + '" maxlength="' + config["toMailLenMax"] + '">' +
        '</div><div style="clear: both;"></div>' +
        '<div class="postmag-label-section"><label for="postmagAliasFormComment">' + t("postmag", "Comment") + '</label></div>' +
        '<div class="postmag-field-section"><input id="postmagAliasFormComment" type="text" maxlength="' + config["commentLenMax"] + '"></div>' +
        '</div>' +
        '<div style="clear: both;"></div>' +
        '<div class="postmag-space-above">' +
        '<button id="postmagAliasFormApply" type="button">' + t("postmag", "Apply") + '</button>' +
        '</div>' +
        '</div>');
}

export function templateDeleteForm(id, aliasHead) {
    $("#postmagAppContentDetail").html('<div class="section">' +
        '<h2>' + t("postmag", "Delete {aliasHead}?", {aliasHead: aliasHead}) + '</h2>' +
        '<hr>' +
        '<p class="postmag-space-above">' + t("postmag", "Are you sure you want to delete this alias?") + '</p>' +
        '<p class="postmag-space-above">' + t("postmag", "Deleted aliases cannot be restored.") + '</p>' +
        '<p>' + t("postmag", "Consider disabling the alias if you don't want to receive mails via it anymore.") + '</p>' +
        '<div class="postmag-space-above">' +
        '<button id="postmagDeleteFormYes" type="button">' + t("postmag", "Yes")+ '</button>' +
        '<button id="postmagDeleteFormNo" type="button">' + t("postmag", "No") + '</button>' +
        '<input id="postmagDeleteFormId" type="hidden" value="' + id + '">' +
        '</div>' +
        '</div>');
}

export function setAliasForm(alias, userInfo, config) {
    templateAliasForm(config);

    // Set disable state of test mail button and ready message
    templateReadyMessage(alias, config);

    if (alias === undefined) {
        // New alias mode
        // Enable/Disable fields
        $("#postmagAliasFormCopy").prop("disabled", true);
        $("#postmagAliasFormDelete").prop("disabled", true);
        $("#postmagAliasFormEnabled").prop("disabled", true);
        $("#postmagAliasFormAliasName").prop("disabled", false);
        $("#postmagAliasFormSendTo").prop("disabled", false);
        $("#postmagAliasFormComment").prop("disabled", false);

        // Set values
        $("#postmagAliasFormId").prop("value", "-1");
        $("#postmagAliasFormHead").text(t("postmag", "New alias"));
        $("#postmagAliasFormAlias").text("");
        $("#postmagAliasFormEnabled").prop("checked", true);
        $("#postmagAliasFormCreated").text(t("postmag", "Now"));
        $("#postmagAliasFormLastModified").text(t("postmag", "Now"));
        $("#postmagAliasFormAliasName").prop("value", "");
        if(userInfo["email_set"] === "true")
            $("#postmagAliasFormSendTo").prop("value", userInfo["email"]);
        else
            $("#postmagAliasFormSendTo").prop("value", "");
        $("#postmagAliasFormComment").prop("value", "");
        $("#postmagAliasFormApply").text(t("postmag", "Create"));
    }
    else {
        // Edit alias mode
        $("#postmagAliasFormCopy").prop("disabled", false);
        $("#postmagAliasFormDelete").prop("disabled", false);
        $("#postmagAliasFormEnabled").prop("disabled", false);
        $("#postmagAliasFormAliasName").prop("disabled", true);
        $("#postmagAliasFormSendTo").prop("disabled", false);
        $("#postmagAliasFormComment").prop("disabled", false);

        // Set values
        $("#postmagAliasFormId").prop("value", alias["id"]);
        $("#postmagAliasFormHead").text(alias["alias_name"] + "." + alias["alias_id"]);
        if(config === undefined)
            $("#postmagAliasFormAlias").text(alias["alias_name"] + "." + alias["alias_id"] + "." + userInfo["user_alias_id"]);
        else
            $("#postmagAliasFormAlias").text(alias["alias_name"] + "." + alias["alias_id"] + "." + userInfo["user_alias_id"] + "@" + config["domain"]);
        $("#postmagAliasFormEnabled").prop("checked", alias["enabled"]);
        $("#postmagAliasFormCreated").text(alias["created"]);
        $("#postmagAliasFormLastModified").text(alias["last_modified"]);
        $("#postmagAliasFormAliasName").prop("value", alias["alias_name"]);
        $("#postmagAliasFormSendTo").prop("value", alias["to_mail"]);
        $("#postmagAliasFormComment").prop("value", alias["comment"]);
        $("#postmagAliasFormApply").text(t("postmag", "Apply"));
    }
}

function templateReadyMessage(alias, config) {
    // Clear timeout if there is one already running
    if (templateReadyMessage.timeoutId !== undefined) {
        window.clearTimeout(templateReadyMessage.timeoutId);
    }

    // New alias mode
    if(alias === undefined) {
        $("#postmagAliasFormSendTest").prop("disabled", true);

        $("#postmagAliasFormReadyMessage").text(n(
            "postmag",
            "%n second after creation",
            "%n seconds after creation",
            config['readyTime']));
        $("#postmagAliasFormReadyMessage").removeClass("postmag-ready");
        $("#postmagAliasFormReadyMessage").removeClass("postmag-notReady");
    }
    // Edit alias mode
    else {
        const timeTillReady = config["readyTime"] - parseInt(Date.now() / 1000) + alias["last_modified_utc"];
        const ready = timeTillReady <= 0;

        // Disable test mail button depending on if the alias is ready
        $("#postmagAliasFormSendTest").prop("disabled", !alias["enabled"] || !ready);

        if (ready) {
            $("#postmagAliasFormReadyMessage").text(t("postmag", "Now"));
            $("#postmagAliasFormReadyMessage").addClass("postmag-ready");
            $("#postmagAliasFormReadyMessage").removeClass("postmag-notReady");
        } else {
            $("#postmagAliasFormReadyMessage").text(n(
                "postmag",
                "In %n second",
                "In %n seconds",
                timeTillReady));
            $("#postmagAliasFormReadyMessage").removeClass("postmag-ready");
            $("#postmagAliasFormReadyMessage").addClass("postmag-notReady");

            templateReadyMessage.timeoutId = window.setTimeout(templateReadyMessage, 1000, alias, config);
        }
    }
}

export function templateContentBase() {
    $("#app-content").html('<div id="app-content-wrapper">' +
        '<div class="app-content-list">' +
        '<div id="postmagNewAliasPlaceholder"></div>' +
        '<div id="postmagAliasListPlaceholder"></div>' +
        '</div>' +
        '<div class="app-content-detail" id="postmagAppContentDetail">' +
        '</div>');
}

export function contentBaseLoaded() {
    return (document.getElementById("app-content-wrapper") !== null);
}

export function templateNewAlias(enabled) {
    if (enabled)
        $("#postmagNewAliasPlaceholder").html('<a href="#" id="postmagAliasId_-1" class="app-content-list-item">' +
            '<div class="app-content-list-item-icon" style="background-color: rgb(34, 139, 34);">N</div>' +
            '<div class="app-content-list-item-line-one">' + t("postmag", "New alias") + '</div>' +
            '</a>');
    else
        $("#postmagNewAliasPlaceholder").html('');
}

function templateExistingAlias(alias) {
    let colNum = parseInt(alias['alias_id'], 16) % 256;
    let col = 'rgb(' + colNum.toString() + ', ' + ((colNum - 100)%256).toString() + ', ' + ((colNum + 100)%256).toString() + ')';

    let ret = '<a href="#" id="postmagAliasId_' + alias['id'] + '" class="app-content-list-item">' +
        '<div class="app-content-list-item-icon" style="background-color: ' + col + ';">' + alias['alias_name'][0].toUpperCase() + '</div>' +
        '<div class="app-content-list-item-line-one">' + alias['alias_name'] + '.' + alias['alias_id'] + '</div>' +
        '<div class="app-content-list-item-line-two">' + alias['comment'] + '</div>';

    if (alias['enabled'])
        ret = ret + '<div class="icon-checkmark"></div>';
    else
        ret = ret + '<div class="icon-close"></div>';

    return ret + '</a>';
}

export function templateAliasList(aliasList, showEnabled, showDisabled) {
    let ret = '';

    for (let i in aliasList) {
        if ((showEnabled && aliasList[i]['enabled']) || (showDisabled && !aliasList[i]['enabled']))
            ret = ret + templateExistingAlias(aliasList[i]);
    }

    $("#postmagAliasListPlaceholder").html(ret);
}

export function setActiveAlias(id) {
    if (id < 0) {
        // Set new alias entry active
        $("#postmagAliasId_-1").addClass("active");
    }
    else {
        $("#postmagAliasId_-1").removeClass("active");
    }

    $("#postmagAliasListPlaceholder").children("a").each(function() {
        if($(this).attr("id") === "postmagAliasId_" + id.toString())
            $(this).addClass("active");
        else
            $(this).removeClass("active");
    });
}
