#!/bin/bash

# Variables
DBUSER=root
DBPASSWD=root

# Update sources first
sudo apt-get update

# PHP
sudo apt-get install -y php-fpm php-mysql php-curl php-xdebug

# Nginx
sudo apt-get install -y nginx

# Preconfigure MySQL & phpMyAdmin installation
sudo apt-get install -y debconf-utils
debconf-set-selections <<< "mysql-server mysql-server/root_password password $DBPASSWD"
debconf-set-selections <<< "mysql-server mysql-server/root_password_again password $DBPASSWD"
debconf-set-selections <<< "phpmyadmin phpmyadmin/dbconfig-install boolean true"
debconf-set-selections <<< "phpmyadmin phpmyadmin/app-password-confirm password $DBPASSWD"
debconf-set-selections <<< "phpmyadmin phpmyadmin/mysql/admin-pass password $DBPASSWD"
debconf-set-selections <<< "phpmyadmin phpmyadmin/mysql/app-pass password $DBPASSWD"
debconf-set-selections <<< "phpmyadmin phpmyadmin/reconfigure-webserver multiselect none"

# MySQL & phpMyAdmin
sudo apt-get install -y mysql-server phpmyadmin

# Nginx Config
sudo rm /etc/nginx/sites-available/default
sudo touch /etc/nginx/sites-available/default

sudo cat >> /etc/nginx/sites-available/default <<'EOF'
server {
    listen 80 default_server;
    listen [::]:80 default_server;
    root /var/www/html;
    index index.php index.html index.htm;
    server_name server_domain_or_IP;
    sendfile off;
    location / {
        try_files $uri $uri/ =404;
    }
    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php7.0-fpm.sock;
    }
    location ~ /\.ht {
        deny all;
    }
    # PHPMYADMIN CONFIG
    location /phpmyadmin {
        root /usr/share/;
        index index.php index.html index.htm;
        location ~ ^/phpmyadmin/(.+\.php)$ {
                try_files $uri =404; root /usr/share/;
                fastcgi_pass unix:/run/php/php7.0-fpm.sock;
                fastcgi_index index.php;
                fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
                include fastcgi_params;
        }
        location ~* ^/phpmyadmin/(.+\.(jpg|jpeg|gif|css|png|js|ico|html|xml|txt))$ {
                root /usr/share/;
        }
    }
    location /phpMyAdmin {
        rewrite ^/* /phpmyadmin last;
    }
}
EOF

# Restarting Nginx for config to take effect
sudo service php7.0-fpm restart
sudo service mysql restart
sudo service nginx restart

echo
echo
echo "+--------------------------------------------------------+"
echo "|  WEB: 127.0.0.1 or localhost                           |"
echo "|  phpMyAdmin: 127.0.0.1/phpmyadmin                      |"
echo "|  SSH: vagrant ssh                                      |"
echo "|  MySQL/phpMyAdmin (login/password): root/root          |"
echo "+--------------------------------------------------------+"