@echo off
cd ../../..

cd vendor/yii2bundle/yii2-db/bin
php bin schema/remove
php bin schema/create
cd ../../../..

php yii migrate --interactive=0

cd vendor/yii2bundle/yii2-db/bin
php bin fixture -i -a
cd ../../../..

pause