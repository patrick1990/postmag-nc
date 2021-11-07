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
const {By} = require("selenium-webdriver");

class ReadyTime extends AbstractTest {

    // ready time configured in postmag
    readyTime = 5 + 2;

    alias = {
        id: -1,
        aliasName: "hello",
        sendTo: "hello@example.com",
        comment: "Selenium Test 1"
    };

    _name = "readyTime";

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
        // Check readyTime
        const initReadyTime = await this._driver.findElement(By.id("postmagAliasFormReadyMessage")).getText();
        this.logger("Initial ready time: " + initReadyTime);
        this.assert(initReadyTime !== "Now", "new alias should not be ready.");

        // Sleep to wait for ready message
        const sleep = s => new Promise(resolve => setTimeout(resolve, 1000*s));
        await sleep(this.readyTime);

        // Check readyTime
        const endReadyTime = await this._driver.findElement(By.id("postmagAliasFormReadyMessage")).getText();
        this.logger("End ready time: " + endReadyTime);
        this.assert(endReadyTime === "Now", "alias should be ready after ready time.");
    }

}

module.exports.ReadyTime = ReadyTime;
