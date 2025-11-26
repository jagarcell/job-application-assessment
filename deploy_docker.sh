docker run --rm -v $(pwd):/opt -w /opt laravelsail/php84-composer:latest composer install --ignore-platform-reqs
sudo npm install
sudo cp .env.docker .env
sudo npm run build
./vendor/bin/sail up -d
./vendor/bin/sail artisan migrate --seed