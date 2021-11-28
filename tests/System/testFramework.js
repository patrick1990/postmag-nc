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

const {Builder, By, Key, Capabilities, until} = require("selenium-webdriver");
const {StaleElementReferenceError} = require("selenium-webdriver/lib/error");
const {Options} = require("selenium-webdriver/firefox");

class AbstractTest {

    /**
     * @property {number} defaultWaitTimeout (static) default timeout for selenium waits
     */
    static defaultWaitTimeout = 60000;

    /**
     * @property {string} name (abstract) name of the test
     */
    _name = undefined;

    /**
     * @property {function()} setUp (abstract) setup before test
     */
    _setUp = undefined;

    /**
     * @property {function()} tearDown (abstract) teardown after test
     */
    _tearDown = undefined;

    /**
     * @property {function()} test (abstract) test candidate
     */
    _test = undefined;

    /**
     * @property {WebDriver} driver selenium web driver
     */
    _driver;

    /**
     * @property {string} loginUser user for login to nextcloud
     */
    #loginUser;

    /**
     * @property {string} loginPassword password for login to nextcloud
     */
    #loginPassword;

    /**
     * @property {string} nextcloudUrl url to nextcloud
     */
    _nextcloudUrl;

    /**
     * @property {string} seleniumServerUrl url to selenium server
     */
    #seleniumServerUrl;

    /**
     * Constructor for Test executor.
     *
     * @param {string} loginUser (optional) user for login to nextcloud (default: admin)
     * @param {string} loginPassword (optional) password for login to nextcloud (default: admin)
     * @param {string} nextcloudUrl (optional) url to nextcloud (default: http://localhost:8080/index.php)
     * @param {string} seleniumServerUrl (optional) url to selenium server (default: webdriver-manager - http://localhost:4444/wd/hub)
     */
    constructor(
        loginUser = "selenium",
        loginPassword = "selenium",
        nextcloudUrl = "http://localhost:8080/index.php",
        seleniumServerUrl = "http://localhost:4444/wd/hub"
    ) {
        if (this.constructor === AbstractTest) {
            throw new TypeError("This class cannot be instantiated.");
        }

        this.#loginUser = loginUser;
        this.#loginPassword = loginPassword;
        this._nextcloudUrl = nextcloudUrl;
        this.#seleniumServerUrl = seleniumServerUrl;
    }

