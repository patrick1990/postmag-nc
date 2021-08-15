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
from uritemplate import expand

# Get arguments
token = sys.argv[1]
version = sys.argv[2]
pkgPath = sys.argv[3]
body = sys.argv[4]

# Some configs
releasesEndpoint = "https://api.github.com/repos/" + os.environ["GITHUB_REPOSITORY"] + "/releases"
releasesHeaders = {
  "Accept": "application/vnd.github.v3+json",
  "Authorization": "Bearer " + token
}

# create release
createBody = {
  "tag_name": "v" + version,
  "name": "Release " + version,
  "body": body
}
sys.stdout.write("Create release...\n")
response = requests.post(releasesEndpoint, headers=releasesHeaders, json=createBody)
if response.status_code != 201:
  # Release was not created successfully
  sys.stderr.write("Got no successful response from Github API for creating the release.\n")
  sys.exit(1)

# Upload package to release
uploadEndpoint = expand(response.json()["upload_url"], name=pkgPath.split("/")[-1])
releasesHeaders["Content-Type"] = "application/gzip"
with open(pkgPath, 'rb') as pkgFile:
  pkgData = pkgFile.read()
sys.stdout.write("Upload release package...\n")
response = requests.post(uploadEndpoint, headers=releasesHeaders, data=pkgData)
if response.status_code != 201:
  # Package was not uploaded successfully
  sys.stderr.write("Got no successful response from Github API for uploading the release package.\n")
  sys.exit(1)

sys.stdout.write("Done!\n")
sys.exit(0)
