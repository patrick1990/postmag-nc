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

class TestExecutor {
    /**
     * @property {string} name name of the test
     */
    #name;

    /**
     * @property {function(!WebDriver, function(string))} test test candidate
     */
    #test;

    /**
     * @property {WebDriver} driver selenium web driver
     */
    #driver;

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
    #nextcloudUrl;

    /**
     * @property {string} seleniumServerUrl url to selenium server
     */
    #seleniumServerUrl;

    /**
     * Constructor for Test executor.
     *
     * @param {string} name name of the test
     * @param {function (!WebDriver, function(string))} test test candidate (first argument takes webdriver, second argument takes logger function)
     * @param {string} loginUser (optional) user for login to nextcloud (default: admin)
     * @param {string} loginPassword (optional) password for login to nextcloud (default: admin)
     * @param {string} nextcloudUrl (optional) url to nextcloud (default: http://localhost:8080/index.php)
     * @param {string} seleniumServerUrl (optional) url to selenium server (default: webdriver-manager - http://localhost:4444/wd/hub)
     */
    constructor(
        name,
        test,
        loginUser = "admin",
        loginPassword = "admin",
        nextcloudUrl = "http://localhost:8080/index.php",
        seleniumServerUrl = "http://localhost:4444/wd/hub"
    ) {
        this.#name = name;
        this.#test = test;
        this.#loginUser = loginUser;
        this.#loginPassword = loginPassword;
        this.#nextcloudUrl = nextcloudUrl;
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

        this.#driver = new Builder()
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

        if (this.#driver !== undefined)
            await this.#driver.quit();
    }

    /**
     * Login to nextcloud instance.
     *
     * @returns {Promise<void>} promise for nextcloud login.
     */
    async login() {
        if (this.#driver !== undefined) {
            this.logger("Browse to login page.")
            await this.#driver.get(this.#nextcloudUrl + "/login");

            this.logger("Type in login info.")
            await this.#driver.findElement(By.id("user")).sendKeys(this.#loginUser);
            await this.#driver.findElement(By.id("password")).sendKeys(this.#loginPassword, Key.RETURN);

            this.logger("Wait for page refresh after login.")
            await this.#driver.wait(until.stalenessOf(this.#driver.findElement(By.id("password"))), 5000)
                .then(
                    () => this.#driver.wait(function (driver) {
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
        this.loggerHead();

        await this.setUp();
        if (login)
            await this.login();
        await this.#test(this.#driver, this.logger);
        await this.tearDown();

        this.loggerFooter();
    }

    loggerHead() {
        console.log(" " + (new Array(this.#name.length + 14).join("=")));
        console.log(" = Run test " + this.#name + " = ");
        console.log(" " + (new Array(this.#name.length + 14).join("=")));
    }

    loggerFooter() {
        console.log();
    }

    logger(message) {
        console.log(" = " + message);
    }
}

module.exports.TestExecutor = TestExecutor;
 