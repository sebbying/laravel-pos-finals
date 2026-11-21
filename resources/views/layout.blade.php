<!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>@yield('title','Overview') · Counterpoint POS</title><link rel="stylesheet" href="{{ asset('app.css') }}"><script defer src="{{ asset('app.js') }}"></script></head>
<body><div class="shell"><aside><a class="brand" href="{{ route('dashboard') }}"><span class="brand-icon">C</span> Counterpoint<span class="tag">POINT OF SALE</span></a>
@auth<nav>@foreach(['dashboard'=>'Overview','sales.create'=>'Record sale','sales.index'=>'Sales history','products.index'=>'Products','customers.index'=>'Customers'] as $route=>$text)<a class="{{ request()->routeIs($route) ? 'active' : '' }}" href="{{ route($route) }}">{{ $text }}</a>@endforeach
@if(auth()->user()->role==='admin')<small>ADMINISTRATION</small><a href="{{ route('staff.index') }}">Staff</a><a href="{{ route('email.index') }}">Email setup</a>@endif</nav><div class="account"><strong>{{ auth()->user()->full_name }}</strong><small>{{ ucfirst(auth()->user()->role) }}</small><form method="post" action="{{ route('logout') }}">@csrf<button class="text-button">Sign out</button></form></div>@endauth</aside>
<main><header><span>WORKSPACE / @yield('title','Overview')</span><span>{{ now()->format('M d, Y') }}</span></header>
@if(session('status'))<div class="notice" role="status">{{ session('status') }}</div>@endif
@if($errors->any())<div class="error" role="alert"><strong>Please check the following:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
@yield('content')<footer>Counterpoint · IT0049 Midterm Project · Laravel</footer></main></div></body></html>
