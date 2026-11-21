<?php
namespace App\Providers;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
class AppServiceProvider extends ServiceProvider {
 public function boot(): void {
  RateLimiter::for('login', fn(Request $r) => Limit::perMinute(5)->by(strtolower((string)$r->input('username')).'|'.$r->ip()));
  RateLimiter::for('email', fn(Request $r) => Limit::perMinute(5)->by($r->user()->id));
 }
}
