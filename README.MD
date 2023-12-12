# Laravel client for fintreen.com.
Fintreen.com laravel client for crypto-payment gateway API

[![MIT License][license-shield]][license-url]


<h3 align="center">Fintreen.com laravel client</h3>

  <p align="center">
    <br />
    <a href="https://fintreen.docs.apiary.io/" target="_blank"><strong>Explore the docs on apiary »</strong></a>
    <br />
    <br />
    <a href="https://github.com/fintreen/laravel-client/issues">Report Bug</a>
    ·
    <a href="https://github.com/fintreen/laravel-client/issues">Request Feature</a>
    .
    <br />
    <br />
    <a href="https://github.com/fintreen/php-client/issues">>>> PHP Client <<<</a>
  </p>
</div>

Tested on php 8.0, 8.1, 8.2. Should be also good with php 8.3.
Tested on laravel 9, 10.

Use composer2 for package installation.

## Installation:

#### 1. Add next env variables to your .env file

```dotenv
#set true if you are using Laravel Backpack
FINTREEN_USE_BAKCPACK=false
FINTREEN_TEST_MODE=false
FINTREEN_TOKEN="yourfintreentoken" 
FINTREEN_EMAIL="yourfintreenemail"

```

#### 2. Install the client

```
composer req fintreen/laravel-client
```

#### 3. Publish config

```shell
php artisan vendor:publish --tag=config
```

### Laravel BackPack + Settings integration

If you are using <a href="https://github.com/Laravel-Backpack/CRUD">Laravel BackPack</a> and <a href="https://github.com/Laravel-Backpack/Settings">Settings</a> add-on 
you can use `FINTREEN_USE_BAKCPACK=true` at your .env. It will add `fintreen_token` and `fintreen_email` setting 
from the `FINTREEN_TOKEN` and `FINTREEN_EMAIL` to database, and default client initialization (without params) will use them.
But if no setting were found (db records are empty) even if you are using FINTREEN_USE_BAKCPACK flag, it will fallback to configuration values from .env.
Otherwise you can use own params on `initClient` method.


## Usage:

### 0. General usage

You can use all methods from <a href="https://github.com/fintreen/php-client/issues">php-client</a> 
with `getClient()` method. Example: 

```php 

$fintreen = new FintreenModel();
$fintreen->initClient();
$this->data['fintreenCurrencies'] = $fintreen->getClient()->getCurrenciesList();
$this->data['statusesList'] = $fintreen->getClient()->getOrderStatusList();
$this->data['calculation'] = $fintreen->getClient()->calculate($amount, $cryptoCodes);

/*####....*/

$fintreen->getClient()->createTransaction($amount, $cryptoCode)

/* you can also use:
checkTransaction(), getTransactionsList() and sendRequest() methods, just like in the php client with getClient() method. 
*/

$fintreen->getCurrenciesList(); // This has cache wrapper with TTL of 10 minutes by default
// but you can use raw no cache method via client  $fintreen->getClient()->getCurrenciesList();

```

### 1. Client initialization
As far as this is just a laravel wrapper, you can initialize the client with no params. 
This will load config from config/fintreen.php file which should be published (installation point 3) which takes env variables.

Bit still you can init client with own params.
```php 
$fintreen = new FintreenModel();
$fintreen->initClient($token, $email, $isTest);

```



### 2. Calculation 
You can use existing route to calculate with `{{route('fintreen.calculate')}}` which goes to `/fintreen/calculate` by default.
The POST params you need to set are `currency` (CryptoCode) and `amount` (Fiat in EUR).

Exaple of using this with jQuery
```javascript

$request = $.ajax({
    url: "{{route('fintreen.calculate')}}",
    method: 'POST',
    data: {
        amount: amount,
        currency: currency,
    },
    beforeSend: function() {
        $('#submit-btn').addClass('hidden');
    },
    success: function(response) {
        // Handle success response
        if(response.status === 1) {
            $('#approx-to-pay').css('color', 'green').html(response.message);
            $('#submit-btn').removeClass('hidden');
        } else {
            $('#approx-to-pay').css('color', 'red').html(response.message);
            $('#submit-btn').addClass('hidden');
        }
    },
    error: function(error) {
        // Handle error response
        $('#request-response').css('color', 'red').text('Error calculation!');
    }
});
```

But for sure its better to use custom route. Take a look on `calculateAction` in `./vendor/fintreen/laravel-client/src/app/Http/Controllers/FintreenController.php`

```php
use Fintreen\Laravel\app\Models\Fintreen\FintreenModel;

....

$fintreen = new FintreenModel();
$fintreen->initClient();
$calcData = $fintreen->getClient()->calculate($amount, $cryptoCodes);

```

Note that in custom variant you can set parameters for initClient and use comma-separated crypto codes calculate method. 


<!-- MARKDOWN LINKS & IMAGES -->
<!-- https://www.markdownguide.org/basic-syntax/#reference-style-links -->
[license-shield]: https://img.shields.io/github/license/othneildrew/Best-README-Template.svg?style=for-the-badge
[license-url]: https://github.com/fintreen/laravel-client/blob/main/LICENCE.txt