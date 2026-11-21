<?php
namespace App\Http\Controllers;
use App\Models\Product;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
class ProductController {
 public function index(Request $r) { $q=Product::query(); $term=mb_substr((string)$r->query('q',''),0,100); if($term!=='') $q->where('name','like','%'.$term.'%'); return view('manage.index',['records'=>$q->latest()->paginate(12)->withQueryString(),'entity'=>'products','title'=>'Product','term'=>$term]); }
 public function create() {return $this->form(new Product);}
 public function show(Product $product) {return view('manage.show',['record'=>$product,'entity'=>'products','title'=>'Product']);}
 public function edit(Product $product) {return $this->form($product);}
 private function form(Product $record) {return view('manage.form',compact('record')+['entity'=>'products','title'=>'Product']);}
 public function store(Request $r,ImageService $images) {return $this->save($r,new Product,$images);}
 public function update(Request $r,Product $product,ImageService $images) {return $this->save($r,$product,$images);}
 private function save(Request $r,Product $record,ImageService $images) {
  $data=$r->validate(['name'=>'required|string|max:100','price'=>['required','numeric','decimal:0,2','min:0.01','max:99999999.99'],'stock_quantity'=>'required|integer|min:0|max:1000000000','image'=>'nullable|image|mimes:jpg,jpeg,png,webp|max:2048|dimensions:max_width=4000,max_height=4000']);
  
  if(empty($data['password'])) unset($data['password']);
  if($r->hasFile('image')) $data['image']=$images->store($r->file('image'));
  $record->fill($data)->save();return redirect()->route('products.index')->with('status','Product saved.');
 }
 public function destroy(Product $product) {  $product->delete();return redirect()->route('products.index')->with('status','Product removed. Transaction history retained.'); }
}
