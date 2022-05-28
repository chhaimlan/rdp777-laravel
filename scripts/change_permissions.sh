#!/bin/bash

# Fix user rights
sudo usermod -a -G www www
sudo chown -R www:www /home/www/hjlh/
sudo chmod 2775 /home/www/hjlh/
# The following two commands are particularly slow,so comment
# find /home/www/hjlh/ -type d -exec sudo chmod 2775 {} \;
# find /home/www/hjlh/ -type f -exec sudo chmod 0664 {} \;
