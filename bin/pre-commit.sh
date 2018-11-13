#!/bin/sh

PROJECT=`php -r "echo dirname(dirname(realpath('$0')));"`
STAGED_FILES_CMD=`git diff --cached --name-only --diff-filter=ACMR HEAD | grep \\\\.php`

# Determine if a file list is passed
if [ "$#" -eq 1 ]
then
	oIFS=$IFS
	IFS='
	'
	SFILES="$1"
	IFS=$oIFS
fi
SFILES=${SFILES:-$STAGED_FILES_CMD}

for FILE in $SFILES
do
	php -l -d display_errors=0 $PROJECT/$FILE 1> /dev/null
	if [ $? != 0 ]
	then
		echo "Fix the error before commit."
		exit 1
	fi
	FILES="$FILES $PROJECT/$FILE"
done

if [ "$FILES" != "" ]
then
	../../../vendor/shopware/platform/bin/phpstan.phar analyze --level 5 --no-progress --configuration phpstan.neon --autoload-file=../../../vendor/autoload.php $FILES
	if [ $? != 0 ]
	then
		echo "Fix the error before commit."
		exit 1
	fi
fi

if [ "$FILES" != "" ]
then
    # fix code style and update the commit
	../../../vendor/shopware/platform/bin/php-cs-fixer.phar fix --config=.php_cs.dist --quiet --allow-risky=yes -vv $FILES
    git add $FILES
fi

exit $?
