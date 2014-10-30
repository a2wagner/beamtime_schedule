# A2 Beamtime Scheduler

This is the beamtime schedule management for the A2 collaboration. It utilizes the PHP framework [Laravel](http://laravel.com), the source can be found in the [laravel/framework](http://github.com/laravel/framework) repository. More infos as well as the documentation for the entire framework can be found on the [Laravel website](http://laravel.com/docs).

## Installation

The easiest way to install and test this software is in using [Laravel Homestead](http://laravel.com/docs/homestead). It provides a ready to use local web development environment using VirtualBox and Vagrant. Once both are installed, the ``laravel/homestead`` box can be added to the Vagrant installation via the command ``vagrant box add laravel/homestead``. This may take some time do download the box. After the image has beed added, clone the Homestead repository with git, e. g. in a central Homestead directory: ``git clone https://github.com/laravel/homestead.git Homestead``. 

When this is finished, the box has to be prepared for use. Therefore the ``Homestead.yaml`` from the repository has to be edited. For authorization while connecting to the virtual machine SSH-Keys are used. If you don't have any SSH-Keys, you can generate a pair using the following command: ``ssh-keygen -t rsa -C "you@homestead"``. When the keys are generated, specify them in the authorize and keys property in the ``Homestead.yaml`` file. 
The folders property lists all the folders which are shared with the Homestead environment. For example if your project resides in a projects folder in your Homestead directory, you change the path in the map property to "~/Homestead/projects" which then gets mapped to "/home/vagrant/Code" inside the virtual machine. 
The path after the to statement in the sites property has to be changed to the path where your public directory of the project resides, e. g. if you have a Laravel project my_project in your Homestead/projects folder, it has to be "/home/vagrant/Code/my_project/public" (following the above example). 

When this is done, the box is ready to use with a nginx server configured using the path to the project you specified in the yaml-file. To start the virtual machine, run the command ``vagrant up`` from inside your Homestead folder. Once it is finished, the project can be accessed via ``localhost:8000``. Or from anywhere in the local network with your hostname instead of localhost using port 8000. 

### Connecting via SSH

To connect to the machine with SSH, you should connect to 127.0.0.1 on port 2222 using the SSH key you specified in Homestead.yaml. It could be convenient to add an alias for this, e. g. ``alias vm='ssh vagrant@127.0.0.1 -p 2222'``. 

### Connecting to the database

The database can be accessed from your local machine via 127.0.0.1 on port 33060 (MySQL) or 54320 (Postgres) or directly from inside the virtual machine with the username "homestead" and the password "secret". 


## Intalling on a server

If you want to install Laravel and this app on a server, follow the instructions from the [Laravel Docs](http://laravel.com/docs/installation). When Laravel is installed, this repository can be used to replace the existing files in the app-directory of the Laravel installation. The [Laravel 4 Generators by Jeffrey Way](https://github.com/JeffreyWay/Laravel-4-Generators) were used in this project to create skeleton classes, to use them the composer.json has to be edited regarding the description from the linked Githun repository. Before the first use run ``composer update --dev`` (or composer.phar, depends on the installation method used). 

In the ``app/config/database.php`` the section regarding the used database system has to be adapted. In this case MySQL is used. To create the database, login to mysql with ``mysql -u root -p`` and insert your password. Inside the mysql client execute the command ``CREATE DATABASE beamtime;`` and ``quit`` afterwards. Edit the database name in ``app/config/database.php`` accordingly. The already prepared database has to be migrated via ``php artisan migrate``. The workgroups can be filled in the database using ``php artisan db:seed``. 

In the ``app/config/app.php`` the url has to be adapted. 
To create a unique encryption key for Laravel, run ``php artisan key:generate``. 


### Troubleshooting

On (at least Debian-like) systems it will show an error because the permissions still need to be set on the folders used for caching:
``chgrp -R www-data /var/www/laravel``
``chmod -R 775 /var/www/laravel/app/storage``
After this everything should run smoothly. 


## License

The Laravel framework is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT)
