# SQL select query to Mongo
Install project with
```sh
composer install
```
Requirements:

  - PHP 7
  - MongoDB 3.4

Also you need to set DB options in /config/db.ini

The query must be like this:
>SELECT [<Projections>] [FROM <Target>] [WHERE <Condition>*] [GROUP BY <Field>*] [ORDER BY <Fields>* [ASC|DESC] *] [SKIP <SkipRecords>] [LIMIT <MaxRecords>]

Available conditions for where:
  ```sh
  <, >, <=, >=, =, <>
  ```
Use next command to fill db
```sh
use test
db.people.insertMany([{name: "Olexandr", age: 20, status: "coding"}, {name: "Anastasia", age: 27, status: "waiting"}, {name: "Fred", age:34, status: "chilling"}])
```
