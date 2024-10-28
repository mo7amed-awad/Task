<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePostsTable extends Migration
{
    public function up()
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // For user relationship
            $table->string('title', 255);
            $table->text('body');
            $table->string('cover_image')->nullable(); // Nullable for updating
            $table->boolean('pinned')->default(false);
            $table->softDeletes(); // For soft deletes
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('posts');
    }
}
