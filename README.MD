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
#### 4. Migrate

```shell
php artisan migrate
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

/* Alternatively you can use createTransaction and getCurrenciesList without getClient*/

$fintreen->getCurrenciesList(); // This has cache wrapper with TTL of 10 minutes by default
// but you can use raw no cache method via client  $fintreen->getClient()->getCurrenciesList();

[$linkToRedirect, $createdTransaction] = $fintreen->createTransaction($amount, $cryptoCode);

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

### 3. Create transaction 

```php
[$linkToRedirect, $createdTransaction] = $fintreen->createTransaction($amount, $cryptoCode);
return redirect($linkToRedirect); // Redirect user to gateway link
```

Example with validation in real project:

```php

use Fintreen\Laravel\app\Exceptions\FintreenApiException;
use Fintreen\Laravel\app\Models\Fintreen\FintreenModel;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Http\Request;

// .........

$fintreen = new FintreenModel();
$fintreen->initClient();
$this->data['fintreenCurrencies'] = $fintreen->getCurrenciesList();

if ($request->isMethod('post')) {
    $error = false;
    $token = $request->post('g-recaptcha-response');
    $cryptoCode = $request->post('crypto-code');
    $amount = (float)$request->post('amount');
    $captchaValidaion = $this->validateRecaptcha($token, 'submit');
    if (!$captchaValidaion) {
        $error = true;
        $this->data['validationErrors'][] = 'Google recaaptcha check failed';
    }
    if (!$amount) {
        $error = true;
        $this->data['validationErrors'][] = 'Minimum amount is ' . FintreenModel::MIN_EURO_AMOUNT_DEFAULT;
    }
    if (isset($this->data['fintreenCurrencies']['crypto'])) {
        $exists = false;
        foreach ($this->data['fintreenCurrencies']['crypto'] as $fintreenCryptoCurrency) {
            if ($fintreenCryptoCurrency['code'] == $cryptoCode) {
                if ( $fintreenCryptoCurrency['min_eur_api'] > $amount) {
                    $error = true;
                    $this->data['validationErrors'][] = 'Minimum amount is ' . $fintreenCryptoCurrency['min_eur_api'];
                }
                $exists = true;
                break;
            }

        }
        if (!$exists) {
            $error = true;
            $this->data['validationErrors'][] = 'Crypto code validation error';
        }
    } else {
        $error = true;
        $this->data['validationErrors'][] = 'Crypto code validation error';
    }

    if ($error === false) {
        try {
            [$linkToRedirect, $createdTransaction] = $fintreen->createTransaction($amount, $cryptoCode);
            return redirect($linkToRedirect);
        } catch (FintreenApiException $e) {
            Log::channel('fintreen_deposit')->debug($e->getMessage(), ['context' => $e]);
            $this->data['validationErrors'][] = 'Something went wrong';
        } catch (\Exception $e) {
            Log::channel('fintreen_deposit')->debug($e->getMessage(), ['context' => $e]);
            $this->data['validationErrors'][] = 'Something went wrong';
        }
    }
}
```

### 4. Transaction list and check

#### 4.1 Transactions list

List transactions are just like with php client. 

The params to filter can be found here https://fintreen.docs.apiary.io/#/reference/order-flow/transactions-list/get-transactions-list/200?mc=reference%2Forder-flow%2Ftransactions-list%2Fget-transactions-list%2F200

```php

use Fintreen\Laravel\app\Models\Fintreen\FintreenModel;

$fintreenModel = new FintreenModel();
$fintreenModel->initClient();

$filters = [];
$fintreenModel->getClient()->getTransactionsList($filters); // returns array

// Or use raw 
$fintreenModel->getClient()->sendRequest('transactions', 'GET', $filters); // returns json string
```

#### 4.2 Transaction check

To check if transactions are paid you can use console command fintreen:transactions:check

```php 
fintreen:transactions:check {$localId?} {--limit=}
```

It run the `check` mehod on `FintreenModel`.

Also you can call this method from instance with callback.
```php
use Fintreen\Laravel\app\Models\Fintreen\FintreenModel;

//***

$fintreenModel = new FintreenModel();
$fintreenModel->initClient();

$fintreenModel->find(1); // Use local id here

$fintreenModel->check();

// ! Or use callable 

// Callback usage in the application
$fintreenModel->check($transactionId, function ($successfullTransaction) {
    // Code to deposit amount to user balance
});

```

!! Note, that callback fires only if transaction is successful.

### 5. How to handle transaction paid event (deposit balance in your app if transaction is successful)

**This is most dangerous one. Please be careful and avoid double checking and depositing balances.**

The `check` method on `FintreenModel` can get callback. In case that transaction is paid it will:
1) update status in table
2) then fire event `FintreenTransactionIsSuccess` 
3) if callback is set it will fire the callback.

There are 2 ways - use custom check with callable as described above and events subscribtion.

**Callback method:**
```php 
$fintreenModel->check(function ($successfullTransaction) use ($someData) {
    // Code to deposit amount to user balance
});
```
**Event method:**

When transaction is successful `FintreenTransactionIsSuccess` event is triggered. 

```php
use Fintreen\Laravel\app\Events\FintreenTransactionIsSuccess;

Event::listen(FintreenTransactionIsSuccess::class, function ($event) {
    // Handle the event, e.g., update user balance
});

```


### 6. Using webhook

#### 6.1 Using exsting webhook

You can set existing webhook to retrive data when transaction is checked.
By default it uses `/fintreen/webhook` but you **still** need to set it on your settings in https://fintreen.com/account/tokens at Update webhook section.

If you are using existing webhook, by default it will run `check` method on `FintreenModel` to handle transaction there.

#### 6.1 Using custom webhook

Example how to retrieve data by custom webhook.

1) create route
2) check its working with post
3) set it in your account settings
4) get the data

### 7. Good practices and Exception handling

It would be good to use own logging channel for this library.
Add to `config/logging.php` new channel

```php 
'fintreen_deposit' => [
    'driver' => 'single',
    'path' => storage_path('logs/fintreen_deposit.log'),
    'level' => env('LOG_LEVEL', 'debug'),
],
```

There are two types of exception for now

`FintreenClientException` - when client is not properly initialized

`FintreenApiException` - when request fails


<!-- MARKDOWN LINKS & IMAGES -->
<!-- https://www.markdownguide.org/basic-syntax/#reference-style-links -->
[license-shield]: https://img.shields.io/github/license/othneildrew/Best-README-Template.svg?style=for-the-badge
[license-url]: https://github.com/fintreen/laravel-client/blob/main/LICENCE.txt