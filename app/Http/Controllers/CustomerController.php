<?php
namespace App\Http\Controllers;
use App\Models\Customer;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
class CustomerController {
 public function index(Request $r) { $q=Customer::query(); $term=mb_substr((string)$r->query('q',''),0,100); if($term!=='') $q->where('full_name','like','%'.$term.'%'); return view('manage.index',['records'=>$q->latest()->paginate(12)->withQueryString(),'entity'=>'customers','title'=>'Customer','term'=>$term]); }
 public function create() {return $this->form(new Customer);}
 public function show(Customer $customer) {return view('manage.show',['record'=>$customer,'entity'=>'customers','title'=>'Customer']);}
 public function edit(Customer $customer) {return $this->form($customer);}
 private function form(Customer $record) {return view('manage.form',compact('record')+['entity'=>'customers','title'=>'Customer']);}
 public function store(Request $r,ImageService $images) {return $this->save($r,new Customer,$images);}
 public function update(Request $r,Customer $customer,ImageService $images) {return $this->save($r,$customer,$images);}
 private function save(Request $r,Customer $record,ImageService $images) {
  $data=$r->validate(['full_name'=>'required|string|max:100','email'=>'required|email|max:100','phone'=>'nullable|string|max:20']);
  
  if(empty($data['password'])) unset($data['password']);
  
  $record->fill($data)->save();return redirect()->route('customers.index')->with('status','Customer saved.');
 }
 public function destroy(Customer $customer) {  $customer->delete();return redirect()->route('customers.index')->with('status','Customer removed. Transaction history retained.'); }
}
