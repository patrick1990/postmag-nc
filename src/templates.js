import $ from "jquery";
import {translate as t} from "@nextcloud/l10n";

export function templateNoAliases() {
    return '<div class="section">' +
        '<h2>' + t("postmag", "Welcome to Postmag!") + '</h2>' +
        '<h3>' + t("postmag", "Your postfix mail alias generator.") + '</h3>' +
        '<p>' +
        t("postmag", "You don't have any mail aliases yet.") + '<br>' +
        t("postmag", "Go on by pressing the new alias button.") +
        '</p>' +
        '</div>';
}

function templateAliasForm(config) {
    return '<div class="section">' +
        '<h2 id="postmagAliasFormHead"></h2>' +
        '<hr>' +
        '<p id="postmagAliasFormAlias" class="postmag-space-above"></p>' +
        '<div class="postmag-space-above">' +
        '<button id="postmagAliasFormCopy" type="button">' + t("postmag", "Copy alias")+ '</button>' +
        '<button id="postmagAliasFormDelete" type="button">' + t("postmag", "Delete alias") + '</button>' +
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
        '</div>';
}

export function templateContentBase(config) {
    return '<div id="app-content-wrapper">' +
        '<div class="app-content-list" id="postmagAppContentList">' +
        '</div>' +
        '<div class="app-content-detail" id="postmag">' +
        templateAliasForm(config) +
        '</div>';
}

function templateNewAlias() {
    return '<a href="#" id="postmagAliasId_-1" class="app-content-list-item active">' +
        '<div class="app-content-list-item-icon" style="background-color: rgb(34, 139, 34);">N</div>' +
        '<div class="app-content-list-item-line-one">New alias</div>' +
        '</a>';
}

function templateExistingAlias(alias, activeId) {
    let ret = '';

    if (activeId === alias['id'])
        ret = ret + '<a href="#" id="postmagAliasId_' + alias['id'] + '" class="app-content-list-item active">';
    else
        ret = ret + '<a href="#" id="postmagAliasId_' + alias['id'] + '" class="app-content-list-item">';

    ret = ret + '<div class="app-content-list-item-icon" style="background-color: rgb(152, 59, 144);">' + alias['alias_name'][0].toUpperCase() + '</div>' +
        '<div class="app-content-list-item-line-one">' + alias['alias_name'] + '.' + alias['alias_id'] + '</div>' +
        '<div class="app-content-list-item-line-two">' + alias['comment'] + '</div>';

    if (alias['enabled'])
        ret = ret + '<div class="icon-checkmark"></div>';
    else
        ret = ret + '<div class="icon-close"></div>';

    return ret + '</a>';
}

export function templateAliasList(aliasList, newAlias, activeId, showEnabled, showDisabled) {
    let ret = '';

    if (newAlias)
        ret = ret + templateNewAlias();
    for (let i in aliasList) {
        if ((showEnabled && aliasList[i]['enabled']) || (showDisabled && !aliasList[i]['enabled']))
            ret = ret + templateExistingAlias(aliasList[i], activeId);
    }

    return ret;
}
