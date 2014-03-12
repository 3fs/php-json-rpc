#!/bin/bash

apt-get update --fix-missing
apt-get install -y python-software-properties \
                   build-essential \
                   curl

add-apt-repository -y ppa:ondrej/php5
apt-get update

apt-get install -y php5 \
                   php5-cli \
                   php5-xdebug \
                   php5-dev \
                   php-pear \
                   git-core

wget https://getcomposer.org/composer.phar -O /usr/local/bin/composer
chmod +x /usr/local/bin/composer

if [ -d "/var/www/html" ]; then
  rm -rf /var/www/html
fi
ln -s /vagrant /var/www/html

composer install -d /vagrant

# custom rules
sed -i 's/\(Minimum length for a variable, property or parameter name" value="\)3/\12/g' \
        /vagrant/vendor/phpmd/phpmd/src/main/resources/rulesets/naming.xml
