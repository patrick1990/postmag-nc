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
const {By, Key, until} = require("selenium-webdriver");

class AdminSettings extends AbstractTest {

    static idDomain = "postmagDomain";
    static idUserAliasIdLen = "postmagUserAliasIdLen";
    static idAliasIdLen = "postmagAliasIdLen";
    static idReadyTime = "postmagReadyTime";
    static dialogShowTimeout = 120000;

    origData;

    _name = "adminSettings";

    constructor() {
        // To test admin settings, we have to login as admin
        super("admin", "admin");
    }

    _setUp = async function () {
        await this.goToAdminSettings();

        // get original settings data
        this.origData = await this.getSettingsData();
        this.printSettingsData("original", this.origData);
    }

    _tearDown = async function() {
        // reset settings
        await this.setSettingsData(this.origData);
    }

    _test = async function () {
        // define expected data
        const expData = {
            domain: "test." + this.origData["domain"],
            userAliasIdLen: this.origData["userAliasIdLen"] - 1,
            aliasIdLen: this.origData["aliasIdLen"] - 1,
            readyTime: this.origData["readyTime"] - 1
        };
        this.printSettingsData("expected", expData);

        // set expected values
        await this.setSettingsData(expData);

        // reload settings
        await this.goToAdminSettings();

        // get settings values
        const testData = await this.getSettingsData();
        this.printSettingsData("test", testData);

        // Assertion
        this.assert(testData["domain"] === expData["domain"], "The domain was not saved correctly.");
        this.assert(testData["userAliasIdLen"] === expData["userAliasIdLen"], "The user alias id len was not saved correctly.");
        this.assert(testData["aliasIdLen"] === expData["aliasIdLen"], "The alias id len was not saved correctly.");
        this.assert(testData["readyTime"] === expData["readyTime"], "The ready time was not saved correctly.");

    }

    async getSettingsData() {
        const domain = await this._driver.findElement(By.id(AdminSettings.idDomain)).getAttribute("value");
        const userAliasIdLen = await this._driver.findElement(By.id(AdminSettings.idUserAliasIdLen)).getAttribute("value");
        const aliasIdLen = await this._driver.findElement(By.id(AdminSettings.idAliasIdLen)).getAttribute("value");
        const readyTime = await this._driver.findElement(By.id(AdminSettings.idReadyTime)).getAttribute("value");

        return {
            domain: domain,
            userAliasIdLen: Number(userAliasIdLen),
            aliasIdLen: Number(aliasIdLen),
            readyTime: Number(readyTime)
        }
    }

    async setSettingsData(data) {
        await this.setSingleSetting(AdminSettings.idDomain, data["domain"]);
        await this.setSingleSetting(AdminSettings.idUserAliasIdLen, data["userAliasIdLen"]);
        await this.setSingleSetting(AdminSettings.idAliasIdLen, data["aliasIdLen"]);
        await this.setSingleSetting(AdminSettings.idReadyTime, data["readyTime"], true);
    }

    async setSingleSetting(id, data, waitAfterSet = false) {
        if(waitAfterSet) {
            // Set text field
            await this._driver.findElement(By.id(id)).clear()
                .then(() => this._driver.findElement(By.id(id)).sendKeys(data)
                    .then(() => this._driver.findElement(By.id(id)).sendKeys(Key.ENTER)));

            // Wait for sending the data to backend
            await this._driver.wait(until.elementLocated(By.className("dialogs")), AbstractTest.defaultWaitTimeout)
                .then(
                    () => this.stableWaitForStaleness(By.className("dialogs"), AdminSettings.dialogShowTimeout)
                        .catch(
                            (error) => this.logger("Warning: Dialog box didn't disappear till timeout (" + error +").")
                        )
                )
                .catch(
                    (error) => this.logger("Warning: Dialog box didn't appear till timeout (" + error + ").")
                );
        }
        else {
            // Set text field
            await this._driver.findElement(By.id(id)).clear()
                .then(() => this._driver.findElement(By.id(id)).sendKeys(data));
        }
    }

    printSettingsData(name, data) {
        this.logger("Print " + name);
        this.logger("   domain: " + data["domain"]);
        this.logger("   userAliasIdLen: " + data["userAliasIdLen"].toString());
        this.logger("   aliasIdLen: " + data["aliasIdLen"].toString());
        this.logger("   readyTime: " + data["readyTime"].toString());
    }

}

module.exports.AdminSettings = AdminSettings;
