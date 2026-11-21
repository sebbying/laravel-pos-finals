<?php
namespace App\Models;
use \Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Product extends Model {
 use SoftDeletes;
 protected $fillable = ['name', 'price', 'stock_quantity', 'image'];
 protected function casts(): array { return ['price'=>'decimal:2','stock_quantity'=>'integer']; }
}
