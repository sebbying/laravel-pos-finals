<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
class AuthController {
 public function create(){return view('auth.login');}
 public function store(Request $r){
  $data=$r->validate(['username'=>'required|string|max:50','password'=>'required|string']);
  if(!Auth::attempt($data)) throw ValidationException::withMessages(['username'=>'Invalid username or password.']);
  $r->session()->regenerate();return redirect()->intended(route('dashboard'));
 }
 public function destroy(Request $r){Auth::logout();$r->session()->invalidate();$r->session()->regenerateToken();return redirect()->route('login');}
}
