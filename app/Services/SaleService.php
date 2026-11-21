<?php
namespace App\Services;
use App\Models\{Product,Customer,Sale,User};
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
class SaleService {
 public function record(array $data, User $staff): Sale {
  return DB::transaction(function () use ($data,$staff) {
   if (Sale::where('request_key',$data['request_key'])->exists()) throw ValidationException::withMessages(['quantity'=>'This checkout was already recorded. Check sales history.']);
   $product=Product::lockForUpdate()->findOrFail($data['product_id']);
   if (Sale::where('request_key',$data['request_key'])->exists()) throw ValidationException::withMessages(['quantity'=>'This checkout was already recorded. Check sales history.']);
   $customer=empty($data['customer_id']) ? null : Customer::findOrFail($data['customer_id']);
   $quantity=(int)$data['quantity'];
   if ($quantity<1 || $quantity>$product->stock_quantity) throw ValidationException::withMessages(['quantity'=>'Insufficient stock. Available: '.$product->stock_quantity.'.']);
   [$whole,$fraction]=explode('.', $product->price);
   $cents=((int)$whole*100+(int)$fraction)*$quantity;
   if($cents>9999999999) throw ValidationException::withMessages(['quantity'=>'Total exceeds the maximum transaction amount.']);
   $updated=Product::whereKey($product->id)->where('stock_quantity','>=',$quantity)->decrement('stock_quantity',$quantity);
   if (!$updated) throw ValidationException::withMessages(['quantity'=>'Stock changed. Refresh and try again.']);
   return Sale::create(['product_id'=>$product->id,'customer_id'=>$customer?->id,'sold_by'=>$staff->id,'request_key'=>$data['request_key'],'quantity'=>$quantity,'unit_price'=>$product->price,'total_price'=>sprintf('%d.%02d',intdiv($cents,100),$cents%100),'product_name'=>$product->name,'customer_name'=>$customer?->full_name,'customer_email'=>$customer?->email,'staff_name'=>$staff->full_name]);
  },3);
 }
}
