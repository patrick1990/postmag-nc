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

const {AdminSettings} = require("./journeys/adminSettings")
const {NoAliases} = require("./journeys/noAliases");
const {CreateAliases} = require("./journeys/createAliases");
const {ReadyTime} = require("./journeys/readyTime");

async function runTests() {
    let testFail = false;

    testFail = await new AdminSettings().run() || testFail;
    testFail = await new NoAliases().run() || testFail;
    testFail = await new CreateAliases().run() || testFail;
    testFail = await new ReadyTime().run() || testFail;

    if (testFail)
        process.exit(1);
    else
        process.exit();
}

runTests();
 