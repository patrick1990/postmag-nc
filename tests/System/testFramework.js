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
        loginUser = "admin",
        loginPassword = "admin",
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
    async setUp() {
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
    async tearDown() {
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
     * Run test candidate.
     *
     * @param {boolean} login (optional) login to nextcloud on true (default: true)
     * @returns {Promise<void>} promise for test run.
     */
    async run(login = true) {
        // Check if name and test was implemented
        if (this._name === undefined) {
            throw new TypeError("Tests have to have a name.");
        }
        if (this._test === undefined) {
            throw new TypeError("Tests have to have an implemented test.")
        }

        this.loggerHead();

        await this.setUp();
        if (login)
            await this.login();
        await this._test();
        await this.tearDown();

        this.loggerFooter();
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
}

module.exports.AbstractTest = AbstractTest;
 