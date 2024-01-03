#!/bin/bash

if [ ! -f "./box.phar" ]; then
    cp ../box/box.phar ./box.phar
    echo 'box.phar copied'
else
    echo 'box.phar exists'
fi

./box.phar compile --no-parallel

#

# set -e


# if [[ ${GITHUB_REPOSITORY} != 'swew-app/test'  &&  -z ${PHAR_REPO_SLUG} ]]; then
#     echo 'Not attempting phar deployment, as this is not swew-app/test, and $PHAR_REPO_SLUG is unset or empty'
#     exit 0;
# fi;

# PHAR_REPO_SLUG=${PHAR_REPO_SLUG:=swew-app/test.phar}

# git clone https://${PHAR_REPO_TOKEN}@github.com/${PHAR_REPO_SLUG}.git phar > /dev/null 2>&1

# set -x # don't do set x above this point to protect the GITHUB_TOKEN

# cd phar
# rm -rf *
# cp ../build/psalm.phar ../assets/psalm-phar/* .
# cp ../build/psalm.phar.asc . || true # not all users have GPG keys
# mv dot-gitignore .gitignore
# git config user.email "github@muglug.com"
# git config user.name "Automated commit"
# git add --all .
# git commit -m "Updated Psalm phar to commit ${GITHUB_SHA}"

# tag=${GITHUB_REF/refs\/heads\//}
# tag=${tag/refs\/tags\//}

# if [[ "$tag" != 'master' ]] ; then
#     git tag "$tag"
# fi

# # this script runs on:
# #  1. pushes to master
# #  2. publishing releases
# #
# # So we push master to psalm/phar:master
# # and tags to psalm/phar:$tag

# git push origin "$tag"

# #
