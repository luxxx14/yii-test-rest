@echo off

cd ../../..
git checkout develop
git pull
php "C:\OSPanel\custom\bin\composer.phar" update
php yii cache/flush-all

cd api
php "../vendor/codeception/base/codecept" run

pause