<x-app-layout>
    <x-slot name="header">
         {{ __('Carga Comisiones') }}
    </x-slot>
    <div id="app">
        <upload_comisiones></upload_comisiones>
    </div>
    <script src="{!! asset('js/app.js') !!}"></script>
</x-app-layout>