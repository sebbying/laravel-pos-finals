<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
class EmailController {
 public function index(){return view('email.index');}
 public function send(Request $r){
  $data=$r->validate(['email'=>'required|email|max:100']);
  try {Mail::raw('Counterpoint POS email configuration test. Sent at '.now()->toDateTimeString(),fn($m)=>$m->to($data['email'])->subject('POS email test'));}
  catch(\Throwable $e){report($e);return back()->withErrors(['email'=>'Mail transport failed. Check .env and storage/logs/laravel.log.']);}
  return back()->with('status',config('mail.default')==='smtp'?'Test accepted by SMTP. Verify receipt in the inbox.':'Test written to storage/logs/laravel.log. Switch MAIL_MAILER to smtp for delivery.');
 }
}
