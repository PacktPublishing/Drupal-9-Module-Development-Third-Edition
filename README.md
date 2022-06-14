# Drupal 9 Module Development - Third Edition

<a href="https://www.packtpub.com/web-development/drupal-9-module-development-third-edition?utm_source=github&utm_medium=repository&utm_campaign=9781800204621"><img src="https://www.packtpub.com/media/catalog/product/cache/4cdce5a811acc0d2926d7f857dceb83b/9/7/9781800204621-original_86.jpeg" alt="Drupal 9 Module Development - Third Edition" height="256px" align="right"></a>

This is the code repository for [Drupal 9 Module Development - Third Edition](https://www.packtpub.com/web-development/drupal-9-module-development-third-edition?utm_source=github&utm_medium=repository&utm_campaign=9781800204621), published by Packt.

**Get up and running with building powerful Drupal modules and applications**

## What is this book about?
With its latest release, Drupal 9, the popular open source CMS platform has been updated with new functionalities for building complex Drupal apps with ease. This third edition of the Drupal Module Development guide covers these new Drupal features, helping you to stay on top of code deprecations and the changing architecture with every release.

This book covers the following exciting features: 
* Develop custom Drupal 9 modules for your applications
* Master different Drupal 9 subsystems and APIs
* Model, store, manipulate, and process data for effective data management
* Display data and content in a clean and secure way using the theme system
* Test your business logic to prevent regression

If you feel this book is for you, get your [copy](https://www.amazon.com/dp/1800204620) today!

<a href="https://www.packtpub.com/?utm_source=github&utm_medium=banner&utm_campaign=GitHubBanner"><img src="https://raw.githubusercontent.com/PacktPublishing/GitHub/master/GitHub.png" 
alt="https://www.packtpub.com/" border="5" /></a>

## Instructions and Navigations
All of the code is organized into folders. For example, Chapter02.

The code will look like the following:
```
hello_world.hello:
  path: '/hello'
  defaults:
    _controller:  Drupal\hello_world\Controller\HelloWorldController::helloWorld
    _title: 'Our first route'
  requirements:
    _permission: 'access content'
```
**Following is what you need for this book:**
If you are a Drupal developer looking to learn Drupal 9 to write modules for your sites, this book is for you. Drupal site builders and PHP developers with basic object-oriented programming skills will also find this book helpful. Although not necessary, some Symfony experience will help with understanding concepts easily.

With the following software and hardware list you can run all code files present in the book (Chapter 1-18).

### Software and Hardware List

| Chapter  | Software required                               | OS required                        |
| -------- | ----------------------------------------------- | -----------------------------------|
| 1-18     | Drupal 9 or higher(9.0.2, 9.0.3)                | Windows, Mac OS X, and Linux (Any) |
| 1-18     | MySQL 5.7.8/MariaDB 10.3.7/Percona Server 5.7.8 | Windows, Mac OS X, and Linux (Any) |
| 1-18     | Apache 2.4.7 or higher, Nginx 1.1 or higher     | Windows, Mac OS X, and Linux (Any) |
| 1-18     | PHP 7.3 (install via composer)                  | Windows, Mac OS X, and Linux (Any) |

## Setup

To set up a local development environment, perform the following:

1. Run the following commands:

```
$ docker-compose up -d
$ docker-compose exec php composer install
$ docker-compose exec php ./vendor/bin/run drupal:site-install
```

2. Go to [http://localhost:8080](http://localhost:8080) and you have a Drupal site running. To log in, use `admin` / `admin`.

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

## Errata
* Page 43 (Custom submit handler code snippet): **'hello_world_salutation_configuration_form_submit';** _should be_ **'my_module_salutation_configuration_form_submit';**
* Page 147 (code snippet): **\Drupal\user\UsedDataInterface** _should be_ **\Drupal\user\UserDataInterface**
* Page 204 ( 1st code snippet line 5): **$nid->addPropertyConstraints('value', ['Range' => ['mn' => 5, 'max' => 10]]);** _should be_ **$nid->addPropertyConstraints('value', ['Range' => ['min' => 5, 'max' => 10]]);**

### Related products <Other books you may enjoy>
* WordPress 5 Cookbook [[Packt]](https://www.packtpub.com/business-other/wordpress-5-cookbook?utm_source=github&utm_medium=repository&utm_campaign=9781838986506) [[Amazon]](https://www.amazon.com/dp/1838986502)

* Mastering WooCommerce 4 [[Packt]](https://www.packtpub.com/web-development/mastering-woocommerce?utm_source=github&utm_medium=repository&utm_campaign=9781838822835) [[Amazon]](https://www.amazon.com/dp/1838822836)

## Get to Know the Author
**Daniel Sipos**
is a senior web developer specializing in Drupal. He's been working with Drupal sites since version 6, and started out, like many others, as a site builder. He's a self-taught programmer with many years' experience working professionally on complex Drupal 7 and 8 projects. In his spare time, he runs webomelette.com, a Drupal website where he writes technical articles, tips, and techniques related to Drupal development.
