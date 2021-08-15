#
# @author Patrick Greyson
#
# Postmag - Postfix mail alias generator for Nextcloud
# Copyright (C) 2021
#
# This program is free software: you can redistribute it and/or modify
# it under the terms of the GNU Affero General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU Affero General Public License for more details.
#
# You should have received a copy of the GNU Affero General Public License
# along with this program.  If not, see <http://www.gnu.org/licenses/>.

import os
import sys
import json
import requests

# Get arguments
semanticLabelPath = sys.argv[1]
prId = sys.argv[2]

# Some configs
prEndpoint = "https://api.github.com/repos/" + os.environ["GITHUB_REPOSITORY"] + "/pulls/" + prId
prHeaders = {"Accept": "application/vnd.github.v3+json"}

# read semantic labels
with open(semanticLabelPath, 'r') as semanticLabelFile:
  semanticLabels = json.load(semanticLabelFile)

# get labels of PR
response = requests.get(prEndpoint, headers=prHeaders)
if response.status_code != 200:
  # No successful response --> Error
  sys.stderr.write("Got no successful response from Github API for PR " + prId + "\n")
  sys.exit(1)

prLabels = response.json()["labels"]
for semanticLabel in semanticLabels:
  if semanticLabel in [prLabel["name"] for prLabel in prLabels]:
    # Semantic label found --> Exit successfully
    sys.stdout.write("Found label " + semanticLabel + " on PR " + prId + "\n")
    sys.exit(0)

# No semantic labels attached --> Error
sys.stderr.write("No semantic labels found on PR " + prId + "\n")
sys.exit(1)
