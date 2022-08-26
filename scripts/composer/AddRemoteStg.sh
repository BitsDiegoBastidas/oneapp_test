#!/bin/bash
#Author: Sebastian Calero, Diego Bastidas
#Description: This Script add remote url with token to child repositories.

if [ -f ".env" ]; then # check if .env file exists
  source .env # load env vars into script
  require=($(cat ./composer.json | jq '.require' | jq -r 'to_entries[]|"\(.key)=\"\(.value)\""' | grep bits)) # read composer.json, filter bits repos and save in require var
  CUSTOM_MODULES_PATH=$(pwd)/web/modules/custom
  for module in "${require[@]}"
  do
    requiredRepo=$(echo $module | awk -F"=" '{ print $1 }')
    moduleName=$(echo $requiredRepo| awk -F"/" '{ print $2 }')
    moduleFullPath="$CUSTOM_MODULES_PATH/$moduleName"
    if [ -d "$moduleFullPath" ]; then # if folder exists...
      cd $moduleFullPath
      git remote set-url composer https://oauth2:${GITLABTOKEN}@gitlab.tigocloud.net/oneapp/${moduleName}.git
      git remote set-url origin https://oauth2:${GITLABTOKEN}@gitlab.tigocloud.net/oneapp/${moduleName}.git
      echo $(pwd) " - remote Added"
    fi
  done
fi
