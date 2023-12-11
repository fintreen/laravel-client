<?php
declare(strict_types=1);

namespace Fintreen\Laravel\App\Models\Fintreen;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Fintreen\FintreenClient;
use Fintreen\Laravel\App\Exceptions\FintreenClientException;
use Backpack\Settings\app\Models\Setting as BackPackSettings;

class FintreenModel extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';

    protected $table = 'fintreen_transactions';

    public const CACHE_CURRENCIES_TTL_MIN_DEFAULT = 10;
    public const MIN_EURO_AMOUNT_DEFAULT = 20;
    public const CACHE_CURRENCIES_KEY = 'fintreen-currencies';

    public const DEFAULT_FIAT_CODE = 'EUR';

    protected $fintreenClient;

    static private $fintreenCurrencies = [];

    static protected function getCurrenciesCacheTtl(): int {
        return self::CACHE_CURRENCIES_TTL_MIN_DEFAULT;
    }

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    private function loadToken() {
        $token = null;
        if (config('fintreen.useConfigFromBackpackSettings') && class_exists('BackPackSettings')) {
            $token = Setting::get('fintreen_token');
        }
        if (!$token) {
            $token =  config('fintreen.token');
        }
        return $token;
    }

    private function loadEmail() {
        $email = null;
        if (config('fintreen.useConfigFromBackpackSettings') && class_exists('BackPackSettings')) {
            $email = Setting::get('fintreen_email');
        }
        if (!$email) {
            $email =  config('fintreen.email');
        }
        return $email;
    }

    public function initClient(string|null $token = null, string|null $email = null, bool $isTest = null): FintreenClient {
        if (!$token) {
            $token = $this->loadToken();
        }
        if (!$email) {
            $email = $this->loadEmail();
        }
        if (is_null($isTest)) {
            $isTest =  config('fintreen.isTest');
        }

        $this->fintreenClient = new FintreenClient($token, $email, $isTest);
        return $this->fintreenClient;
    }

    public function getClient(): FintreenClient {
        if (!$this->fintreenClient) {
            throw new FintreenClientException();
        }
        return $this->fintreenClient;
    }

    public function getCurrenciesList() {
        if (!self::$fintreenCurrencies) {
            $currencies = Cache::get(self::CACHE_CURRENCIES_KEY);
            if (!$currencies) {
                $resp = $this->getClient()->sendRequest('currencies');
                $currencies = @json_decode($resp, true);
                Cache::put(self::CACHE_CURRENCIES_KEY, $currencies, now()->addMinutes(self::getCurrenciesCacheTtl()));
            }
            self::$fintreenCurrencies = $currencies;
        }

        return self::$fintreenCurrencies;
    }



}
