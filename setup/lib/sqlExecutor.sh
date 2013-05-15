#!/bin/bash

# Arguments
name="[$1]";
sqlDir="/db/"$2;

# Get game root path
scriptPath=`cd $(dirname "$0"); pwd`;
root=`cd $scriptPath/../../; pwd`;
configPath="$root/configs/config.json";
description="-- Creates MySQL database from scratch --";
# Extract sql files
files=(`ls $root$sqlDir`);
# start the process
echo "== $name Script ==";
echo $name" Script path is "$scriptPath;
echo $name" Root is $root";
echo $name" Current location is "`pwd`;
echo $name" Configuration path is $configPath";
echo "--- SQL files to execute ---";
echo $name" Finding SQL files to execute in $root$sqlDir";
fileCount=0;
for f in "${files[@]}"; do
	if [[ $f =~ .*sql ]]; then
		echo $name" SQL file: "$f;
		fileCount+=1;
	fi
done
# Check the number of sql file to execute
if [ $fileCount -eq 0 ]; then
	echo $name" *** Error: No SQL files to execute. Aborting...";
	exit 1;
fi
# Parse config.json
reader=$scriptPath"/readJson.php";
parser="php $reader $configPath";
# Get DB configs
dbType=`$parser "Sql-write.type"`;
dbHost=`$parser "Sql-write.host"`;
dbPort=`$parser "Sql-write.port"`;
dbDatabase=`$parser "Sql-write.db"`;
dbUser=`$parser "Sql-write.user"`;
dbPass=`$parser "Sql-write.password"`;
# echo
echo "--- DB Configurations ---";
echo $name"db Type: $dbType";
echo $name"db Host: $dbHost";
echo $name"db Port: $dbPort";
echo $name"db Database: $dbDatabase";
echo $name"db User: $dbUser";
echo "";
# prompt
echo "Will you execute the command? : (y/n) or (yes/no)";
read confirm;
exec=false;
if [ "$confirm" == "y" ]; then
	exec=true;
elif [ "$confirm" == "yes" ]; then
	exec=true;
fi
# execute mysql files
if [ "$exec" == true ]; then
	for file in "${files[@]}"; do
		if [[ $file =~ .*sql ]]; then
			echo $name" Executing [\"$dbType\"] SQL: $file";
			sql=$root$sqlDir$file;
			if [ $dbType == "mysql" ]; then
				echo `mysql -h$dbHost -P$dbPort -u$dbUser -p$dbPass $dbDatabase < $sql`;
			elif [ $dbType == "pgsql" ]; then
				echo `psql -h $dbHost -U $dbUser -d $dbDatabase -f $sql --no-password`;
			fi
		fi
	done
else
	echo $name" Aborting...";
fi