    /**
     * Setup selenium web driver.
     *
     * @param {boolean} headless create headless driver
     * @returns {Promise<void>} promise for driver generation.
     */
    async driverSetUp(headless) {
        this.logger("Setup web driver.");

        let builder = new Builder()
            .usingServer(this.#seleniumServerUrl)
            .withCapabilities(Capabilities.firefox());

        this._driver = headless ?
            builder.setFirefoxOptions(new Options().headless()).build() :
            builder.build();
    }

    /**
     * Teardown selenium web driver.
     *
     * @returns {Promise<void>} promise for driver tear down.
     */
    async driverTearDown() {
        this.logger("Tear down web driver.");

        if (this._driver !== undefined)
            await this._driver.quit();
    }

    /**
     * Stable selenium wait for text is.
     *
     * Sometimes Seleniums findElement returns an old element that is deleted and newly created by my (not optimal)
     * frontend. This method tries to get the new generated elements, when the found elements are stale.
     *
     * @param {!By} by element to check for string identity
     * @param {string} str string to check
     * @param {string} attribute (optional) attribute of element to check (if undefined, text is checked)
     * @returns {Promise<void>} promise to perform the code
     */
    async stableWaitElementTextIs(by, str, attribute = undefined) {
        let checkAttribute = undefined;
        if (attribute !== undefined) {
            checkAttribute = async function (driver, by, expStr, attribute) {
                const testStr = await driver.findElement(by).getAttribute(attribute);
                return testStr === expStr;
            };
        }

        try {
            if (checkAttribute === undefined)
                await this._driver.wait(until.elementTextIs(this._driver.findElement(by), str), AbstractTest.defaultWaitTimeout);
            else
                await this._driver.wait((driver) => checkAttribute(driver, by, str, attribute), AbstractTest.defaultWaitTimeout);
        }
        catch (e) {
            if(e instanceof StaleElementReferenceError) {
                if (checkAttribute === undefined)
                    await this._driver.wait(until.elementLocated(by), AbstractTest.defaultWaitTimeout)
                        .then(() => this._driver.wait(
                            until.elementTextIs(this._driver.findElement(by), str),
                            AbstractTest.defaultWaitTimeout
                        ));
                else
                    // Maybe this is not needed? checkAttribute finds the queried element on every run.
                    await this._driver.wait(until.elementLocated(by), AbstractTest.defaultWaitTimeout)
                        .then(() => this._driver.wait(
                            (driver) => checkAttribute(driver, by, str, attribute),
                            AbstractTest.defaultWaitTimeout
                        ));
            }
            else
                throw e;
        }
    }

    /**
     * Stable selenium wait for text contains.
     *
     * Sometimes Seleniums findElement returns an old element that is deleted and newly created by my (not optimal)
     * frontend. This method tries to get the new generated elements, when the found elements are stale.
     *
     * @param {!By} by element to check for substring
     * @param {string} substr substring to check
     * @returns {Promise<void>} promise to perform the code
     */
    async stableWaitElementTextContains(by, substr) {
        try {
            await this._driver.wait(until.elementTextContains(this._driver.findElement(by), substr), AbstractTest.defaultWaitTimeout);
        }
        catch (e) {
            if(e instanceof StaleElementReferenceError) {
                await this._driver.wait(until.elementLocated(by), AbstractTest.defaultWaitTimeout)
                    .then(() => this._driver.wait(
                        until.elementTextContains(this._driver.findElement(by), substr),
                        AbstractTest.defaultWaitTimeout
                    ));
            }
            else
                throw e;
        }
    }

    /**
     * Stable selenium wait for staleness.
     *
     * This wait for staleness works via By and not via element (like the stalenessOf function of the until package).
     * Additionally it times out after two times of the default wait (hopefully this is enough).
     *
     * @param {!By} by element to check for staleness.
     * @param {number} timeout timeout for wait
     * @returns {Promise<void>} promise to perform the code
     */
    async stableWaitForStaleness(by, timeout = 2*AbstractTest.defaultWaitTimeout) {
        await this._driver.wait((driver) => async function(driver, by){
            return (await driver.findElements(by)).length === 0;
        }(driver, by), timeout);
    }

    /**
     * Login to nextcloud instance.
     *
     * @returns {Promise<void>} promise for nextcloud login.
     */
    async login() {
        if (this._driver !== undefined) {
            this.logger("Browse to login page.");
            await this._driver.get(this._nextcloudUrl + "/login");

            this.logger("Type in login info.");
            await this._driver.findElement(By.id("user")).sendKeys(this.#loginUser);
            await this._driver.findElement(By.id("password")).sendKeys(this.#loginPassword, Key.RETURN);

            this.logger("Wait for page refresh after login.");
            await this.stableWaitForStaleness(By.id("password"));
            await this._driver.wait(function (driver) {
                return driver.executeScript('return document.readyState === "complete"');
            }, 2*AbstractTest.defaultWaitTimeout);
            this.logger("Login done!");
        }
    }

    /**
     * Browse to postmag.
     *
     * @param {boolean} waitForNoAliases (optional) wait for empty alias page (default: true)
     * @returns {Promise<void>} promise for browsing to postmag
     */
    async goToPostmag(waitForNoAliases = true) {
        // Go to postmag
        this.logger("Browse to postmag.");
        await this._driver.get(this._nextcloudUrl + "/apps/postmag");
        await this._driver.wait(until.elementLocated(By.id("postmagNewAlias")), AbstractTest.defaultWaitTimeout);

        if(waitForNoAliases) {
            await this.stableWaitElementTextContains(By.id("app-content"), "You don't have any mail aliases yet.");
        }
    }

    /**
     * Browse to admin settings.
     *
     * @returns {Promise<void>} promise for browsing to admin settings
     */
    async goToAdminSettings() {
        // Go to admin settings
        this.logger("Browse to admin settings.");
        await this._driver.get(this._nextcloudUrl + "/settings/admin/additional");
        await this._driver.wait(until.elementLocated(By.id("postmag")), AbstractTest.defaultWaitTimeout);
    }

    /**
     * Creates an alias in Postmag.
     *
     * @param alias JSON containing aliasName, sendTo and comment information.
     * @returns {Promise<number>} Returns the id of the created alias.
     */
    async createAlias(alias) {
        // Push new alias button
        await this._driver.findElement(By.id("postmagNewAlias")).click();
        await this._driver.wait(until.elementLocated(By.id("postmagAliasFormId")), AbstractTest.defaultWaitTimeout);
        await this.stableWaitElementTextIs(By.id("postmagAliasFormId"), "-1", "value");

        // Type in alias information
        await this._driver.findElement(By.id("postmagAliasFormAliasName")).sendKeys(alias["aliasName"]);
        await this._driver.findElement(By.id("postmagAliasFormSendTo")).sendKeys(alias["sendTo"]);
        await this._driver.findElement(By.id("postmagAliasFormComment")).sendKeys(alias["comment"]);
        await this._driver.findElement(By.id("postmagAliasFormApply")).click();
        await this.stableWaitElementTextContains(By.id("postmagAliasFormHead"), alias["aliasName"]);

        // Return alias id
        return Number(await this._driver.findElement(By.id("postmagAliasFormId")).getAttribute("value"));
    }

    async deleteAlias(aliasId) {
        // Go to the specified id
        await this._driver.get(this._nextcloudUrl + "/apps/postmag?id=" + aliasId.toString());
        await this._driver.wait(until.elementLocated(By.id("postmagAliasFormId")), AbstractTest.defaultWaitTimeout);
        await this.stableWaitElementTextIs(By.id("postmagAliasFormId"), aliasId.toString(), "value");

        // Click delete button
        await this._driver.findElement(By.id("postmagAliasFormDelete")).click();
        await this._driver.wait(until.elementLocated(By.id("postmagDeleteFormYes")), AbstractTest.defaultWaitTimeout);

        // Click confirm button
        await this._driver.findElement(By.id("postmagDeleteFormYes")).click();
        await this.stableWaitForStaleness(By.id("postmagDeleteFormYes"));
    }

    /**
     * Run test candidate.
     *
     * @param {boolean} headless (optional) run test headless (default: true)
     * @param {boolean} login (optional) login to nextcloud on true (default: true)
     * @returns {Promise<boolean>} promise for test run. returns if test failed.
     */
    async run(headless = true, login = true) {
        // Check if name and test was implemented
        if (this._name === undefined) {
            throw new TypeError("Tests have to have a name.");
        }
        if (this._setUp === undefined) {
            throw new TypeError("Tests have to have a setup routine.")
        }
        if (this._tearDown === undefined) {
            throw new TypeError("Tests have to have a teardown routine.")
        }
        if (this._test === undefined) {
            throw new TypeError("Tests have to have an implemented test.")
        }

        let testFail = false;

        // Log Head
        this.loggerHead();

        // Driver setup and login
        await this.driverSetUp(headless);
        if (login)
            await this.login();

        // Perform test
        await this._setUp();
        try {
            await this._test();
        }
        catch (e) {
            testFail = true;
            console.error(" EEEE Assertion error: " + e.message);
        }
        await this._tearDown();

        // Driver teardown
        await this.driverTearDown();

        // Log footer
        this.loggerFooter();

        return testFail;
    }

    loggerHead() {
        console.log(" " + (new Array(this._name.length + 14).join("=")));
        console.log(" = Run test " + this._name + " = ");
        console.log(" " + (new Array(this._name.length + 14).join("=")));
    }

    loggerFooter() {
        console.log();
    }

    logger(message) {
        console.log(" = " + message);
    }

    assert(condition, message) {
        if (!condition) {
            throw new Error(message);
        }
    }
}

module.exports.AbstractTest = AbstractTest;
 