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

const {AbstractTest} = require("../testFramework");
const {By, until} = require("selenium-webdriver");

class SendTestmail extends AbstractTest {

    // ready time configured in postmag
    readyTime = 5 + 2;

    // url to maildev
    maildevUrl = "http://localhost:1080";

    alias = {
        id: -1,
        aliasName: "hello",
        sendTo: "hello@example.com",
        comment: "Selenium Test 1"
    };

    _name = "sendTestmail";

    _setUp = async function() {
        await this.goToPostmag();

        const id = await this.createAlias(this.alias);
        this.alias["id"] = id;

        this.logger("Id of created alias: " + this.alias["id"].toString());
    }

    _tearDown = async function() {
        await this.deleteAlias(this.alias["id"]);
    }

    _test = async function () {
        // Get alias mail address
        const alias = await this._driver.findElement(By.id("postmagAliasFormAlias")).getText();
        this.assert(
            alias.startsWith(this.alias["aliasName"]),
            "The generated alias " + alias + " doesn't starts with the alias name " + this.alias["aliasName"] + "."
        );

        // Sleep to wait for the alias to get ready
        const sleep = s => new Promise(resolve => setTimeout(resolve, 1000*s));
        await sleep(this.readyTime);

        // Click the button to send a testmail
        this.logger("Send testmail to " + alias + "...");
        await this._driver.findElement(By.id("postmagAliasFormSendTest")).click();
        await sleep(2);

        // Go to maildev
        this.logger("Browse to maildev.");
        await this._driver.get(this.maildevUrl);
        await this._driver.wait(until.elementLocated(By.className("email-list")), AbstractTest.defaultWaitTimeout);

        // Check for mail to alias
        await this._driver.wait((driver) => async function(driver, alias){
            const mailList = await driver.findElement(By.className("email-list"));
            const mailEntries = await mailList.findElements(By.tagName("li"));

            for (let i=0; i<mailEntries.length; i++) {
                const mailTo = await mailEntries[i].findElement(By.className("title-subline")).getText();
                if(mailTo.includes(alias))
                    return true;
            }
            return false;
        }(driver, alias), 3*AbstractTest.defaultWaitTimeout)
            .catch(() => this.assert(
                false,
                "No test mail was sent to " + alias + "."
            ));
        this.logger("Got mail!");
    }

}

module.exports.SendTestmail = SendTestmail;
 