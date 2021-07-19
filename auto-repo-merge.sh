#!/bin/bash -eu -x
# autoupdate and DN repo for dev

# prevent interaction
export GIT_EDITOR=:
# force commits to have predictable hashes
export GIT_AUTHOR_DATE='1970-01-01 00:00 +0000'
export GIT_COMMITTER_DATE='1970-01-01 00:00 +0000'

reset=$'\e[m'
black_fg=$'\e[30m'
red_fg=$'\e[31m'
green_fg=$'\e[32m'
yellow_fg=$'\e[33m'
blue_fg=$'\e[34m'
magenta_fg=$'\e[35m'
cyan_fg=$'\e[36m'
gray_fg=$'\e[37m'

now()
{
    date -u +'%F %T'
}

good()
{
    echo "$(now)$green_fg" "$@" "$reset" >&2
}

info()
{
    echo "$(now)$cyan_fg" "$@" "$reset" >&2
}

warning()
{
    echo "$(now)$yellow_fg" "$@" "$reset" >&2
}

error()
{
    echo "$(now)$red_fg" "$@" "$reset" >&2
}

run()
{
    info '$' "$@"
    "$@"
}

try_merge()
{
    test -n "$1"
    local branch=$1 commit
    info 'commit=$(' git rev-parse --verify -q $branch ')'
    if ! commit=$(git rev-parse --verify -q $branch)
    then
        error bogus $branch
        return
    fi
    info commit=$commit
    if run bash -c 'set -o pipefail; git branch --contains '$commit' | grep -qw master'
    then
        warning already $branch
        pr_num=`echo $branch | sed 's/origin\/pr\///g'`
        rm_conflict ForeverCompanies/magento2 $GITOAUTH $pr_num > /dev/null 2>&1
        mark_deployed ForeverCompanies/magento2 $GITOAUTH $pr_num > /dev/null 2>&1
        return
    fi
    if run git merge --ff-only $branch $commit
    then
        good fast $branch $commit
        pr_num=`echo $branch | sed 's/origin\/pr\///g'`
        rm_conflict ForeverCompanies/magento2 $GITOAUTH $pr_num > /dev/null 2>&1
        mark_deployed ForeverCompanies/magento2 $GITOAUTH $pr_num > /dev/null 2>&1
        return
    fi
    if run git merge $branch $commit
    then
        good merge $branch $commit
        pr_num=`echo $branch | sed 's/origin\/pr\///g'`
        rm_conflict ForeverCompanies/magento2 $GITOAUTH $pr_num > /dev/null 2>&1
        mark_deployed ForeverCompanies/magento2 $GITOAUTH $pr_num > /dev/null 2>&1
        return
    fi
    git merge --abort
    error abort $branch $commit
    pr_num=`echo $branch | sed 's/origin\/pr\///g'`
    mark_conflict ForeverCompanies/magento2 $GITOAUTH $pr_num > /dev/null 2>&1
    rm_deployed ForeverCompanies/magento2 $GITOAUTH $pr_num > /dev/null 2>&1
    return
}


## main script
trouble=false

while true
do
    start_time=$(date +%s)
    source ./conf.sh
    pwd
    
    run git fetch --all 
    run git reset --hard $main_branch
    info 'commit=$(' git rev-parse --verify -q $main_branch ')'
    commit=$(git rev-parse --verify -q $main_branch)
    if $trouble
    then
        error "Not merging branches - we're in trouble."
    else
        for branch in ${extra_branches[@]}
        do
            try_merge $branch
        done
    fi

    if (( end_time > start_time + expected_life ))
    then
        trouble=true
        error 'Something is wrong'
	exit
    else
        trouble=false
        warning 'Something is not wrong'
	exit
    fi
done
