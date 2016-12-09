## Consume Laravel REST api without HTTP request.

Consume your own API from the same application without request via HTTP protocol

### Installation

- [Consume on Packagist](https://packagist.org/packages/teepluss/consume)
- [Consume on GitHub](https://github.com/teepluss/laravel-consume)

To get the latest version of `Consume` simply require it in your `composer.json` file.

~~~
"teepluss/consume": "^1.0.0"
~~~

You'll then need to run `composer install` to download it and have the autoloader updated.

Once Consume is installed you need to register the service provider with the application. Open up `config/app.php` and find the `providers` key.

~~~
'providers' => [

    Teepluss\Consume\ConsumeServiceProvider::class,

]
~~~

Consume also ships with a facade which provides the static syntax for creating collections. You can register the facade in the `aliases` key of your `config/app.php` file.

~~~
'aliases' => [

    'Consume' => Teepluss\Consume\Facades\Consume::class,

]
~~~

## Usage

```php
$accessToken = "[YOUR_ACCESS_TOKEN]";

// File uploading.
$userfile = request()->file('userfile');

// POST parameters.
$parameters = [
    'name'     => 'Teepluss',
    'userfile' => $userfile
];

try {
    $request = Consume::asJson()
                      ->withAccessToken($accessToken)
                      ->request('POST', '/api/user', $parameters)
                      ->send();

    $response = $request->getContent();
} catch (\Teepluss\Consume\Exception\ErrorException $e) {
    // This may return laravel validation error.
    $response = $e->getContent();
} catch (\Teepluss\Consume\Exception\NotFoundException $e) {
    $response = 'Not Found Exception';
}
```

## Problem
If you are sending file upload to the REST api you need to get file directly.
```php
// Not work
request()->file('userfile');

// work
request()->files->get('userfile');
```

## Support or Contact

If you have any problems, Contact teepluss@gmail.com


[![Support via PayPal](https://rawgithub.com/chris---/Donation-Badges/master/paypal.jpeg)](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=9GEC8J7FAG6JA)
