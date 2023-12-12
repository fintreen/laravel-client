<?php
declare(strict_types=1);

namespace Fintreen\Laravel\app\Models\Fintreen;

use Fintreen\Laravel\app\Events\FintreenTransactionIsSuccess;
use Fintreen\Laravel\app\Exceptions\FintreenApiException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Fintreen\FintreenClient;
use Fintreen\Laravel\App\Exceptions\FintreenClientException;

class FintreenModel extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';

    protected $table = 'fintreen_transactions';

    public const CACHE_CURRENCIES_TTL_MIN_DEFAULT = 10;
    public const MIN_EURO_AMOUNT_DEFAULT = 20;
    public const CACHE_CURRENCIES_KEY = 'fintreen-currencies';

    public const DEFAULT_FIAT_CODE = 'EUR';

    public const TRANSACTION_NEW_STATUS = 1;
    public const TRANSACTION_SUCCESS_STATUS = 3;

    protected $fintreenClient;

    static private $fintreenCurrencies = [];

    protected bool $isTest = false;

    static protected function getCurrenciesCacheTtl(): int {
        return self::CACHE_CURRENCIES_TTL_MIN_DEFAULT;
    }

    private function loadToken() {
        $token = null;
        if (config('fintreen.useConfigFromBackpackSettings') && class_exists('Backpack\Settings\app\Models\Setting')) {
            $token = \Backpack\Settings\app\Models\Setting::get('fintreen_token');
        }
        if (!$token) {
            $token =  config('fintreen.token');
        }
        return $token;
    }

    private function loadEmail() {
        $email = null;
        if (config('fintreen.useConfigFromBackpackSettings') && class_exists('Backpack\Settings\app\Models\Setting')) {
            $email = \Backpack\Settings\app\Models\Setting::get('fintreen_email');
        }
        if (!$email) {
            $email =  config('fintreen.email');
        }
        return $email;
    }

    public function initClient(string|null $token = null, string|null $email = null, bool $isTest = null, bool $ignoreSslVerif = false): FintreenClient {
        if (!$token) {
            $token = $this->loadToken();
        }
        if (!$email) {
            $email = $this->loadEmail();
        }
        if (is_null($isTest)) {
            $isTest =  config('fintreen.isTest');
        }
        $this->isTest = $isTest;

        try {
            $this->fintreenClient = new FintreenClient($token, $email, $isTest, $ignoreSslVerif);
        } catch (\Exception $e) {
            throw new FintreenClientException();
        }

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

    /**
     * @param float $amount
     * @param string $cryptoCode
     * @return array|null
     * @throws FintreenApiException
     * @throws FintreenClientException
     */
    public function createTransaction(float $amount, string $cryptoCode): array|null {
        $createdTransaction = $this->getClient()->createTransaction($amount, $cryptoCode);
        if (isset($createdTransaction['status']) && $createdTransaction['status'] == 'OK') {
            $fintreenItem = new self();
            $fintreenItem->fintreen_id = $createdTransaction['data']['id'];
            $fintreenItem->fiat_amount = $amount;
            $fintreenItem->fintreen_fiat_code = self::DEFAULT_FIAT_CODE;
            $fintreenItem->crypto_amount = $createdTransaction['data']['amount'];
            $fintreenItem->fintreen_crypto_code = $createdTransaction['data']['cryptoCode'];
            $fintreenItem->fintreen_status_id = $createdTransaction['data']['statusId'];
            $fintreenItem->link = $createdTransaction['data']['link'];
            if (isset($createdTransaction['isTest'])) {
                $fintreenItem->is_test = (int)$createdTransaction['isTest'];
            } else {
                $fintreenItem->is_test = (int)$this->isTest;
            }
            $fintreenItem->save();
            return [$createdTransaction['data']['link'], $createdTransaction];
        } else {
            throw new FintreenApiException();
        }
    }

    public function check(callable|null $onSuccess = null) {
        $checkedTransaction = $this->getClient()->checkTransaction($this->fintreen_id);
        if ($checkedTransaction['data']['status'] == self::TRANSACTION_SUCCESS_STATUS) {
            $this->update(['fintreen_status_id' => self::TRANSACTION_SUCCESS_STATUS]);
            event(new FintreenTransactionIsSuccess($this));
            // Check the transaction
            if (is_callable($onSuccess)) {
                $onSuccess($this);
            }
        }
    }


}
