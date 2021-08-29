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

# Some configs
ncBranchPrefix = "stable"
prEndpoint = "https://api.github.com/repos/" + os.environ["GITHUB_REPOSITORY"] + "/pulls?state=open&base=" + baseBranch
ncBranchesEndpoint = "https://api.github.com/repos/nextcloud/server/branches"
headers = {
  "Accept": "application/vnd.github.v3+json",
  "Authorization": "Bearer " + token
}

# Search for open pull requests for nextcloud version updates
page = 0
prs = [None]
while len(prs) != 0:
  page = page+1
  response = requests.get(prEndpoint + "&page=" + str(page), headers=headers)
  if response.status_code != 200:
    # No successful response --> Error
    sys.stderr.write("Got no successful response from Github API to list PRs.\n")
    sys.exit(1)

  prs = response.json()
  for pr in prs:
    for label in pr["labels"]:
      if label["name"] == newNCLabel:
        sys.stderr.write("Open PR (#" + str(pr["number"]) + ") found with label " + newNCLabel + ". Don't open new PR.\n")
        sys.stdout.write("False")
        sys.exit(0)

sys.stderr.write("No open PR found with label " + newNCLabel + ".\n")

# Search for stable branch of new nc version
page = 0
branches = [None]
while len(branches) != 0:
  page = page+1
  response = requests.get(ncBranchesEndpoint + "?page=" + str(page), headers=headers)
  if response.status_code != 200:
    # No successful response --> Error
    sys.stderr.write("Got no successful response from Github API to list nextcloud branches.\n")
    sys.exit(1)

  branches = response.json()
  for branch in branches:
    if branch["name"] == ncBranchPrefix + newNCVersion:
      sys.stderr.write("Nextcloud branch " + branch["name"] + " found. Open new PR.\n")
      sys.stdout.write("True")
      sys.exit(0)

sys.stderr.write("No Nextcloud branch found for version " + newNCVersion + ". Don't open new PR.\n")
sys.stdout.write("False")
sys.exit(0)
