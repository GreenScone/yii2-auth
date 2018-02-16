Two-Factor Authentication
=========================
Two-Factor Authentication

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
composer require devmary/yii2-auth
```

or add

```
"devmary/yii2-auth": "*"
```

to the require section of your `composer.json` file.


Configure
---------

Add following lines to your main configuration file:

```php
'modules' => [
    'auth' => [
        'class' => 'devmary\auth\Module',
    ],
],
```

Update database schema
----------------------

The last thing you need to do is updating your database schema by applying the
migrations. Make sure that you have properly configured `db` application component
and run the following command:

```bash
$ php yii migrate/up --migrationPath=@vendor/devmary/yii2-auth/migrations
```



Usage
-----

Once the extension is installed, simply use it in your code by  :

```php
<?= \devmary\auth\AutoloadExample::widget(); ?>```
