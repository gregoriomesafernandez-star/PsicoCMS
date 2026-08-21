@extends('publico.layouts.app')

@section('portada')
    @include('publico.secciones.hero')
@endsection

@section('contenido')

    @include('publico.secciones.especialidades')

    @if ($sitio->esLanding())
        @include('publico.secciones.sobre-mi')
        @include('publico.secciones.servicios')
        @include('publico.secciones.planes')
        @include('publico.secciones.como-empezar')
        @include('publico.secciones.blog')
        @include('publico.secciones.faq')
        @include('publico.secciones.contacto')
    @else
        @include('publico.secciones.sobre-mi')
        @include('publico.secciones.servicios', ['limite' => 4])
        @include('publico.secciones.como-empezar')
        @include('publico.secciones.blog', ['limite' => 3])
        @include('publico.secciones.contacto')
    @endif

@endsection
