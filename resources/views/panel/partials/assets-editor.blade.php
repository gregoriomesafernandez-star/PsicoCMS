@push('estilos')
    <link rel="stylesheet" href="{{ asset('vendor/jodit/jodit.min.css') }}">
    <link rel="stylesheet" href="{{ asset('panel/css/editor.css') }}">
@endpush

@push('scripts')
    <script src="{{ asset('vendor/jodit/jodit.min.js') }}"></script>
    <script src="{{ asset('panel/js/editor.js') }}"></script>
@endpush
