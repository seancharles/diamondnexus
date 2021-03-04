#!/bin/bash -eu
# autoupdate repo to merge PRs
#
# Add pull refs to git branches you wish to pull
# fetch = +refs/pull/*/head:refs/remotes/origin/pr/*

#tf=tf/
export oauth="$GITOAUTH"
export username="$GITUSER"
export pass="$GITPASSWORD"


list_issues()
{
python -c '
# replace this with one of your oauth keys from github (no permissions needed)
oauth = "365d35478bd1f401129d3e6608410ea00bad0f3e"
import requests
issues = requests.get("https://api.github.com/search/issues", params={"q": "repo:'$1' type:pr state:open label:develop"}, headers={"Authorization": "token %s" % oauth, "Accept": "application/vnd.github.v3+json"})
issues.raise_for_status()
for issue in issues.json()["items"]:
    print(issue["number"])
' | sort -n
}

main_branch=origin/develop

extra_branches=(
    $(list_issues ForeverCompanies/magento2 | sed 's|^|origin/pr/|;')
)

expected_life=360

ulimit -c unlimited
