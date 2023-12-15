<?php

namespace Fintreen\Laravel\app\Http\Controllers;

use Fintreen\Laravel\app\Models\Fintreen\FintreenModel;
use Backpack\Settings\app\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;

class FintreenController {

    public const TYPE_TO_CHECK = 'TRANSACTION_WEBHOOK_PAID_CHECK';

    public function calculateAction(Request $request) {
        $json['message'] = 'Calculation error!';
        $json['status'] = 0;
        $currency = $request->post('currency');
        $amount = $request->post('amount', 0);
        if ($request->method() === 'POST' && $currency && $amount) {
            $fintreen = new FintreenModel();
            $fintreen->initClient();
            $calcData = $fintreen->getClient()->calculate($amount, $currency);
            if ($calcData && isset($calcData['data'][$currency])) {
                $json['message'] = $calcData['data'][$currency];
                $json['status'] = 1;
            }
        }
        return Response::json($json);
    }

    public function webHookAction(Request $request): \Symfony\Component\HttpFoundation\Response
    {
        $input_json = file_get_contents('php://input');
        $successCount = 0;
        $decoded = @json_decode($input_json, true);
        if ($decoded && isset($decoded['transaction_id']) && isset($decoded['type']) && $decoded['type'] == self::TYPE_TO_CHECK) {
            $existingTransaction = FintreenModel::where('fintreen_id')->first();
            if (!empty($existingTransaction->id)) {
                $existingTransaction->initClient(null, null, (bool)$existingTransaction->is_test);
                $existingTransaction->check(function ($successfullTransaction) use (&$successCount) {
                    // Code to deposit amount to user balance but not that event will b fired
                    ++$successCount;
                });
            }
        }
        $response['successCount'] = $successCount;
        Log::debug($input_json);
        Log::debug($response);
        return response()->json($response);
        // Find transaction anc check it
    }
}