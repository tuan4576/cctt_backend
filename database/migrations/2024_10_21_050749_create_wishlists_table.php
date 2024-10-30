<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWishlistsTable extends Migration
{
    public function up()
    {
        Schema::create('wishlists', function (Blueprint $table) {
            $table->id(); // Tạo cột id (primary key, auto increment)
            $table->unsignedBigInteger('user_id'); // Khóa ngoại user_id
            $table->unsignedBigInteger('product_id'); // Khóa ngoại product_id
            $table->timestamps(); // Tạo created_at và updated_at
        });
    }

    public function down()
    {
        Schema::dropIfExists('wishlists');
    }
}
