import $ from "jquery";
import {translate as t} from "@nextcloud/l10n";

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
        '<button id="postmagAliasFormDelete" type="button">' + t("postmag", "Delete alias") + '</button>' +
        '<div id="postmagAliasFormDeleteSurePlaceholder"></div>' +
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

export function templateDeleteSure(enabled) {
    if (enabled)
        $("#postmagAliasFormDeleteSurePlaceholder").html("<p>" + t("postmag", "Sure?") + "</p>");
    else
        $("#postmagAliasFormDeleteSurePlaceholder").html('');
}

export function setAliasForm(alias, userInfo, config) {
    templateDeleteSure(false);

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
        $("#postmagAliasFormCreated").text(t("postmag", alias["created"]));
        $("#postmagAliasFormLastModified").text(t("postmag", alias["last_modified"]));
        $("#postmagAliasFormAliasName").prop("value", alias["alias_name"]);
        $("#postmagAliasFormSendTo").prop("value", alias["to_mail"]);
        $("#postmagAliasFormComment").prop("value", alias["comment"]);
        $("#postmagAliasFormApply").text(t("postmag", "Apply"));
    }
}

export function templateContentBase(config) {
    $("#app-content").html('<div id="app-content-wrapper">' +
        '<div class="app-content-list">' +
        '<div id="postmagNewAliasPlaceholder"></div>' +
        '<div id="postmagAliasListPlaceholder"></div>' +
        '</div>' +
        '<div class="app-content-detail" id="postmagAppContentDetail">' +
        '</div>');

    templateAliasForm(config);
}

export function contentBaseLoaded() {
    return (document.getElementById("app-content-wrapper") !== null);
}

export function templateNewAlias(enabled) {
    if (enabled)
        $("#postmagNewAliasPlaceholder").html('<a href="#" id="postmagAliasId_-1" class="app-content-list-item">' +
            '<div class="app-content-list-item-icon" style="background-color: rgb(34, 139, 34);">N</div>' +
            '<div class="app-content-list-item-line-one">New alias</div>' +
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
