<?php

namespace Fintreen\Laravel\app\Http\Controllers;

use Fintreen\Laravel\app\Models\Fintreen\FintreenModel;
use Backpack\Settings\app\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;

class FintreenController {

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

    public function webHookAction(Request $request) {
        $input_json = file_get_contents('php://input');
        Log::debug($input_json);
    }
}