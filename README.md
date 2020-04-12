# Drupal 9 Module Development

## Setup

To set up a local development environment, perform the following:

1. Run the following commands:

```
$ docker-compose up -d
$ docker-compose exec php composer install
$ docker-compose exec php ./vendor/bin/run drupal:site-install
```

2. Go to [http://localhost:8080/build](http://localhost:8080/build) and you have a Drupal site running. To log in, use `admin` / `admin`.

## Modules

The modules covered in the book are found inside the `packt` folder in the root of the project. These are duplicated in each chapter and are individually symlinked in the Drupal custom module folder.

By default, when setting up the project, the chapter 2 modules are symlinked. You can change this by creating a local `runner.yml` file and overriding the `chapter` value that is used in the symlink (check the default in the `runner.yml.dist` file).

Once that is done, you can run the following command to symlink the right modules:

```bash
$ docker-compose exec php ./vendor/bin/run drupal:module-setup
```

## Mails

All outgoing sent using the native PHP mailer are caught using Mailhog. You can access the emails at [http://localhost:8025](http://localhost:8025).

## Tests

Run tests as follows:

```bash
$ docker-compose exec -u www-data php ./vendor/bin/phpunit
```

This will run all the tests in the configured packt modules.

## Coding standards

To run the coding standards check, use this command:

```bash
$ docker-compose exec php ./vendor/bin/run drupal:phpcs
```

And this command to try to automatically fix coding standards issues that pop up:

```bash
$ docker-compose exec php ./vendor/bin/run drupal:phpcbf
```
