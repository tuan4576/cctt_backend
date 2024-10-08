<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateProductsTable extends Migration
{
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned()->index();
            $table->integer('category_id')->unsigned()->index();
            $table->integer('deal_id')->unsigned()->nullable()->index();
            $table->string('photo');
            $table->string('brand');
            $table->string('name');
            $table->string('description');
            $table->string('details');
            $table->double('price');
            $table->timestamps();
        });

        if (config('app.debug') == true) {
            DB::table('products')->insert([
                [
                    'user_id' => 1,
                    'category_id' => 1,
                    'deal_id' => null,
                    'photo' => json_encode([
                        'product01.png',
                        'product02.png',
                        'product03.png',
                        'product04.png',
                        'product05.png',
                        'product06.png',
                        'product07.png',
                        'product08.png',
                    ]),
                    'brand' => 'HP',
                    'name' => 'HP Probook 4540s',
                    'description' => 'This is the product description!',
                    'details' => 'These are the product details',
                    'price' => 700,
                    'created_at' => now(),
                ],
                [
                    'user_id' => 1,
                    'category_id' => 1,
                    'deal_id' => null,
                    'photo' => json_encode([
                        'product01.png',
                        'product02.png',
                        'product03.png',
                        'product04.png',
                        'product05.png',
                        'product06.png',
                        'product07.png',
                        'product08.png',
                    ]),
                    'brand' => 'Dell',
                    'name' => 'Dell XPS',
                    'description' => 'This is the product description!',
                    'details' => 'These are the product details',
                    'price' => 1700,
                    'created_at' => now(),
                ],
                // Add more products here if needed
            ]);
        }
    }

    public function down()
    {
        Schema::dropIfExists('products');
    }
}
