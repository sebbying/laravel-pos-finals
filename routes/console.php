<?php
use Illuminate\Support\Facades\{Artisan,Hash};
use App\Models\User;
Artisan::command('pos:install', function(){
 if(User::withTrashed()->exists()){ $this->error('Installation stopped: users already exist. Use the Staff page to manage accounts.');return 1; }
 $username=$this->ask('Administrator username','admin');
 $name=$this->ask('Administrator full name','John Xavier Tumbokon');
 $password=$this->secret('Administrator password (10–72 characters)');
 $validator=validator(compact('username','name','password'),['username'=>'required|alpha_dash|max:50','name'=>'required|string|max:100','password'=>'required|string|min:10|max:72']);
 if($validator->fails()){foreach($validator->errors()->all() as $error)$this->error($error);return 1;}
 User::create(['username'=>$username,'full_name'=>$name,'password'=>$password,'role'=>'admin']);
 $this->info('Administrator created. Sign in with the credentials you just entered.');
})->purpose('Create the initial administrator without a default password');
