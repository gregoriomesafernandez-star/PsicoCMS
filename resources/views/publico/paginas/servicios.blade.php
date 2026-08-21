@extends('publico.layouts.app')

@section('contenido')
    @include('publico.secciones.servicios', ['limite' => 99])
    @include('publico.secciones.planes')
    @include('publico.secciones.como-empezar')
    @include('publico.secciones.contacto')
@endsection
