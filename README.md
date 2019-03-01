# A2 Beamtime Scheduler

This is the beamtime schedule management for the A2 collaboration. It utilizes the PHP framework [Laravel](http://laravel.com), the source can be found in the [laravel/framework](http://github.com/laravel/framework) repository. More infos as well as the documentation for the entire framework can be found on the [Laravel website](http://laravel.com/docs).

## Installation

The easiest way to install and test this software is in using [Laravel Homestead](http://laravel.com/docs/homestead). It provides a ready to use local web development environment using VirtualBox and Vagrant. Once both are installed, reboot your PC in order to load the VirtualBox kernel modules. Afterwards the ``laravel/homestead`` box can be added to the Vagrant installation via the command ``vagrant box add laravel/homestead``. This may take some time to download the box. After the image has beed added, clone the Homestead repository with Git, e. g. in a central Homestead directory: ``git clone https://github.com/laravel/homestead.git Homestead``. 

### Homestead Configuration

When this is finished, the box has to be prepared for use. Therefore the ``Homestead.yaml`` from the repository has to be edited. For authorization while connecting to the virtual machine SSH-Keys are used. If you don't have any SSH-Keys, you can generate a pair using the following command: ``ssh-keygen -t rsa -C "you@homestead"``. When the keys are generated, specify them in the authorize and keys property in the ``Homestead.yaml`` file. 

The folders property lists all the folders which are shared with the Homestead environment. For example if your project resides in a projects folder in your Homestead directory, you change the path in the map property to "~/Homestead/projects" which then gets mapped to "/home/vagrant/projects" inside the virtual machine. 

The path after the to statement in the sites property has to be changed to the path where your public directory of the project resides, e. g. if you have a Laravel project my\_project in your Homestead/projects folder, it has to be "/home/vagrant/projects/my\_project/public" (following the above example). 

### Clone this Beamtime Scheduler

To finally get this beamtime scheduler, you have to create the folder you specified above, e. g. ``mkdir ~/Homestead/projects``. In this projects folder, the beamtime scheduler can be cloned after a ``cd ~/Homestead/projects`` into it via ``git clone https://github.com/a2wagner/beamtime_schedule.git beamtime`` to place it in the projects directory "beamtime" (which is called in the example above "my\_project"). 

To start the virtual machine, just run ``vagrant up`` from somewhere inside the Homestead directory. In case you're experiencing problems to launch the VM and you're using systemd, you may have to create a file ``virtualbox.conf`` in ``/etc/modules-load.d/`` and add the following content to the file: 
```
vboxdrv
vboxnetadp
vboxnetflt
vboxpci
```
If this doesn't help, try to use the hints given in the error message. After rebooting your PC the ``vagrant up`` should (hopefully) work. 

### Final set up

Now everything is prepared and running. The only thing left is to make the beamtime scheduler ready to work. For this connect to the VM via SSH with the key you created before, e. g. if your key is saved in "~/.ssh/homestead" use ``ssh vagrant@127.0.0.1 -p 2222 -i ~/.ssh/homestead``. If something went wrong and you are prompted to enter a password, use "vagrant". Inside the VM change to the directory where your project resides as configured in the Homestead.yaml, e. g. ``cd projects/beamtime/``. There run the following command to fetch all the missing dependencies: ``composer update --dev``. Once this is finished, run the prepared migrations and seed the database: 

* ``php artisan migrate``
* ``php artisan db:seed``

When this is done, the box is ready to use with a nginx server configured for your use. If you reboot your PC and the VM is not running, start the virtual machine with run the command ``vagrant up`` from inside your Homestead folder. Once it is finished, the project can be accessed via ``localhost:8000``. Or from anywhere in the local network with your hostname instead of localhost using port 8000. 

### Connecting via SSH

To connect to the machine with SSH, you should connect to 127.0.0.1 on port 2222 using the SSH key you specified in Homestead.yaml. It could be convenient to add an alias for this, e. g. ``alias vm='ssh vagrant@127.0.0.1 -p 2222 -i ~/.ssh/homestead'``. 

### Connecting to the database

The database can be accessed from your local machine via 127.0.0.1 on port 33060 (MySQL) or 54320 (Postgres) or directly from inside the virtual machine with the username "homestead" and the password "secret". 


## Intalling on a server

If you want to install Laravel and this app on a server, follow the instructions from the [Laravel Docs](http://laravel.com/docs/installation). An additional dependency to use the LDAP functionality to retrieve user data and authenticate them against the LDAP server, the package ``php5-ldap`` is required. When Laravel is installed, most files of this repository can be used to replace the existing files in the root directory of the Laravel installation. To do this, remove the follwing already existing folders and files: 

* app/
* public/
* composer.json

Now we only want to use specific files from the repository, a so-called **sparse checkout**, for this the minimum required version of Git is 1.7.0. Initialize a new git repository in the root of the Laravel installation: ``git init``. Now we add the remote origin we want to use: ``git remote add -f origin https://github.com/a2wagner/beamtime_schedule.git``. Now we have a repository with this remote. Now do ``git config core.sparsecheckout true`` which gives us the possibility to list exact the files we want to use from this repository in the file .git/info/sparse-checkout"". 
To add all required files, run the following:
```
echo "app/" >> .git/info/sparse-checkout
echo "public/" >> .git/info/sparse-checkout
echo "composer.json" >> .git/info/sparse-checkout
```
The rest of the files should be used from the Laravel installation. Last but not least, we have to update the files from the remote: ``git pull origin master``.

In case you already have an existing local repository which you want to make sparse, enable the git config option and edit everything as above and finally re-read the repository tree using ``git read-tree -m -u HEAD``. 

Now we have all required files for this project. 

### Final set up

The [Laravel 4 Generators by Jeffrey Way](https://github.com/JeffreyWay/Laravel-4-Generators) were used in this project to create skeleton classes as well as the [eluceo — iCal package from Markus Poerschke](https://github.com/markuspoerschke/iCal) to crate iCal files. To use them the composer.json has already been edited. Before the first use run ``composer update --dev`` (or composer.phar, depends on the installation method used) like above. 

In the ``app/config/database.php`` the section regarding the used database system has to be adapted. In this case MySQL is used. To create the database, login to mysql with ``mysql -u root -p`` and insert your password. Inside the mysql client execute the command ``CREATE DATABASE beamtime;`` and ``quit`` afterwards. Edit the database name in ``app/config/database.php`` accordingly. The already prepared database has to be migrated via ``php artisan migrate``. The workgroups can be filled in the database using ``php artisan db:seed``. 

In the ``app/config/app.php`` the url has to be adapted. 
To create a unique encryption key for Laravel, run ``php artisan key:generate``. 

Now we're done and the site should be accessible on the server .


### Updating

To get the updates from the remote, save the current state of your installation with ``git stash``. Then you can pull the changes, ``git pull``. To get the local changes back, run the command ``git stash pop``. 

### Troubleshooting

* On (at least Debian-like) systems it will show an error because the permissions still need to be set on the folders used for caching:

	- ``chgrp -R www-data /var/www/laravel``
	- ``chmod -R 775 /var/www/laravel/app/storage``

	After this everything should run smoothly. 

* If you can't run php commands from your console, you're possibly lacking the "php5-cli" package or similar. 

* In case you change something like paths in the Homestead.yaml, you may see the error "No input file specified". To change the paths etc. in the VM you have to run ``vagrant provision`` to apply the changes to the VM. 


## Upgrading to PHP 7.2

The used Laravel version 4.2 relies on the `mcrypt` module of PHP which has been removed with the release of PHP 7.1. So in order to still being able to run the Beamtime Scheduler on a up-to-date system with PHP 7.2 installed, there are a few things which need to be adapted.

### Homestead-specific

You probably wanna first test it in a local development space like Homestead. If you're using Homestead, you need to first update your system. Connect to a running vagrant session and first update some keys to verify packages:

    sudo apt-key adv --keyserver pool.sks-keyservers.net --recv-keys A4A9406876FCBD3C456770C88C718D3B5072E1F5
	sudo apt-key adv --keyserver pool.sks-keyservers.net --recv-keys B4112585D386EB94
	sudo apt-key adv --keyserver pool.sks-keyservers.net --recv-keys 696DBE66A72D76DA

Another thing is the PHP repository has changed. You probably see some 403 errors while fetching those packages. To fix this, remove the old name from the apt sources and add the new one:

    sudo rm /etc/apt/sources.list.d/ondrej-php5-5_6-trusty.list
	sudo add-apt-repository -y ppa:ondrej/php

The last thing before we can upgrade is the redis-server package. I didn't bother fixing this since I didn't need the new version, so I just disabled it from being upgraded: `sudo apt-mark hold redis-server`.

Now updating should work:

    sudo apt-get update
	sudo apt-get dist-upgrade

If there should be problems during the installation, you can trigger the skipped dpkg steps with `sudo dpkg-reconfigure -phigh -a`, and simply install the packages which made problems again. In my case I had to run `sudo apt-get install ca-certificates nginx-full` in addition to finally upgrade all packages.
Since some packages were not needed anymore, a final `sudo apt-get autoremove` gets rid of them.

Next thing is upgrading some modules for php7.2 to get the composer working. First I installed some dependencies which seemed to be missing: `sudo apt install zip unzip php7.2-zip`. Additionally I checked which PHP modules had been installed for PHP 5.6 and installed the corresponding versions for 7.2: `sudo apt-get install php7.2-curl php7.2-gd php7.2-ldap php7.2-pgsql php7.2-sqlite php7.2-ssh2 php7.2-fpm php7.2-dev php7.2-apcu`.

To switch system wide to the new PHP version, I chose it after issuing the following command:
    
	sudo update-alternatives --config php

You might want to remove the old PHP 5.6 packages with
    
	sudo apt-get --remove purge -y php5 php5-cli php5-curl php5-gd php5-intl php5-mcrypt php5-memcached php5-mysqlnd php5-readline php5-sqlite php5-cgi php5-common php5-fpm php5-imagick php5-json php5-memcache php5-mongo php5-pgsql php5-redis

The nginx configuration for Homestead relies on a hardcoded PHP5.6 socket, so we need to change the file `/etc/nginx/sites-available/homestead.app` and replace the line starting with `fastcgi_pass unix:/var/run/php` with the following line:

    fastcgi_pass unix:/var/run/php/php7.2-fpm.sock;

Restarting nginx or just restarting the VM should now properly load PHP 7.2 and the new configurations.

### Update Laravel Encrypter

Since Laravel 4.2 relies on the mcrypt module for encryption, we need to replace it. The easiest way is to use the [Laravel 4.2 Encrypter project](https://github.com/tomgrohl/laravel4-php71-encrypter) from Github. To do this, we add it to the `composer.json` file within the `require` block as an additional dependency:
    
	"tomgrohl/laravel4-php71-encrypter": "^1.1"

This has already been done for the file(s) in this repository. Additionally we need to register it as a new provider in the file `app/config/app.php` and choose either 'AES-128-CBC' or 'AES-256-CBC' as the new cipher for the project with a key of 16 or 32 bit length respectively. The commit which adds this to this repository is a00664b557880db88ac8b4433a6f157f2e8ef24e.

The next step is to install the mcrypt module with pecl. Theoretically we do not need this anymore since the new Encrypter module we use relies on OpenSSL instead, but there are still a few checks hardcoded within Laravel for this module. This could be removed, but updating stuff with composer might overwrite those changes. To prevent this problem, we need the mcrypt development files which can be installed on systems like Ubuntu with `sudo apt-get install libmcrypt-dev`, as it is the case for Homestead. On other systems replace this with the appropriate package manager and package. 

Then we can compile and install the mcrypt module for php7.2 with:
    
	sudo pecl install mcrypt-1.0.1

As a last step we need to add the mcrypt extension to the `php.ini`. On Ubuntu this file is located in `/etc/php/7.2/cli/php.ini`. There simply adding the line `extension=mcrypt.so` does the job.

In my case self-updating composer failed. To upgrade it, I followed the instructions on the [Composer Download page](https://getcomposer.org/download/) and moved the downloaded/installed `composer.phar` from the current directory to /usr/local/bin:
    
	sudo mv composer.phar /usr/local/bin/composer

With everything installed and updated, we can finally run

    composer update

to update everything and install the Encrypter module we need.


## License

The Laravel framework is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT)
