<?php
use Illuminate\Support\Facades\{Route,Storage};
use App\Http\Controllers\{AuthController,ProductController,CustomerController,StaffController,SaleController,EmailController};
use App\Models\{Product,Customer,User,Sale};
Route::middleware('guest')->group(function(){
 Route::get('/login',[AuthController::class,'create'])->name('login');
 Route::post('/login',[AuthController::class,'store'])->middleware('throttle:login');
});
Route::middleware('auth')->group(function(){
 Route::get('/',fn()=>view('dashboard',['products'=>Product::count(),'customers'=>Customer::count(),'sales'=>Sale::count(),'revenue'=>Sale::sum('total_price'),'recent'=>Sale::latest()->take(5)->get()]))->name('dashboard');
 Route::post('/logout',[AuthController::class,'destroy'])->name('logout');
 Route::resource('products',ProductController::class);
 Route::resource('customers',CustomerController::class);
 Route::resource('staff',StaffController::class)->parameters(['staff'=>'staff'])->middleware('admin');
 Route::get('/sales',[SaleController::class,'index'])->name('sales.index');
 Route::get('/sales/create',[SaleController::class,'create'])->name('sales.create');
 Route::post('/sales',[SaleController::class,'store'])->name('sales.store');
 Route::get('/sales/{sale}',[SaleController::class,'show'])->name('sales.show');
 Route::post('/sales/{sale}/email',[SaleController::class,'email'])->middleware('throttle:email')->name('sales.email');
 Route::get('/email',[EmailController::class,'index'])->middleware('admin')->name('email.index');
 Route::post('/email',[EmailController::class,'send'])->middleware(['admin','throttle:email'])->name('email.send');
 Route::get('/media/{file}',function(string $file){abort_unless(preg_match('/^[a-f0-9-]{36}\.jpg$/',$file),404);abort_unless(Storage::exists('images/'.$file),404);return response()->file(Storage::path('images/'.$file),['Content-Type'=>'image/jpeg','X-Content-Type-Options'=>'nosniff','Cache-Control'=>'private, max-age=3600']);})->name('media');
});
