@extends('errors.layout')

@section('code', '419')
@section('title', 'Sesi Kadaluarsa')
@section('description', 'Sesi kamu telah berakhir. Muat ulang halaman dan coba lagi.')

@section('actions')
  <a href="{{ url()->previous() ?: url('/') }}" class="link">Muat Ulang</a>
  <a href="{{ url('/') }}" class="link-muted">Ke Beranda</a>
@endsection
