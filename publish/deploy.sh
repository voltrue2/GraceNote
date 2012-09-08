#!/bin/sh

# Variables
user='nobu';
servers[0]=$user'@49.212.31.139:';
root_path='/var/www/admin.php-gracenote.com';
backup_root_path='/var/www/rollback';
path=$root_path;
doc_root='/var/www/htdocs/';
source='/GraceNote/';
actions='/GraceNote/actions/';
models='/GraceNote/models/';
core='/GraceNote/core/';
batch='/GraceNote/batch/';
templates='/GraceNote/templates/';
css=$doc_root'css/';
img=$doc_root'img/';
js=$doc_root'js/';
point='';
dest='';
target='';
publish_type='live';
backup='true';
# Determine publish type
if [ "$3" == "rollback" ]; then
        # Publish to live
        publish_type=$3;
fi
# Function(s)
publish(){
	timestamp=`date "+%Y-%h-%d-%H%M%S-"`;
	to=$1;
	# Confirmation Message
	for server in "${servers[@]}"; do
		if [ "$1" != "$backup_root_path" ]; then
			if [ "$backup" == "true" ]; then
				echo Will create backup : from $server$item$root_path$source to $backup_root_path$source;
			fi
			echo Will publish from $1 to $server$to;
		else
			echo Will rollback from $backup_root_path$source to $server$root_path$source;	
		fi
	done
	echo "Are you sure? : (yes/no)";
	read confirm;
	exec=false;
	if [ "$confirm" == "y" ]; then
		exec=true;
	elif [ "$confirm" == "yes" ]; then
		exec=true;
	else
		echo Publish Aborted;
	fi
	# Execution
	if [ "$exec" == "true" ]; then
		for item in "${servers[@]}"; do
			if [ "$1" != "$backup_root_path" ]; then
				if [ "$backup" == "true" ]; then
					# Create Backup Version
					if [ -d "$backup_root_path$source" ]; then
						index=`expr length $source`;
						dir=`expr substr $source 2 $index`;
						version='/'$timestamp$dir;
						mv $backup_root_path$source $backup_root_path$version;
					fi
					/usr/bin/rsync -av $item$root_path$source $backup_root_path$source;
					echo Backuping Has Completed : from $item$root_path$source to $backup_root_path$source;
				fi
				/usr/bin/rsync -av $1 $item$to;
				echo Publish Has Completed : from $1 to $item$to;
			else
				/usr/bin/rsync -av $backup_root_path$source $server$root_path$source;
				echo Rollback Has Completed : from $backup_root_path$source to $server$root_path$source;
			fi
        	done
	fi
}
# Determine where to start from
if [ "$1" == "css" ]; then
	point=$css;
	dest=$css;
	backup='false';
elif [ "$1" == "img" ]; then
	point=$img;
	dest=$img;
	backup='false';
elif [ "$1" == "js" ]; then
	point=$js;
	dest=$js;
	backup='false';
elif [ "$1" == "doc_root" ]; then
	point=$doc_root;
	dest=$doc_root;
	backup='false';
elif [ "$1" == "actions" ]; then
	point=$path$actions;
	dest=$actions;
elif [ "$1" == "models" ]; then
	point=$path$models;
	dest=$models;
elif [ "$1" == "core" ]; then
	point=$path$core;
	dest=$core;
elif [ "$1" == "templates" ]; then
	point=$path$templates;
	dest=$templates;
elif [ "$1" == "batch" ]; then
	point=$path$batch;
	dest=$batch;
elif [ "$1" == "rollback" ]; then
	point=$backup_root_path;
	dest=$source;
elif [ "$1" == "" ]; then
	point=$path$source;
	dest=$source;
elif [ "$1" == "GraceNote" ]; then 
	point=$path$source;
	dest=$source;
fi

# Chose either a directory or a file to deploy
dir=$point$2;
target=$2;
# Check to see if the directory or the file exists
if [ -d "$dir" ]; then
	publish $dir;
elif [ -f "$dir" ]; then
	publish $dir;
else
	echo Invalid Input $dir;
	echo "Parameter 1 : Anchor Directory (GraceNote, actions, models, templates, core, batch, doc_root, img, js, css, rollback)";
	echo "Parameter 2 : Target Directory/File";
fi
