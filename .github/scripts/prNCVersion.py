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
import requests

# Get arguments
token = sys.argv[1]
newNCVersion = sys.argv[2]
newNCLabel = sys.argv[3]
baseBranch = sys.argv[4]
headBranch = sys.argv[5]

# Some configs
prEndpoint = "https://api.github.com/repos/" + os.environ["GITHUB_REPOSITORY"] + "/pulls"
issueEndpoint = "https://api.github.com/repos/" + os.environ["GITHUB_REPOSITORY"] + "/issues"
headers = {
  "Accept": "application/vnd.github.v3+json",
  "Authorization": "Bearer " + token
}

# Create PR
prBody = """Support for new NC version.

Merge the PR to support the new version.

Close the PR to rebase it. A new PR with the current base will be created automatically on the next version check."""
createBody = {
  "title": "Support for NC version " + newNCVersion,
  "head": headBranch,
  "base": baseBranch,
  "body": prBody
}
sys.stdout.write("Create PR...\n")
response = requests.post(prEndpoint, headers=headers, json=createBody)
if response.status_code != 201:
  # PR was not created successfully
  sys.stderr.write("Got no successful response from Github API for creating the PR.\n")
  sys.exit(1)
prNumber = response.json()["number"]

# Add label to PR
modifyBody = {
  "labels": [newNCLabel]
}
sys.stdout.write("Add PR labels...\n")
response = requests.patch(issueEndpoint + "/" + str(prNumber), headers=headers, json=modifyBody)
if response.status_code != 200:
  # PR was not modified successfully
  sys.stderr.write("Got no successful response from Github API for adding labels to the PR.\n")
  sys.exit(1)

sys.stdout.write("Done!\n")
sys.exit(0)
