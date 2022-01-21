@echo off
cd ../..
php "../vendor/codeception/base/codecept" run --coverage-html
pause
