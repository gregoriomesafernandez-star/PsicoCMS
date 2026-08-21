@extends('publico.layouts.app')

@section('contenido')
    @include('publico.secciones.faq', ['limite' => 99])
    @include('publico.secciones.contacto')
@endsection
