<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fintreen_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedDecimal('fiat_amount', 35, 2)->nullable(false);
            $table->string('fintreen_fiat_code')->default('EUR');
            $table->unsignedDecimal('crypto_amount', 35, 12)->nullable(false);
            $table->string('fintreen_crypto_code');
            $table->unsignedSmallInteger('fintreen_status_id')->default(1);
            $table->unsignedSmallInteger('isTest')->default(0);
            $table->text('link');
            $table->timestamps();
        });

        if ($backPackSettings = @config('backpack.settings.table_name')) {
            if(Schema::hasTable($backPackSettings)) {
                $settings = [
                    [
                        'key'         => 'fintreen_token',
                        'name'        => 'Token for fintreen deposits',
                        'description' => 'Valid token for fintreen account deposits',
                        'value'       => env('FINTREEN_TOKEN', ''),
                        'field'       => '{"name":"value","label":"Value","type":"text"}',
                        'active'      => 1,
                    ],
                    [
                        'key'         => 'fintreen_email',
                        'name'        => 'Email for fintreen deposits',
                        'description' => 'Used for signature',
                        'value'       => env('FINTREEN_EMAIL', ''),
                        'field'       => '{"name":"value","label":"Value","type":"text"}',
                        'active'      => 1,
                    ]
                ];
                foreach ($settings as $index => $setting) {
                    $result = DB::table(config('backpack.settings.table_name'))->insertOrIgnore($setting);
                }
            }
        }

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('fintreen_transactions');
    }
};
