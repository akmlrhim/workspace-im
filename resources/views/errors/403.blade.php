@extends('errors.layout')

@section('code', '403')
@section('title', 'Akses Ditolak')
@section('description', 'Kamu tidak memiliki izin untuk mengakses halaman ini.')

@section('actions')
  <a href="{{ url('/') }}" class="link">Kembali ke Beranda</a>
  <a href="javascript:history.back()" class="link-muted">Halaman Sebelumnya</a>
@endsection
