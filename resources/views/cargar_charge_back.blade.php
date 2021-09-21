<x-app-layout>
    <x-slot name="header">
         {{ __('Carga Charge Back') }}
    </x-slot>
    <div id="app">
        <upload_charge_back></upload_charge_back>
    </div>
    <script src="{!! asset('js/app.js') !!}"></script>
</x-app-layout>