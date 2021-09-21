<x-app-layout>
    <x-slot name="header">
         {{ __('Carga Residual') }}
    </x-slot>
    <div id="app">
        <upload_residual></upload_residual>
    </div>
    <script src="{!! asset('js/app.js') !!}"></script>
</x-app-layout>