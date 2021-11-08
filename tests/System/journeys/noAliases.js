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

class NoAliases extends AbstractTest {

    _name = "noAliases";

    _setUp = async function() {
        await this.goToPostmag();
    }

    _tearDown = async function() {}

    _test = async function () {
        // get app content
        let noAliasMsg = await this._driver.findElement(By.id("app-content")).getText();
        let noAliasMsgExpected = "Welcome to Postmag!\n" +
            "Your postfix mail alias generator.\n" +
            "You don't have any mail aliases yet.\n" +
            "Go on by pressing the new alias button.";
        this.assert(
            noAliasMsg === noAliasMsgExpected,
            "Postmag does not show the expected message if there are no aliases."
        );
    }

}

module.exports.NoAliases = NoAliases;
