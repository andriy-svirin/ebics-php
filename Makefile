WIN_ETH_DRIVER := 'Ethernet adapter Ethernet'

ifdef WIN_ETH_DRIVER
WIN_ETH_IP := $(shell ipconfig.exe | grep ${WIN_ETH_DRIVER} -A3 | cut -d':' -f 2 | tail -n1 | sed -e 's/\s*//g')
endif

docker-up u start:
	cd docker && docker-compose -p ebics-client-php up -d;
	@if [ "$(WIN_ETH_IP)" ]; then cd docker && docker-compose -p ebics-client-php exec php-cli-ebics-client-php sh -c "echo '$(WIN_ETH_IP) host.docker.internal' >> /etc/hosts"; fi

docker-down d stop:
	cd docker && docker-compose -p ebics-client-php down

docker-build build:
	cd docker && docker-compose -p ebics-client-php build --no-cache

docker-php php:
	cd docker && docker-compose -p ebics-client-php exec php-cli-ebics-client-php /bin/bash

check:
	cd docker && docker-compose -p ebics-client-php exec php-cli-ebics-client-php ./vendor/bin/phpcbf
	cd docker && docker-compose -p ebics-client-php exec php-cli-ebics-client-php ./vendor/bin/phpcs
	cd docker && docker-compose -p ebics-client-php exec php-cli-ebics-client-php ./vendor/bin/phpstan --xdebug
	cd docker && docker-compose -p ebics-client-php exec php-cli-ebics-client-php ./vendor/bin/phpunit

credentials-pack:
	cd docker && docker-compose -p ebics-client-php exec php-cli-ebics-client-php zip -P $(pwd) -r ./tests/_data.zip ./tests/_data/

credentials-unpack:
	unzip -P $(pwd) ./tests/_data.zip -d .
