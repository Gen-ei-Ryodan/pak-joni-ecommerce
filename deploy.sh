#!/bin/bash

cp -r css ~/public_html/jomotocenter.com/
cp -r js ~/public_html/jomotocenter.com/
cp -r assets ~/public_html/jomotocenter.com/
cp -r fonts ~/public_html/jomotocenter.com/
cp -r images ~/public_html/jomotocenter.com/
cp -r laravel ~/public_html/jomotocenter.com/

# Set permissions for Laravel storage & cache
chmod -R 755 ~/public_html/jomotocenter.com/laravel/storage
chmod -R 755 ~/public_html/jomotocenter.com/laravel/bootstrap/cache
chmod 644 ~/public_html/jomotocenter.com/laravel/.env

cp index.php ~/public_html/jomotocenter.com/
cp .htaccess ~/public_html/jomotocenter.com/
cp favicon.ico ~/public_html/jomotocenter.com/
cp robots.txt ~/public_html/jomotocenter.com/
