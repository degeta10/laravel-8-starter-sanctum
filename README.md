## About

This is a starter project built using Laravel v8. <br>
The API authentication is done using Laravel Sanctum. <br>

## Pre Requesites

-   PHP 7.3 or greater
-   Composer
-   MySQL
-   Any email service credentials (use [Mailtrap](https://mailtrap.io/) for testing purposes)

## Installation

-   Clone the repository to your machine
-   Run `composer install` command to install all dependencies
-   Create 2 databases. One for the app and one for testing purposes.
-   Copy `.example.env` file and rename it to **.env**
-   Make sure the database credentials are updated in **.env** file (Both databases must have same username & password)
-   Update the testing database's name at `DB_TEST_DATABASE` in .env file
-   Update email service credentials in .env file
-   Run `php artisan key:generate` to generate app key
-   Run `php artisan config:cache`
-   Run `composer update` to update all dependencies to latest (OPTIONAL)
-   Run `php artisan config:clear`
-   Run `php artisan test` to perform all the tests
-   Run `php artisan optimize` to optimize the whole app
-   Run `php artisan migrate --seed`

**App is Ready!**

Run `php artisan serve` to start the app.
<br>
**Note: MySQL server must be up before running this command**

## Credentials

Access the app using the below credentials
   -   email: admin@admin.com
   -   password: qwe123123

## API Documentation

The API documentation can be found [here](https://documenter.getpostman.com/view/3544229/UVXesdWo).
