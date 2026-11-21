<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {
  Schema::create('products',function(Blueprint $t){$t->id();$t->string('name',100);$t->decimal('price',10,2);$t->unsignedInteger('stock_quantity')->default(0);$t->string('image')->nullable();$t->timestamps();$t->softDeletes();});
  Schema::create('customers',function(Blueprint $t){$t->id();$t->string('full_name',100);$t->string('email',100);$t->string('phone',20)->nullable();$t->timestamps();$t->softDeletes();});
  Schema::create('users',function(Blueprint $t){$t->id();$t->string('username',50)->unique();$t->string('full_name',100);$t->string('password');$t->string('role',10)->default('staff');$t->string('avatar')->nullable();$t->rememberToken();$t->timestamps();$t->softDeletes();});
  Schema::create('sales',function(Blueprint $t){$t->id();$t->foreignId('product_id')->constrained()->restrictOnDelete();$t->foreignId('customer_id')->nullable()->constrained()->restrictOnDelete();$t->foreignId('sold_by')->constrained('users')->restrictOnDelete();$t->uuid('request_key')->unique();$t->unsignedInteger('quantity');$t->decimal('unit_price',10,2);$t->decimal('total_price',10,2);$t->string('product_name',100);$t->string('customer_name',100)->nullable();$t->string('customer_email',100)->nullable();$t->string('staff_name',100);$t->timestamp('receipt_sent_at')->nullable();$t->timestamps();});
 }
 public function down(): void {Schema::dropIfExists('sales');Schema::dropIfExists('users');Schema::dropIfExists('customers');Schema::dropIfExists('products');}
};
