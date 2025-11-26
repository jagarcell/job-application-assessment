sudo cp .env.laravel .env
composer install
sudo npm install
sudo php artisan migrate --seed
sudo npm run build
sudo php artisan migrate --seed
sudo php artisan serve --host=127.0.0.1 --port=60