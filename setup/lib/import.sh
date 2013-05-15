#!/bin/bash

# turn on error mode
set -e;

# paths
scriptPath=`cd $(dirname "$0"); pwd`;
rootPath=`cd $scriptPath/../; pwd`;
baseConfigPath=`ls $rootPath/configs/game/base.json`;
configPath=`ls $rootPath/configs/game/config.json`;
pageDataPath=`cd $rootPath/../www/; pwd`;
name="[IMPORT]";
safeResponse="200";
# TODO fix the hard coding part
# script description
description="--- Import mithril page data for binary build ---";

# script start messge
echo "###### Executing import script ######";
echo "$description";

# verify the paths
echo "$name Script path:      $scriptPath";
echo "$name Root path:        $rootPath";
echo "$name Base config path: $baseConfigPath";
echo "$name Config path:      $configPath";
echo "$name Page data path:   $pageDataPath";

# extract config value(s) from node.js
echo "$name Extracting configuration from $baseConfigPath";
nodeBaseScript=" \
    var  fs = require('fs'); \
    var data = fs.readFileSync('$baseConfigPath', 'utf8'); \
    var conf = JSON.parse(data); \
";
# get mithril page data import path
pageDataName=`echo "$nodeBaseScript console.log(conf.pageDataName);" | node | sed 's/> //g'`;
echo "$name Page data file name: $pageDataName";
# extract config value(s) from node.js
echo "$name Extracting configuration from $configPath ";
nodeConfigScript="\
	var fs = require('fs'); \
	var data = fs.readFileSync('$configPath', 'utf-8'); \
	var conf = JSON.parse(data); \
";
pageUrl=`echo "$nodeConfigScript console.log(conf.pageDataImportUrl);" | node | sed 's/> //g'`;
echo "$name Page data import URL: $pageUrl";

# verify the import path
echo "==================";
echo "$name Requesting mithril page data from $pageUrl";
echo "------------------";
echo "$name Import page data to: $pageDataPath/$pageDataName";
echo "==================";

# prompt for script execution
echo "$name Will you execute the command? : (y/n)";
read confirm;
if [ "$confirm" != "y" ]; then
	echo "$name Aborting...";
	exit 130;
fi

# curl mithril page data header to check if the server is running
response=`curl --output /dev/null --silent --head --write-out "%{http_code}\n" $pageUrl`;
echo "$name HTTP Response Code: $response";
# evaluate the response code and exit with error if code # other than 200 is found
if [ "$response" != "$safeResponse" ]; then
	echo "$name *** Error: Aborting...";
	exit 1;
fi

# curl mithril page data
`curl -o $pageDataPath/$pageDataName "$pageUrl"`;
# display the imported file detail
echo "$name Displaying the imported file detail:";
echo `ls -la $pageDataPath/$pageDataName`;
# done exit
exit 0;
