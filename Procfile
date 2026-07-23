web: vendor/bin/heroku-php-apache2 public/
worker: php artisan schedule:work
release: php artisan migrate --force && php artisan db:seed --force
