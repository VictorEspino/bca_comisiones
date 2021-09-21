<x-guest-layout>

        <x-jet-validation-errors class="mb-4" />

        @if (session('status'))
            <div class="mb-4 font-medium text-sm text-green-600">
                {{ session('status') }}
            </div>
        @endif

      <main class="bg-white max-w-lg mx-auto px-8 py-2 md:px-8 md:py-6 my-10 rounded-lg shadow-2xl">
        <section>
            <!--<h3 class="font-bold text-2xl">Bienvenido</h3>-->
            <p class="text-gray-900 pt-2 font-bold">Ingresa tu cuenta</p>
        </section>

        <section class="mt-3">
            <form class="flex flex-col" method="POST" action="{{ route('login') }}">
                 @csrf

            <div class="mt-4">
                <x-jet-label value="{{ __('User') }}" />
                <x-jet-input class="block mt-1 w-full" type="text" name="user" :value="old('user')" required autofocus />
            </div>
            <div class="mt-4">
                <x-jet-label value="{{ __('Password') }}" />
                <x-jet-input class="block mt-1 w-full" type="password" name="password" required autocomplete="current-password" />
            </div>
            <div class="mt-4 w-full">
                <button class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 rounded shadow-lg hover:shadow-xl transition duration-200" type="submit">Ingresar</button>
            </div>
            </form>
        </section>
    </main>

    <footer class="max-w-lg mx-auto flex justify-center text-white">
        <a href="#" class="hover:underline">Contacto</a>
        <span class="mx-3">•</span>
        <a href="#" class="hover:underline">Aviso de privacidad</a>
    </footer>

</x-guest-layout>
