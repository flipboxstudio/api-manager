# Rest Api Structure for Laravel

Build Rest Api with laravel v.5.3 with easy way.

## Features
* Versioning API
* Enabled/Disabled API or Some version
* Generate Controller or Request (Based artisan make)

## Installation
Require this package with composer:
```
composer require flipboxstudio/api-manager
```
Add the ApiServiceProvider to the providers array in config/app.php
```
Flipbox\ApiManager\ApiServiceProvider::class,
```
Copy the package resource to your application with the publish command:
```
php artisan vendor:publish
```
And you are ready to build your API.

## Using package

### Make new api
with php artisan create new api
```
php artisan api:new
```
that process will be generate Api\v1 folder in App\Http (You freely modify the namespace). And now you use that namespace to build your api.
Go to `http://yourbaseurl/api/v1`

### Make Controller
generate new controller to api version
```
php artisan api:make controller Auht/AuthController v1
```
this process will make new controller in v1

### Make Request
generate new request to api version
```
php artisan api:make request Auht/LoginRequest v1
```
this process will make new request in v1
