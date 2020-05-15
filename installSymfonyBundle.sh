#!/usr/bin/env sh

function line {
  echo " "
}

function current {
  echo "Current path " $PWD
  line
}

function confirm() {
    read -r -p "${1} [y/N] " response

    case "$response" in
        [yY][eE][sS]|[yY]) 
            true
            ;;
        *)
            false
            ;;
    esac
}


username=akyoscommunication
password=iBzKXSkKg2PaBcH

line
echo "Hi! Let's make this quickly :) "
line

read -e -p "1 - Where do you want to install your project (default Current): " path
line
path="${path:=$PWD}"

cd $path

current

echo "2 - Install Symfony"
line

read -e -p "Wich version do you want to install (default 4.4): " symfonyVersion
symfonyVersion="${symfonyVersion:=4.4}"
line

read -e -p "How do you want to call your project (default defaultName): " projectName
projectName="${projectName:=defaultName}"
line


if [[ ! -d $path/$projectName ]] 
then

	echo "Downloading Symfony..."
	line

	composer create-project symfony/website-skeleton:^$symfonyVersion $projectName
fi

	cd $path/$projectName 

	current

	if [[ ! -d $path/$projectName/lib ]]
	then 

		echo "... Creation lib folder ..."
		line 
		mkdir -p lib

	fi
	
	cd $path/$projectName/lib

	current

	if confirm "Do you want to install CoreBundle ? "; 
	then

		git clone https://$username:$password@github.com/akyoscommunication/CoreBundle.git

	fi
	if confirm "Do you want to install FileManagerBundle ? "; 
	then

		git clone https://$username:$password@github.com/akyoscommunication/FileManagerBundle.git

	fi

	if confirm "Do you want to install FormBundle ? "; 
	then

		git clone https://$username:$password@github.com/akyoscommunication/FormBundle.git

	fi

	if confirm "Do you want to install BuilderBundle ? "; 
	then

		git clone https://$username:$password@github.com/akyoscommunication/BuilderBundle.git

	fi

	if confirm "Do you want to create database for this project ? "; 
	then
		if [ -f /root/.my.cnf ]; then
			echo "Please enter the NAME of the new MySQL database! (example: database1)"
			read dbname
			echo "Please enter the MySQL database CHARACTER SET! (example: latin1, utf8, ...)"
			echo "Enter utf8 if you don't know what you are doing"
			read charset
			echo "Creating new MySQL database..."
			mysql -e "CREATE DATABASE ${dbname} /*\!40100 DEFAULT CHARACTER SET ${charset} */;"
			echo "Database successfully created!"
			echo "Showing existing databases..."
			mysql -e "show databases;"
			echo ""
			echo "Please enter the NAME of the new MySQL database user! (example: user1)"
			read username
			echo "Please enter the PASSWORD for the new MySQL database user!"
			echo "Note: password will be hidden when typing"
			read -s userpass
			echo "Creating new user..."
			mysql -e "CREATE USER ${username}@localhost IDENTIFIED BY '${userpass}';"
			echo "User successfully created!"
			echo ""
			echo "Granting ALL privileges on ${dbname} to ${username}!"
			mysql -e "GRANT ALL PRIVILEGES ON ${dbname}.* TO '${username}'@'localhost';"
			mysql -e "FLUSH PRIVILEGES;"
			echo "You're good now :)"
			cd ..
			current
			sed -i "/DATABASE_URL/s/^/#/" .env
			sed -i 32i\DATABASE_URL=mysql://$username:$userpass@127.0.0.1:3306/$dbname?\n .env
		else
			echo "Please enter root user MySQL password!"
			echo "Note: password will be hidden when typing"
			read -s rootpasswd
			echo "Please enter the NAME of the new MySQL database! (example: database1)"
			read dbname
			echo "Please enter the MySQL database CHARACTER SET! (example: latin1, utf8, ...)"
			echo "Enter utf8 if you don't know what you are doing"
			read charset
			echo "Creating new MySQL database..."
			mysql -uroot -p${rootpasswd} -e "CREATE DATABASE ${dbname} /*\!40100 DEFAULT CHARACTER SET ${charset} */;"
			echo "Database successfully created!"
			echo "Showing existing databases..."
			mysql -uroot -p${rootpasswd} -e "show databases;"
			echo ""
			echo "Please enter the NAME of the new MySQL database user! (example: user1)"
			read username
			echo "Please enter the PASSWORD for the new MySQL database user!"
			echo "Note: password will be hidden when typing"
			read -s userpass
			echo "Creating new user..."
			mysql -uroot -p${rootpasswd} -e "CREATE USER ${username}@localhost IDENTIFIED BY '${userpass}';"
			echo "User successfully created!"
			echo ""
			echo "Granting ALL privileges on ${dbname} to ${username}!"
			mysql -uroot -p${rootpasswd} -e "GRANT ALL PRIVILEGES ON ${dbname}.* TO '${username}'@'localhost';"
			mysql -uroot -p${rootpasswd} -e "FLUSH PRIVILEGES;"
			echo "You're good now :)"
			cd ..
			current
			sed -i "/DATABASE_URL/s/^/#/" .env
			sed -i 32i\DATABASE_URL=mysql://$username:$userpass@127.0.0.1:3306/$dbname .env
		fi

	else

		cd ..

		current

	fi

	echo "Composer Install..."
	composer install

	echo "Webpack-encore Install..."
	composer require symfony/webpack-encore-bundle
	
	line

	echo "Yarn install"
	yarn install

	line	

	echo "Setup SassLoader..."
	yarn add sass-loader@^7.0.1 node-sass --dev
	sed -i -r 's/.*enableSassLoader().*/    .enableSassLoader()/g' webpack.config.js

	line

	echo "Build JS CSS"
	yarn run encore dev

	line

	cd templates

	current

	echo "Setup templates/base.html.twig ..."
	sed -i 4i\ "<meta name='viewport' content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0'>\n\t\t{%block meta %}{% endblock %}\n\t\t\t{% if core_options and core_options.favicon %}\n\t\t\t\t<link rel='icon' href='{{ asset(core_options.favicon) }}' />\n\t\t\t{% endif %}" base.html.twig

	cd ..

	current

	line

	echo "Setup composer.json autoload ..."

	sed -i -r 's/"App\\\\": "src\/"/"App\\\\": "src\/",\n\t\t\t"Akyos\\\\BuilderBundle\\\\": "lib\/BuilderBundle",\n\t\t\t"Akyos\\\\CoreBundle\\\\": "lib\/CoreBundle",\n\t\t\t"Akyos\\\\FileManagerBundle\\\\": "lib\/FileManagerBundle",\n\t\t\t"Akyos\\\\FormBundle\\\\": "lib\/FormBundle"/g' composer.json
	sed -i -r 's/"allow-contrib": false,/"allow-contrib": true,/g' composer.json

	composer dump-autoload

	line

	echo "Setup routes.yaml ..."

	cd config
	echo "builder:" >> routes.yaml
	echo "  resource: '../lib/BuilderBundle/Controller/'" >> routes.yaml
	echo "  type:     annotation" >> routes.yaml
	echo "core:" >> routes.yaml
	echo "  resource: '../lib/CoreBundle/Controller/'" >> routes.yaml
	echo "  type:     annotation" >> routes.yaml
	echo "contact_form:" >> routes.yaml
	echo "  resource: '../lib/FormBundle/Controller/'" >> routes.yaml
	echo "  type:     annotation" >> routes.yaml
	echo "file_manager:" >> routes.yaml
	echo "  resource: '../lib/FileManagerBundle/Controller/'" >> routes.yaml
	echo "  type:     annotation" >> routes.yaml

	cd ..

	line
	echo "Setup bundles.php ..."
	cd config
	sed -i -r "s/];/\tAkyos\\\BuilderBundle\\\AkyosBuilderBundle::class => ['all' => true],\n\tAkyos\\\CoreBundle\\\AkyosCoreBundle::class => ['all' => true],\n\tAkyos\\\FormBundle\\\AkyosFormBundle::class => ['all' => true],\n\tAkyos\\\FileManagerBundle\\\AkyosFileManagerBundle::class => ['all' => true],\n];/g" bundles.php
	cd ..

	composer require knplabs/knp-paginator-bundle symfony/swiftmailer-bundle stof/doctrine-extensions-bundle friendsofsymfony/ckeditor-bundle orm-fixtures
	composer update

	php bin/console ckeditor:install

	cd config/packages

	touch fos_ck_editor.yaml

	echo "fos_ck_editor:" >> fos_ck_editor.yaml
	echo "    base_path: 'build/ckeditor'" >> fos_ck_editor.yaml
	echo "    js_path:   'build/ckeditor/ckeditor.js'" >> fos_ck_editor.yaml

	rm security.yaml
	touch security.yaml

	echo "security:" >> security.yaml
	echo "    encoders:" >> security.yaml
	echo "        Akyos\CoreBundle\Entity\User:" >> security.yaml
	echo "            algorithm: auto" >> security.yaml
	echo "    # https://symfony.com/doc/current/security.html#where-do-users-come-from-user-providers" >> security.yaml
	echo "    providers:" >> security.yaml
	echo "        # used to reload user from session & other features (e.g. switch_user)" >> security.yaml
	echo "        app_user_provider:" >> security.yaml
	echo "            entity:" >> security.yaml
	echo "                class: Akyos\CoreBundle\Entity\User" >> security.yaml
	echo "                property: email" >> security.yaml
	echo "    firewalls:" >> security.yaml
	echo "        dev:" >> security.yaml
	echo "            pattern: ^/(_(profiler|wdt)|css|images|js)/" >> security.yaml
	echo "            security: false" >> security.yaml
	echo "        main:" >> security.yaml
	echo "            remember_me:" >> security.yaml
	echo "                secret:   '%kernel.secret%'" >> security.yaml
	echo "                lifetime: 604800 # 1 week in seconds" >> security.yaml
	echo "                path:     /" >> security.yaml
	echo "            anonymous: true" >> security.yaml
	echo "            guard:" >> security.yaml
	echo "                authenticators:" >> security.yaml
	echo "                    - Akyos\CoreBundle\Security\CoreBundleAuthenticator" >> security.yaml
	echo "            logout:" >> security.yaml
	echo "                path: app_logout" >> security.yaml
	echo "                # where to redirect after logout" >> security.yaml
	echo "                # target: app_any_route" >> security.yaml
	echo "            # activate different ways to authenticate" >> security.yaml
	echo "            # https://symfony.com/doc/current/security.html#firewalls-authentication" >> security.yaml
	echo "            # https://symfony.com/doc/current/security/impersonating_user.html" >> security.yaml
	echo "            # switch_user: true" >> security.yaml
	echo "    role_hierarchy:" >> security.yaml
	echo "        ROLE_CM:       ROLE_USER" >> security.yaml
	echo "        ROLE_ADMIN:       [ROLE_CM]" >> security.yaml
	echo "        ROLE_SUPER_ADMIN: [ROLE_ADMIN, ROLE_ALLOWED_TO_SWITCH]" >> security.yaml
	echo "        ROLE_AKYOS: [ROLE_SUPER_ADMIN, ROLE_ALLOWED_TO_SWITCH]" >> security.yaml
	echo "    # Easy way to control access for large sections of your site" >> security.yaml
	echo "    # Note: Only the *first* access control that matches will be used" >> security.yaml
	echo "    access_control:" >> security.yaml
	echo "    	- { path: ^/admin, roles: ROLE_ADMIN }" >> security.yaml
	echo "    	- { path: ^/profile, roles: ROLE_USER }" >> security.yaml

	rm twig.yaml
	touch twig.yaml

	echo "twig:" >> twig.yaml
	echo "    default_path: '%kernel.project_dir%/templates'" >> twig.yaml
	echo "    debug: '%kernel.debug%'" >> twig.yaml
	echo "    strict_variables: '%kernel.debug%'" >> twig.yaml
	echo "    exception_controller: null" >> twig.yaml
	echo "    form_themes: ['bootstrap_4_horizontal_layout.html.twig']" >> twig.yaml
	echo "    paths:" >> twig.yaml
	echo "        '%kernel.project_dir%/src/Components': Components" >> twig.yaml

	rm stof_doctrine_extensions.yaml
	touch stof_doctrine_extensions.yaml

	echo "stof_doctrine_extensions:" >> stof_doctrine_extensions.yaml
	echo "  default_locale: fr_FR" >> stof_doctrine_extensions.yaml
	echo "  orm:" >> stof_doctrine_extensions.yaml
	echo "    default:" >> stof_doctrine_extensions.yaml
	echo "      timestampable: true" >> stof_doctrine_extensions.yaml
	echo "      sluggable: true" >> stof_doctrine_extensions.yaml

	cd ..

	cd ..

	current

	php bin/console make:migration
	php bin/console doctrine:migrations:migrate

	cd src
	mkdir Components
	cd ..

 	php bin/console doctrine:fixtures:load

	symfony server:start
