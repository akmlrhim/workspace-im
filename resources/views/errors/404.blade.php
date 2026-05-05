@extends('errors.layout')

@section('code', '404')
@section('title', 'Halaman Tidak Ditemukan')
@section('description', 'Halaman yang kamu cari tidak ada atau sudah dipindahkan.')

@section('actions')
  <a href="{{ url('/') }}" class="link">Kembali ke Beranda</a>
  <a href="javascript:history.back()" class="link-muted">Halaman Sebelumnya</a>
@endsection
