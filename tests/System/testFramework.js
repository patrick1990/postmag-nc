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

class AbstractTest {
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
     * @returns {Promise<void>} promise for driver generation.
     */
    async driverSetUp() {
        this.logger("Setup web driver.");

        let capabilities = Capabilities.firefox();

        this._driver = new Builder()
            .usingServer(this.#seleniumServerUrl)
            .withCapabilities(capabilities)
            .build();
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
     * Login to nextcloud instance.
     *
     * @returns {Promise<void>} promise for nextcloud login.
     */
    async login() {
        if (this._driver !== undefined) {
            this.logger("Browse to login page.")
            await this._driver.get(this._nextcloudUrl + "/login");

            this.logger("Type in login info.")
            await this._driver.findElement(By.id("user")).sendKeys(this.#loginUser);
            await this._driver.findElement(By.id("password")).sendKeys(this.#loginPassword, Key.RETURN);

            this.logger("Wait for page refresh after login.")
            await this._driver.wait(until.stalenessOf(this._driver.findElement(By.id("password"))), 5000)
                .then(
                    () => this._driver.wait(function (driver) {
                        return driver.executeScript('return document.readyState === "complete"');
                    }, 5000)
                );
            this.logger("Login done!")
        }
    }

    /**
     * Browse to postmag.
     *
     * @returns {Promise<void>} promise for browsing to postmag
     */
    async goToPostmag() {
        // Go to postmag
        this.logger("Browse to postmag.");
        await this._driver.get(this._nextcloudUrl + "/apps/postmag");
        await this._driver.wait(until.elementLocated(By.id("postmagNewAlias")), 5000);
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
        await this._driver.wait(until.elementLocated(By.id("postmag")), 5000);
    }

    /**
     * Run test candidate.
     *
     * @param {boolean} login (optional) login to nextcloud on true (default: true)
     * @returns {Promise<boolean>} promise for test run. returns if test failed.
     */
    async run(login = true) {
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
        await this.driverSetUp();
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
 