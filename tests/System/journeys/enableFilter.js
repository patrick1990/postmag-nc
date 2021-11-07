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

class EnableFilter extends AbstractTest {

    aliases = [
        {
            id: -1,
            aliasName: "hello",
            sendTo: "hello@example.com",
            comment: "Selenium Test 1",
            enabled: true
        },
        {
            id: -1,
            aliasName: "world",
            sendTo: "world@example.com",
            comment: "Selenium Test 2",
            enabled: true
        }
    ];

    _name = "enableFilter";

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
        // Disable alias 0
        await this._driver.get(this._nextcloudUrl + "/apps/postmag?id=" + this.aliases[0]["id"].toString());
        await this._driver.wait(until.elementLocated(By.id("postmagAliasFormId")), 5000);
        await this._driver.findElement(By.id("postmagAliasFormEnabled")).click();
        const closeIconsBeforeDisable = await this._driver.findElements(By.className("icon-close"));
        await this._driver.findElement(By.id("postmagAliasFormApply")).click();
        await this._driver.wait((driver) => async function(driver, closeIconCntBeforeDisable) {
            const closeIcons = await driver.findElements(By.className("icon-close"));
            return closeIcons.length === closeIconCntBeforeDisable+1;
        }(driver, closeIconsBeforeDisable.length));
        this.aliases[0]["enabled"] = false;

        // Check elements in All-Tab
        await this.checkTab("postmagAliasFilterAll", this.aliases);
        // Check elements in Enabled tab
        await this.checkTab("postmagAliasFilterEnabled", [this.aliases[1]]);
        // Check elements in Disabled tab
        await this.checkTab("postmagAliasFilterDisabled", [this.aliases[0]]);
    }

    async checkTab(tabLinkId, expectedAliases) {
        // Go to tab
        await this._driver.findElement(By.id(tabLinkId)).click();

        // Wait for the expected number of aliases in the alias list
        await this._driver.wait((driver) => async function(driver, expectedAliasCnt){
            const aliases = await driver.findElement(By.id("postmagAliasListPlaceholder")).findElements(By.tagName("a"));
            return aliases.length === expectedAliasCnt;
        }(driver, expectedAliases.length), 5000)
            .catch(() => this.assert(
                false,
                "Alias filter " + tabLinkId + " doesn't yield the expected number of aliases (exp: " + expectedAliases.length.toString() + ")."
            ))

        // Check the aliases
        const testAliases = await this._driver.findElement(By.id("postmagAliasListPlaceholder")).findElements(By.tagName("a"));
        for (let i=0; i<testAliases.length; i++) {
            const testAlias = {
                id: Number((await testAliases[i].getAttribute("id")).split("_")[1]),
                icon: await testAliases[i].findElement(By.className("app-content-list-item-icon")).getText(),
                aliasNameWithId: await testAliases[i].findElement(By.className("app-content-list-item-line-one")).getText(),
                comment: await testAliases[i].findElement(By.className("app-content-list-item-line-two")).getText(),
                enabled: (await testAliases[i].findElements(By.className("icon-checkmark"))).length === 1
            };
            this.printAliasData("test", testAlias);

            // Find alias with same id in expected aliases
            let expAlias = undefined;
            for (let j=0; i<expectedAliases.length; j++) {
                if (testAlias["id"] === expectedAliases[j]["id"]) {
                    expAlias = expectedAliases[j];
                    break;
                }
            }
            if (expAlias === undefined)
                this.assert(false, "Tested alias " + testAlias["aliasNameWithId"] + " is not contained in the expected aliases.");
            this.printAliasData("expected", expAlias);

            // Check the alias information
            this.assert(
                testAlias["icon"] === expAlias["aliasName"][0].toUpperCase(),
                "The alias icon of alias " + testAlias["aliasNameWithId"] + " is not the letter " + expAlias["aliasName"][0].toUpperCase() + "."
            );
            this.assert(
                testAlias["aliasNameWithId"].startsWith(expAlias["aliasName"]),
                "The alias " + testAlias["aliasNameWithId"] + " doesn't start with " + expAlias["aliasName"] + "."
            );
            this.assert(
                testAlias["comment"] === expAlias["comment"],
                "The alias comment of alias " + testAlias["aliasNameWithId"] + " is not " + expAlias["comment"] + "."
            );
            this.assert(
                testAlias["enabled"] === expAlias["enabled"],
                expAlias["enabled"] ?
                    "The alias " + testAlias["aliasNameWithId"] + " should be enabled." :
                    "The alias " + testAlias["aliasNameWithId"] + " should be disabled."
            );
            this.logger("");
        }
    }

    printAliasData(name, data) {
        this.logger("Print " + name);
        for(let key of Object.keys(data)) {
            this.logger("   " + key + ": " + data[key]);
        }
    }

}

module.exports.EnableFilter = EnableFilter;
