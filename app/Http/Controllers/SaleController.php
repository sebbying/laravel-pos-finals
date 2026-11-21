<?php
namespace App\Http\Controllers;
use App\Models\{Product,Customer,Sale};
use App\Services\SaleService;
use App\Mail\SaleReceipt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
class SaleController {
 public function index(){return view('sales.index',['sales'=>Sale::latest()->paginate(15)]);}
 public function create(){return view('sales.create',['products'=>Product::where('stock_quantity','>',0)->orderBy('name')->get(),'customers'=>Customer::orderBy('full_name')->get()]);}
 public function store(Request $r,SaleService $service){
  $data=$r->validate(['product_id'=>['required','integer',Rule::exists('products','id')->whereNull('deleted_at')],'customer_id'=>['nullable','integer',Rule::exists('customers','id')->whereNull('deleted_at')],'quantity'=>'required|integer|min:1|max:1000000000','request_key'=>'required|uuid']);
  $sale=$service->record($data,$r->user());return redirect()->route('sales.show',$sale)->with('status','Sale recorded. Inventory updated.');
 }
 public function show(Sale $sale){return view('sales.show',compact('sale'));}
 public function email(Sale $sale){
  if(!$sale->customer_email) return back()->withErrors(['email'=>'This sale has no customer email.']);
  if(config('mail.default')!=='smtp') return back()->withErrors(['email'=>'Enable SMTP in .env before sending customer receipts. Use Email setup to test first.']);
  try {Mail::to($sale->customer_email)->send(new SaleReceipt($sale));$sale->update(['receipt_sent_at'=>now()]);}
  catch(\Throwable $e){report($e);return back()->withErrors(['email'=>'Email failed. The sale is saved. Check SMTP settings and retry.']);}
  return back()->with('status','Receipt accepted by the mail server. Check the recipient inbox.');
 }
}
