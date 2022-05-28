#!/bin/bash

# Exit on error
set -o errexit -o pipefail

# Update yum
#yum update -y

# Install packages
yum install -y curl
yum install -y git
yum install -y unzip
#yum install -y unzip

# Get Composer, and install to /usr/local/bin
if [ ! -f "/usr/local/bin/composer" ]; then
    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
    php composer-setup.php --install-dir=/usr/bin --filename=composer
    php -r "unlink('composer-setup.php');"
else
    /usr/local/bin/composer self-update --stable --no-ansi --no-interaction
fi

# Ensure aws-cli is installed and configured
if [ ! -f "/usr/bin/aws" ]; then
#    curl "https://s3.amazonaws.com/aws-cli/awscli-bundle.zip" -o "awscli-bundle.zip"
#    unzip awscli-bundle.zip
#    ./awscli-bundle/install -b /usr/bin/aws
    # Upgrade aws-cli 2
    curl "https://awscli.amazonaws.com/awscli-exe-linux-x86_64.zip" -o "awscliv2.zip"
    unzip awscliv2.zip
    sudo ./aws/install
fi
