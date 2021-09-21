<x-app-layout>
    <x-slot name="header">
         {{ __('Nuevo Calculo') }}
    </x-slot>
    <div id="app">
        <calculo_completo></calculo_completo>
    </div>
    <script src="{!! asset('js/app.js').'?'.random_int(100, 99999999) !!}"></script>
</x-app-layout>