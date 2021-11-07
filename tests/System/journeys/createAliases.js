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
const {until, By} = require("selenium-webdriver");

class CreateAliases extends AbstractTest {

    aliases = [
        {
            id: -1,
            aliasName: "hello",
            sendTo: "hello@example.com",
            comment: "Selenium Test 1"
        },
        {
            id: -1,
            aliasName: "world",
            sendTo: "world@example.com",
            comment: "Selenium Test 2"
        }
    ];

    _name = "createAliases";

    _setUp = async function() {
        await this.goToPostmag();

        for(let i = 0; i < this.aliases.length; i++) {
            const id = await this.createAlias(this.aliases[i]);
            this.aliases[i]["id"] = id;

            this.logger("Id of created alias: " + this.aliases[i]["id"].toString());
        }
    }

    _tearDown = async function() {
        for(let i = 0; i < this.aliases.length; i++)
            await this.deleteAlias(this.aliases[i]["id"]);
    }

    _test = async function () {
        for(let i = 0; i < this.aliases.length; i++) {
            // Click on alias
            await this._driver.findElement(By.id("postmagAliasId_" + this.aliases[i]["id"].toString())).click();
            await this.stableWaitElementTextIs(By.id("postmagAliasFormId"), this.aliases[i]["id"].toString(), "value");

            // Check data
            this.printAliasData("expected", this.aliases[i]);
            const testData = {
                id: await this._driver.findElement(By.id("postmagAliasFormId")).getAttribute("value"),
                head: await this._driver.findElement(By.id("postmagAliasFormHead")).getText(),
                alias: await this._driver.findElement(By.id("postmagAliasFormAlias")).getText(),
                enabled: await this._driver.findElement(By.id("postmagAliasFormEnabled")).getAttribute("checked"),
                created: await this._driver.findElement(By.id("postmagAliasFormCreated")).getText(),
                lastModified: await this._driver.findElement(By.id("postmagAliasFormLastModified")).getText(),
                aliasName: await this._driver.findElement(By.id("postmagAliasFormAliasName")).getAttribute("value"),
                sendTo: await this._driver.findElement(By.id("postmagAliasFormSendTo")).getAttribute("value"),
                comment: await this._driver.findElement(By.id("postmagAliasFormComment")).getAttribute("value")
            }
            this.printAliasData("test", testData);

            this.assert(testData["id"] === this.aliases[i]["id"].toString(), "Id of alias is not the expected id.");
            this.assert(testData["head"].startsWith(this.aliases[i]["aliasName"]), "alias title (head) doesnt start with alias name.");
            this.assert(testData["alias"].startsWith(this.aliases[i]["aliasName"]), "alias mail doesnt start with alias name.");
            this.assert(testData["enabled"] === "true", "alias is not enabled (which is the default state).");
            this.assert(testData["created"] !== "", "alias created timestamp should not be empty.");
            this.assert(testData["lastModified"] !== "", "alias last modified timestamp should not be empty.");
            this.assert(testData["aliasName"] === this.aliases[i]["aliasName"], "alias name is not the set value.");
            this.assert(testData["sendTo"] === this.aliases[i]["sendTo"], "send to mail is not the set value.");
            this.assert(testData["comment"] === this.aliases[i]["comment"], "comment is not the set value.");
        }
    }

    printAliasData(name, data) {
        this.logger("Print " + name);
        for(let key of Object.keys(data)) {
            this.logger("   " + key + ": " + data[key]);
        }
    }

}

module.exports.CreateAliases = CreateAliases;
