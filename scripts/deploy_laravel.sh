#!/bin/bash

# Exit on error
set -o errexit -o pipefail

WWWROOT="/home/www/hjlh/"
DEPLOY_HOME="deploy-common"
scp -r deploy@172.31.19.236:~/${DEPLOY_HOME}/nginx/* /etc/nginx/
scp -r deploy@172.31.19.236:~/${DEPLOY_HOME}/php74  /etc/opt/remi/

# Check nginx config
#nginx -t -c /etc/nginx/nginx.conf

# Enter html directory
cd ${WWWROOT}

# Create cache and chmod folders
#mkdir -p ${WWWROOT}bootstrap/cache
#mkdir -p ${WWWROOT}storage/framework/sessions
#mkdir -p ${WWWROOT}storage/framework/views
#mkdir -p ${WWWROOT}storage/framework/cache
#mkdir -p ${WWWROOT}public/files/

# Install dependencies
export COMPOSER_ALLOW_SUPERUSER=1
composer install -d ${WWWROOT} --no-scripts -o --no-dev --no-interaction
# 就地部署一定需要执行，否则依赖如果发生变化，很可能报错
#composer dump-autoload -o -d ${WWWROOT}


# Copy configuration from deploy ec2, see README.MD for more information
#scp -r deploy@172.31.19.236:~/${DEPLOY_HOME}/.env ${WWWROOT}.env
cp .env.production .env
#chown -R www:www ${WWWROOT}

# Migrate all tables
#php /var/www/html/artisan migrate

# Clear any previous cached views
#php ${WWWROOT}artisan config:clear
php ${WWWROOT}artisan cache:clear
#php ${WWWROOT}artisan view:clear
#php ${WWWROOT}artisan route:clear

# Optimize the application
php ${WWWROOT}artisan config:cache
php ${WWWROOT}artisan optimize
php ${WWWROOT}artisan route:cache

# Change rights
#chmod 777 -R ${WWWROOT}bootstrap/cache
chmod 777 -R ${WWWROOT}storage

# Bring up application(report error: Script at specified location: scripts/deploy_laravel.sh run as user root failed with exit code 1)
# php ${WWWROOT}artisan up
