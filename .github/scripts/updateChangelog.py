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
import re
import json
import requests

# Get arguments
semanticLabelPath = sys.argv[1]
changelogPath = sys.argv[2]
version = sys.argv[3]
commitMsg = sys.argv[4].split("\n")

# Some configs
prEndpoint = "https://api.github.com/repos/" + os.environ["GITHUB_REPOSITORY"] + "/pulls/"
prHeaders = {"Accept": "application/vnd.github.v3+json"}

# read semantic labels
with open(semanticLabelPath, 'r') as semanticLabelFile:
  semanticLabels = json.load(semanticLabelFile)

# read changelog json
with open(changelogPath, 'r') as changelogFile:
  changelog = json.load(changelogFile)

# add new commits to changelog
newLvlId = -1
newChanges = {}
for msg in commitMsg:
  commitLvl = "other"

  # Search for id of PR in commit message (squash merges)
  pr = re.findall("\(#[0-9]+\)", msg)
  if len(pr) != 0:
    pr = pr[-1][2:-1]

    # get labels of PR
    response = requests.get(prEndpoint + pr, headers=prHeaders)
    if response.status_code != 200:
      # No successful response --> Error
      sys.stderr.write("Got no successful response from Github API for PR " + pr + "\n")
      sys.exit(1)
     
    prLabels = response.json()["labels"]

    # what is the sementic level of the PR?
    for semanticLabel in semanticLabels:
      if semanticLabel in [prLabel["name"] for prLabel in prLabels]:
        commitLvl = semanticLabel
        
    if commitLvl == "other":
      # No semantic labels attached --> Error
      sys.stderr.write("No semantic labels found on PR " + pr + "\n")
      sys.exit(1)

  # Add commit message to changelog
  if commitLvl in newChanges:
    newChanges[commitLvl].append(msg)
  else:
    newChanges[commitLvl] = [msg]

  # Update version level
  commitLvlId = -1 if commitLvl not in semanticLabels else semanticLabels.index(commitLvl)
  newLvlId = max(commitLvlId, newLvlId)

# Calc new version
major, minor, bug = version.split(".")
if newLvlId <= 1:
  newVersion = major + "." + minor + "." + str(int(bug)+1)
elif newLvlId <= 2:
  newVersion = major + "." + str(int(minor)+1) + ".0"
else:
  newVersion = str(int(major)+1) + ".0.0"

# Return results
changelog[newVersion] = newChanges
with open(changelogPath, 'w') as changelogFile:
  json.dump(changelog, changelogFile, indent=2)
sys.stdout.write(newVersion)

sys.exit(0)
