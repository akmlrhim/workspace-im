@extends('errors.layout')

@section('code', '429')
@section('title', 'Terlalu Banyak Permintaan')
@section('description', 'Kamu telah melakukan terlalu banyak permintaan. Tunggu sebentar dan coba lagi.')

@section('actions')
  <a href="{{ url()->current() }}" class="link">Coba Lagi</a>
  <a href="{{ url('/') }}" class="link-muted">Ke Beranda</a>
@endsection
