/**
 * @author Patrick Greyson
 *
 * Postmag - Postfix mail alias generator for Nextcloud
 * Copyright (C) 2022
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

class NavigationCounters extends AbstractTest {

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
        },
        {
            id: -1,
            aliasName: "test",
            sendTo: "test@example.com",
            comment: "Selenium Test 3",
            enabled: true
        }
    ];

    _name = "navigationCounters";

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
        await this._driver.wait(until.elementLocated(By.id("postmagAliasFormId")), AbstractTest.defaultWaitTimeout);
        await this._driver.findElement(By.id("postmagAliasFormEnabled")).click();
        const closeIconsBeforeDisable = await this._driver.findElements(By.className("icon-close"));
        await this._driver.findElement(By.id("postmagAliasFormApply")).click();
        await this._driver.wait((driver) => async function(driver, closeIconCntBeforeDisable) {
            const closeIcons = await driver.findElements(By.className("icon-close"));
            return closeIcons.length === closeIconCntBeforeDisable+1;
        }(driver, closeIconsBeforeDisable.length), 2*AbstractTest.defaultWaitTimeout);
        this.aliases[0]["enabled"] = false;

        // Check counter in All-Tab
        let cntAll = await this._driver.findElement(By.id("postmagAliasFilterAllCounter")).getText();
        this.logger("All alias count: " + cntAll);
        this.assert(
            cntAll === this.aliases.length.toString(),
            "The all alias counter has not the expected count " + this.aliases.length.toString() + "."
        );
        // Check counter in Enabled tab
        let cntEnabled = await this._driver.findElement(By.id("postmagAliasFilterEnabledCounter")).getText();
        this.logger("Enabled alias count: " + cntEnabled);
        this.assert(
            cntEnabled === (this.aliases.length - 1).toString(),
            "The enabled alias counter has not the expected count " + this.aliases.length.toString() + "."
        );
        // Check counter in Disabled tab
        let cntDisabled = await this._driver.findElement(By.id("postmagAliasFilterDisabledCounter")).getText();
        this.logger("Disabled alias count: " + cntDisabled);
        this.assert(
            cntDisabled === "1",
            "The disabled alias counter has not the expected count " + this.aliases.length.toString() + "."
        );
    }
}

module.exports.NavigationCounters = NavigationCounters;
