@push('estilos')
    <link rel="stylesheet" href="{{ asset('vendor/jodit/jodit.min.css') }}">
    <link rel="stylesheet" href="{{ recurso('panel/css/editor.css') }}">
@endpush

@push('scripts')
    <script src="{{ asset('vendor/jodit/jodit.min.js') }}"></script>
    <script src="{{ recurso('panel/js/editor.js') }}"></script>
@endpush
