#!/bin/sh
php artisan down

git pull
php artisan migrate
gulp --production

chmod -R 777 bootstrap/cache public/forge public/uploads public/uploads/support public/filemanager/userfiles

chmod -R 777 storage
mkdir -p storage/framework/sessions
chmod -R 777 storage

php artisan clear-compiled
php artisan optimize

php artisan up
