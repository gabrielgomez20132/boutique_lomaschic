#/bin/bash

EMAIL="infounlimitedsoft@gmail.com;emytissera36@gmail.com;emy_36@hotmail.com.ar"

MYSQLDUMP=/bin/mysqldump
GZIP=/bin/gzip
TAR=/bin/tar
ID=/bin/id

UPLOAD_DIR=./public/uploads

FECHA=`date '+%d%m%Y-%H%M%S.%N'`
FECHA_FMT=`date '+%d/%m/%Y - %H:%M:%S'`

SITE=$($ID -u -n)
##SITE=$(cat .env | grep ^APP_SITE |  cut -d "=" -f 2)
HOST=$(cat .env | grep ^DB_HOST |  cut -d "=" -f 2)
PORT=$(cat .env | grep ^DB_PORT |  cut -d "=" -f 2)
USER=$(cat .env | grep ^DB_USERNAME |  cut -d "=" -f 2)
PASSWD=$(cat .env | grep ^DB_PASSWORD |  cut -d "=" -f 2)
DBNAME=$(cat .env | grep ^DB_DATABASE |  cut -d "=" -f 2)

SUBJECT="Backup UnlimitedSoft - $SITE - $FECHA_FMT"

DUMPFILE="DB_$DBNAME.$FECHA.sql"
UPLOADFILES="FILES_"$SITE"_"$FECHA".tar.gz"

$MYSQLDUMP -u $USER $DBNAME -p$PASSWD > $DUMPFILE

$GZIP $DUMPFILE

$TAR -czvf $UPLOADFILES $UPLOAD_DIR

##php artisan command:send_backup --to=$EMAIL --subject="$SUBJECT" --db="$DUMPFILE.gz" --files=$UPLOADFILES

php /opt/backup/backup.php $SITE "$DUMPFILE.gz"
php /opt/backup/backup.php $SITE $UPLOADFILES

rm -rf "$DUMPFILE.gz"
rm -rf $UPLOADFILES

