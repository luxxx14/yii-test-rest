@echo off
cd ../../..
cd vendor/yii2tool/yii2-vendor/bin
php bin git/pull
php bin git/push
pause