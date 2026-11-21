@extends('layout')
@section('title','Sign in')
@section('content')<section class="login card"><span class="eyebrow">WELCOME BACK</span><h1>Your store, in focus.</h1><p>Sign in to manage inventory and record sales.</p><form method="post" action="{{ url('/login') }}">@csrf<label>Username<input name="username" value="{{ old('username') }}" autocomplete="username" required autofocus maxlength="50"></label><label>Password<input type="password" name="password" autocomplete="current-password" required></label><button>Sign in →</button></form></section>@endsection
