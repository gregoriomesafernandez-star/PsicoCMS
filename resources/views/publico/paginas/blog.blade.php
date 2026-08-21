@extends('publico.layouts.app')

@section('contenido')
    @include('publico.secciones.blog', ['limite' => 12])
    @include('publico.secciones.contacto')
@endsection
