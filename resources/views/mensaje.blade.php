<x-app-layout>
    <x-slot name="header">
            {{ __('Mensaje') }}
    </x-slot>

    <div class="flex justify-center flex-col">
        <div class="w-full flex flex-row py-32">
            <div class="w-2/6"></div>
            <div class="w-4/6 border-l-8 {{$estatus=='OK'?'border-green-700':'border-red-700'}} py-6 flex flex-row">
                <div class="2/6 px-4 text-4xl {{$estatus=='OK'?'text-green-700':'text-red-700'}}">
                    {!!$estatus=='OK'?'<i class="fas fa-check-circle"></i>':'<i class="fas fa-exclamation-circle"></i>'!!}
                </div>
                <div class="w-4/6 px-6 flex justify-center">{{$mensaje}}</div>
            </div>
            <div class="w-1/6"></div>
        </div>

    </div>
</x-app-layout>
