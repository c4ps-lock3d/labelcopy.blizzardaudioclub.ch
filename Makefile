deploy:
	rsync -avz public/build infomaniakbac:~/sites/labelcopy.blizzardaudioclub.ch/public
	ssh infomaniakbac 'cd ~/sites/labelcopy.blizzardaudioclub.ch && git pull origin master && make install'

install: vendor/autoload.php .env public/storage
	# composer dump-autoload
	# composer update
	# php artisan vendor:publish --provider=Webkul\ZAddArtist\Providers\ZAddArtistServiceProvider --force
	# php artisan vendor:publish --provider="Webkul\ZInventaire\Providers\ZInventaireServiceProvider" --force
	  php artisan migrate
	# /opt/php8.2/bin/php artisan vendor:publish --provider="Webkul\Store\Providers\StoreServiceProvider" --force
	# /opt/php8.2/bin/php artisan cache:clear
	# /opt/php8.2/bin/php artisan config:cache

.env:
	cp .env.example
	php artisan key:generate

public/storage:
	php artisan storage:link

vendor/autoload.php: composer.lock
	# /opt/php8.2/bin/composer install
	# /opt/php8.2/bin/composer dump-autoload
	# /opt/php8.2/bin/composer update
	# touch vendor/autoload.php