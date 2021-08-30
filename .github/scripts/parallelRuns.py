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
import time

# Get arguments
token = sys.argv[1]

# Some configs
workflowEndpoint = "https://api.github.com/repos/" + os.environ["GITHUB_REPOSITORY"] + "/actions/workflows/" + os.environ["GITHUB_WORKFLOW"] +".yml/runs"
headers = {
  "Accept": "application/vnd.github.v3+json",
  "Authorization": "Bearer " + token
}

# Sleep a little bit to wait for parallel runs to start
sys.stderr.write("Wait 30s for other potential runs...\n")
time.sleep(30)

# Search for parallel runs
for status in ["queued", "in_progress"]:
  page = 0
  workflows = [None]
  while len(workflows) != 0:
    page = page+1
    response = requests.get(workflowEndpoint + "?status=" + status + "&page=" + str(page), headers=headers)
    if response.status_code != 200:
      # No successful response --> Error
      sys.stderr.write("Got no successful response from Github API to list workflows.\n")
      sys.exit(1)

    workflows = response.json()["workflow_runs"]
    sys.stderr.write(" - Me    #" + str(os.environ["GITHUB_RUN_NUMBER"]) + ": SHA " + str(os.environ["GITHUB_SHA"]) + "\n")
    for workflow in workflows:
      sys.stderr.write(" - Other #" + str(workflow["run_number"]) + ": SHA " + str(workflow["head_sha"]) + "\n")
      if str(workflow["head_sha"]) == str(os.environ["GITHUB_SHA"]):
        # Parallel run found. Stop me if my run number is higher.
        if int(workflow["run_number"]) < int(os.environ["GITHUB_RUN_NUMBER"]):
          sys.stderr.write("Parallel run with smaller run number found. Stop me!\n")
          sys.stdout.write("True")
          sys.exit(0)

sys.stderr.write("No parallel runs found (Or I'm the first run).\n")
sys.stdout.write("False")
sys.exit(0)
