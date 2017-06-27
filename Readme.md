# SQL select query to Mongo [![Build Status](https://travis-ci.org/smagolexandr/mongo_client_alt.svg?branch=master)](https://travis-ci.org/smagolexandr/mongo_client_alt)


## Requirements:


  - PHP 7
  - MongoDB 3.4
  

## Application


The query must be like this:
>SELECT [ Projections ] [FROM Target] [WHERE Condition*] [GROUP BY Field*] [ORDER BY Field* [ASC|DESC] *] [OFFSET SkipRecords] [LIMIT MaxRecords]

Available conditions for where:
  ```sh
  <, >, <=, >=, =, <>
  ```

## Apache configuration

To install application you need apache server with enabled mongodb driver. To Install mongodb driver you need execute:
```sh
sudo pecl install mongodb
```
Check if it is enabled with
```php
<?php phpinfo(); ?>
```
If doesn't you need to enable it manually:
```sh
echo "extension=mongodb.so" > /etc/php/7.0/fpm/conf.d/20-mongodb.ini
echo "extension=mongodb.so" > /etc/php/7.0/cli/conf.d/20-mongodb.ini
echo "extension=mongodb.so" > /etc/php/7.0/mods-available/mongodb.ini
```
or do the same with your destination.

Configure virtual host. For example:

```apache
<VirtualHost 0.0.0.0:80>
  ServerAdmin me@example.com
  DocumentRoot /var/www/example/web
  <Directory /var/www/example/web>
      Options Indexes FollowSymLinks MultiViews
      AllowOverride All
      Order deny,allow
      Allow from all
  </Directory>
#Uncomment for logs
#ErrorLog ${APACHE_LOG_DIR}/error.log
#CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
```


## Project install


Go to project folder and install it with:
```sh
cd ~/Sites/mongo_client_alt
composer install
```

Also you need to rename and set DB options /config/db.ini.dist

```ini
#/config/db.ini
host = 127.0.0.1
port = 27017
```


## Database


Firstly you need to [install MongoDB](https://docs.mongodb.com/manual/installation/), then load fixtures:
```sh
mongo
>use test
>db.people.insertMany(
[
  {
    "name": "Olexandr",
    "email": "smagolexandr@gmail.com",
    "age": 20,
    "status": "coding"
  },
  {
    "name": "Anastasia",
    "email": "jford@gmail.com",
    "age": 26,
    "status": "waiting"
  },
  {
    "name": "Fred",
    "email": "jqwdqwford@gmail.com",
    "age": 34,
    "status": "chilling"
  }
]
);
```

Also you can use dockerized db, just execute:
```sh
docker-compose up
```
default connection mongodb://127.0.0.1:27017


## Tests


Tests run with:
```sh
cd ~/Sites/mongo_client_alt
bin/phpunit
```
P.S. You need to use fixtures that appeared upper.
