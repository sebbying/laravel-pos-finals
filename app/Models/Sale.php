<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Sale extends Model {
 protected $guarded = ['id'];
 protected function casts(): array { return ['total_price'=>'decimal:2','unit_price'=>'decimal:2','receipt_sent_at'=>'datetime']; }
 public function product() { return $this->belongsTo(Product::class)->withTrashed(); }
 public function customer() { return $this->belongsTo(Customer::class)->withTrashed(); }
 public function staff() { return $this->belongsTo(User::class,'sold_by')->withTrashed(); }
}
