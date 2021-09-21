<x-app-layout>
    <x-slot name="header">
         {{ __('Historial Conciliaciones') }}
    </x-slot>
    <div id="app">
        <conciliaciones_guardadas></conciliaciones_guardadas>
    </div>
    <script src="{!! asset('js/app.js').'?'.random_int(100, 99999999) !!}"></script>
</x-app-layout>