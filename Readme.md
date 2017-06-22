# SQL select query to Mongo [![Build Status](https://travis-ci.org/smagolexandr/mongo_client_alt.svg?branch=master)](https://travis-ci.org/smagolexandr/mongo_client_alt)
Install project with
```sh
composer install
```
Requirements:

  - PHP 7
  - MongoDB 3.4
 
Also you need to rename and set DB options /config/db.ini.dist
You can use dockerized db, just execute:
```sh
docker-compose up
```
default connection mongodb://127.0.0.1:27017

The query must be like this:
>SELECT [ Projections ] [FROM <Target>] [WHERE <Condition>*] [GROUP BY <Field>*] [ORDER BY <Field>* [ASC|DESC] *] [OFFSET <SkipRecords>] [LIMIT <MaxRecords>]

Available conditions for where:
  ```sh
  <, >, <=, >=, =, <>
  ```

