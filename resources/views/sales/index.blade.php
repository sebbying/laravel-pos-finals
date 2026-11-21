@extends('layout')
@section('title','Sales history')
@section('content')<div class="heading"><div><span class="eyebrow">TRANSACTIONS</span><h1>Sales history</h1><p>A permanent record of your store’s sales.</p></div><a class="button" href="{{ route('sales.create') }}">+ Record sale</a></div><section class="card">@include('sales.table')<div class="pagination">{{ $sales->links('pagination') }}</div></section>@endsection
