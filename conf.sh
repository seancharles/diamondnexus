#!/bin/bash -eu
# autoupdate repo to merge PRs
#
# Add pull refs to git branches you wish to pull
# fetch = +refs/pull/*/head:refs/remotes/origin/pr/*
# In developer-environment export the values for BRANCH & GITOAUTH

list_issues()
{
python -c '
# replace this with one of your oauth keys from github (no permissions needed)
oauth = "'$3'"
import requests
issues = requests.get("https://api.github.com/search/issues", params={"q": "repo:'$1' type:pr state:open label:'$2'"}, headers={"Authorization": "token %s" % oauth, "Accept": "application/vnd.github.v3+json"})
issues.raise_for_status()
for issue in issues.json()["items"]:
    print(issue["number"])
' | sort -n
}

main_branch=origin/$BRANCH

extra_branches=(
    $(list_issues ForeverCompanies/magento2 $BRANCH $GITOAUTH | sed 's|^|origin/pr/|;')
)

expected_life=360

ulimit -c unlimited
