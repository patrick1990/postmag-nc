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

import sys
import json

# Get arguments
semanticLabelPath = sys.argv[1]
changelogPath = sys.argv[2]
if len(sys.argv)>3:
  version = sys.argv[3]
else:
  version = None

# read semantic labels
with open(semanticLabelPath, 'r') as semanticLabelFile:
  semanticLabels = json.load(semanticLabelFile)

# print version changelog function
def printVersionChangelog(log: dict, version: str):
  sys.stdout.write("## Version " + version + "\n\n")
  for label in (semanticLabels["major"] + semanticLabels["minor"] + semanticLabels["patch"] + ["other"]):
    if label in log[version]:
      sys.stdout.write("### " + label + "\n\n")
      for change in log[version][label]:
        sys.stdout.write("* " + change + "\n")
      sys.stdout.write("\n")

# read changelog json
with open(changelogPath, 'r') as changelogFile:
  changelog = json.load(changelogFile)

sys.stdout.write("# CHANGELOG\n\n")
if version is not None:
  printVersionChangelog(changelog, version)
else:
  for ver in list(changelog.keys())[::-1]:
    printVersionChangelog(changelog, ver)
    
