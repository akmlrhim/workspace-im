@extends('errors.layout')

@section('code', '503')
@section('title', 'Sedang Dalam Pemeliharaan')

@section('description')
  @isset($exception)
    @if($exception->getMessage())
      {{ $exception->getMessage() }}
    @else
      Sistem sedang dalam pemeliharaan. Kami akan segera kembali.
    @endif
  @else
    Sistem sedang dalam pemeliharaan. Kami akan segera kembali.
  @endisset
@endsection

@section('actions')
  <a href="javascript:location.reload()" class="link">Cek Kembali</a>
@endsection
