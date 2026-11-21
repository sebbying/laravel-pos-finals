<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\{Product,Customer};
class DatabaseSeeder extends Seeder {
 public function run(): void {
  Product::firstOrCreate(['name'=>'Notebook'],['price'=>'45.00','stock_quantity'=>50]);
  Product::firstOrCreate(['name'=>'Ballpen'],['price'=>'12.50','stock_quantity'=>100]);
  Customer::firstOrCreate(['email'=>'demo@example.com'],['full_name'=>'Demo Customer','phone'=>'09000000000']);
 }
}
