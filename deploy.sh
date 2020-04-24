#!/usr/bin/env bash

composer install

git fetch
git merge origin/master

php artisan migrate

php artisan cache:clear
php artisan view:clear
php artisan config:cache