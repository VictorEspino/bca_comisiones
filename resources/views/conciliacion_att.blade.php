<x-app-layout>
    <x-slot name="header">
         {{ __('Conciliacion AT&T') }}
    </x-slot>
    <div id="app">
        <conciliacion_completa></conciliacion_completa>
    </div>
    <script src="{!! asset('js/app.js').'?'.random_int(100, 99999999) !!}"></script>
</x-app-layout>