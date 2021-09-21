<x-app-layout>
    <x-slot name="header">
         {{ __('Historial Calculos Distribuidores') }}
    </x-slot>
    <div id="app">
        <calculos_guardados_dist></calculos_guardados_dist>
    </div>
    <script src="{!! asset('js/app.js').'?'.random_int(100, 99999999) !!}"></script>
</x-app-layout>