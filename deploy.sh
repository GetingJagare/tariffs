#!/usr/bin/env bash

composer install

git fetch
git merge origin/master

php artisan cache:clear
php artisan view:clear
php artisan config:cache