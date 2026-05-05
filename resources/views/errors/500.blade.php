@extends('errors.layout')

@section('code', '500')
@section('title', 'Kesalahan Server')
@section('description', 'Terjadi kesalahan di sisi server. Silakan coba lagi dalam beberapa saat.')

@section('actions')
  <a href="javascript:location.reload()" class="link">Coba Lagi</a>
  <a href="{{ url('/') }}" class="link-muted">Ke Beranda</a>
@endsection
