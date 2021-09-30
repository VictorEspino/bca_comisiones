<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap">

        <!-- Styles -->
        <link rel="stylesheet" href="{{ asset('css/app.css') }}">

        @livewireStyles

        <!-- Scripts -->
        <script src="https://cdn.jsdelivr.net/gh/alpinejs/alpine@v2.6.0/dist/alpine.js" defer></script>

<!-- PARA EL DASHBOARD -->
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.3.1/css/all.css" integrity="sha384-mzrmE5qonljUremFsqc01SB46JvROS7bZs3IO2EmfFsd15uHvIt+Y8vEf7N7fWAU" crossorigin="anonymous">
    <link href="https://unpkg.com/tailwindcss/dist/tailwind.min.css">

    <style>
        .bg-side-nav {
             background-color: #ECF0F1;
            }
    </style>
    
    </head>
    
    <body class="font-sans antialiased" >
        <div class="min-h-screen bg-gray-100">
            @livewire('navigation-dropdown')

            <!-- Page Heading -->
            <header class="bg-blue-900">
                <div class="max-w-7xl mx-auto py-6 px-2 sm:px-2 lg:px-4">
                    <h2 class="font-semibold text-l text-gray-100 leading-tight bg-blue-900">

                            <i class="fas fa-bars pr-2 text-white" onclick="sidebarToggle()"></i>

                            {{ $header }}
                    </h2>
                </div>
            </header>

            <!-- Page Content -->
            <main>
            <div class="flex -mb-4">
                <!--

                bg-side-nav w-1/2 md:w-1/6 lg:w-1/6 border-r border-side-nav hidden md:block lg:block
                -->
                <div id="sidebar" class="bg-gray-800 text-gray-200 h-screen flex w-52 flex-shrink-0 border-r border-side-nav hidden md:block lg:block">
                    <div>
                        <ul class="list-reset flex flex-col">
                        @if(Auth::user()->tipo=="admin")
                            <li class=" w-full h-full py-3 px-2 border-b border-light-border {{request()->routeIs('dashboard')?'bg-gray-500 text-yellow-300':'br-gray-800'}}">
                                <a href="{{ route('dashboard') }}" 
                                    class="font-sans font-hairline hover:font-normal text-sm text-nav-item no-underline">
                                    <i class="fas fa-tachometer-alt float-left mx-2"></i>
                                    Dashboard
                                    <span><i class="fas fa-angle-right float-right"></i></span>
                                </a>
                            </li>
                            <li class="w-full h-full py-3 px-2 border-b border-light-border">
                                <!--
                                <a href="forms.html"
                                    class="font-sans font-hairline hover:font-normal text-sm text-nav-item no-underline">
                                    <i class="fab fa-wpforms float-left mx-2"></i>
                                    Forms
                                    <span><i class="fa fa-angle-right float-right"></i></span>
                                </a>
                                -->
                                Comisiones Internas
                            </li>
                            <li class="w-full h-full py-3 px-2 border-b border-light-border {{request()->routeIs('nuevo_calculo')?'bg-gray-500 text-yellow-300':'br-gray-800'}}">
                                <a href="{{ route('nuevo_calculo') }}"
                                    class="font-sans font-hairline hover:font-normal text-sm text-nav-item no-underline">
                                    <i class="fas fa-grip-horizontal float-left mx-2"></i>
                                    Nuevo Calculo
                                    <span><i class="fa fa-angle-right float-right"></i></span>
                                </a>
                            </li>
                            
                            <li class="w-full h-full py-3 px-2 border-b border-light-border {{request()->routeIs('calculos')?'bg-gray-500 text-yellow-300':'br-gray-800'}}">
                                <a href="{{ route('calculos') }}"
                                    class="font-sans font-hairline hover:font-normal text-sm text-nav-item no-underline">
                                    <i class="fab fa-uikit float-left mx-2"></i>
                                    Historial Calculos
                                    <span><i class="fa fa-angle-right float-right"></i></span>
                                </a>
                            </li>
                            <li class="w-full h-full py-3 px-2 border-b border-light-border">
                                
                                <i class="far fa-user-circle float-left mx-2"></i>
                                    Estado de Cuenta
                                <div>
                                    <input type="text" class="bg-gray-200 text-gray-700" placeholder="Num Empleado" name="numero_empleado" id="numero_empleado_menu">
                                    <button onClick="estado_cuenta_interno();">Ver..</button>
                                </div>
                                
                            </li>
                            <li class="w-full h-full py-3 px-2 border-b border-light-border">
                                <!--
                                <a href="forms.html"
                                    class="font-sans font-hairline hover:font-normal text-sm text-nav-item no-underline">
                                    <i class="fab fa-wpforms float-left mx-2"></i>
                                    Forms
                                    <span><i class="fa fa-angle-right float-right"></i></span>
                                </a>
                                -->
                                Comisiones Distribuidores
                            </li>
                            <li class="w-full h-full py-3 px-2 border-b border-light-border {{request()->routeIs('nuevo_calculo_dist')?'bg-gray-500 text-yellow-300':'br-gray-800'}}">
                                <a href="{{ route('nuevo_calculo_dist') }}"
                                    class="font-sans font-hairline hover:font-normal text-sm text-nav-item no-underline">
                                    <i class="fas fa-grip-horizontal float-left mx-2"></i>
                                    Nuevo Calculo Dist
                                    <span><i class="fa fa-angle-right float-right"></i></span>
                                </a>
                            </li>
                            <li class="w-full h-full py-3 px-2 border-b border-light-border {{request()->routeIs('calculos_dist')?'bg-gray-500 text-yellow-300':'br-gray-800'}}">
                                <a href="{{ route('calculos_dist') }}"
                                    class="font-sans font-hairline hover:font-normal text-sm text-nav-item no-underline">
                                    <i class="fab fa-uikit float-left mx-2"></i>
                                    Historial Calculos
                                    <span><i class="fa fa-angle-right float-right"></i></span>
                                </a>
                            </li>
                            @endif
                            @if(Auth::user()->tipo=="administrativo" || Auth::user()->tipo=="admin" )
                        <li class="w-full h-full py-3 px-2 border-b border-light-border {{request()->routeIs('calculos_semanales')?'bg-gray-500 text-yellow-300':'br-gray-800'}}">
                            <a href="{{ route('calculos_semanales_admin',['tipo'=>'1']) }}"
                                class="font-sans font-hairline hover:font-normal text-sm text-nav-item no-underline">
                                <i class="fas fa-database float-left mx-2"></i>
                                    Adelantos Semanales
                                <span><i class="fa fa-angle-right float-right"></i></span>
                            </a>
                        </li>
                        <li class="w-full h-full py-3 px-2 border-b border-light-border {{request()->routeIs('calculos_mensuales')?'bg-gray-500 text-yellow-300':'br-gray-800'}}">
                            <a href="{{ route('calculos_mensuales_admin',['tipo'=>'2']) }}"
                                class="font-sans font-hairline hover:font-normal text-sm text-nav-item no-underline">
                                <i class="fas fa-database float-left mx-2"></i>
                                    Pagos Mensuales
                                <span><i class="fa fa-angle-right float-right"></i></span>
                            </a>
                        </li>
                        @endif 
                            @if(Auth::user()->tipo=="admin" || Auth::user()->tipo=="credito" )
                            <li class="w-full h-full py-3 px-2 border-b border-light-border">
                                <!--
                                <a href="forms.html"
                                    class="font-sans font-hairline hover:font-normal text-sm text-nav-item no-underline">
                                    <i class="fab fa-wpforms float-left mx-2"></i>
                                    Forms
                                    <span><i class="fa fa-angle-right float-right"></i></span>
                                </a>
                                -->
                                Conciliacion ATT
                            </li>
                            <li class="w-full h-full py-3 px-2 border-b border-light-border {{request()->routeIs('conciliacion_att')?'bg-gray-500 text-yellow-300':'br-gray-800'}}">
                                <a href="{{ route('conciliacion_att') }}"
                                    class="font-sans font-hairline hover:font-normal text-sm text-nav-item no-underline">
                                    <i class="fas fa-database float-left mx-2"></i>
                                        Nueva Conciliacion
                                    <span><i class="fa fa-angle-right float-right"></i></span>
                                </a>
                            </li>
                            <li class="w-full h-full py-3 px-2 border-b border-light-border {{request()->routeIs('conciliaciones')?'bg-gray-500 text-yellow-300':'br-gray-800'}}">
                                <a href="{{ route('conciliaciones') }}"
                                    class="font-sans font-hairline hover:font-normal text-sm text-nav-item no-underline">
                                    <i class="fas fa-database float-left mx-2"></i>
                                        Historial Conciliaciones
                                    <span><i class="fa fa-angle-right float-right"></i></span>
                                </a>
                            </li>
                            <li class="w-full h-full py-3 px-2 border-b border-light-border {{request()->routeIs('seguimiento_contrato')?'bg-gray-500 text-yellow-300':'br-gray-800'}}">
                                <a href="{{ route('seguimiento_contrato') }}"
                                    class="font-sans font-hairline hover:font-normal text-sm text-nav-item no-underline">
                                    <i class="fas fa-database float-left mx-2"></i>
                                        Seguimiento Contrato
                                    <span><i class="fa fa-angle-right float-right"></i></span>
                                </a>
                            </li>
                        @endif
                        @if(Auth::user()->tipo=="distribuidor" )
                            <li class="w-full h-full py-3 px-2 border-b border-light-border">
                                Comisiones ({{Auth::user()->user}})
                            </li>
                            <li class="w-full h-full py-3 px-2 border-b border-light-border {{request()->routeIs('calculos_semanales')?'bg-gray-500 text-yellow-300':'br-gray-800'}}">
                                <a href="{{ route('calculos_semanales',['tipo'=>'1']) }}"
                                    class="font-sans font-hairline hover:font-normal text-sm text-nav-item no-underline">
                                    <i class="fas fa-database float-left mx-2"></i>
                                        Adelantos Semanales
                                    <span><i class="fa fa-angle-right float-right"></i></span>
                                </a>
                            </li>
                            <li class="w-full h-full py-3 px-2 border-b border-light-border {{request()->routeIs('calculos_mensuales')?'bg-gray-500 text-yellow-300':'br-gray-800'}}">
                                <a href="{{ route('calculos_mensuales',['tipo'=>'2']) }}"
                                    class="font-sans font-hairline hover:font-normal text-sm text-nav-item no-underline">
                                    <i class="fas fa-database float-left mx-2"></i>
                                        Pagos Mensuales
                                    <span><i class="fa fa-angle-right float-right"></i></span>
                                </a>
                            </li>
                        @endif  
                                       
                        </ul>
            
                    </div>
                </div>
                <div class="w-full py-5 sm:px-6 lg:px-8">

                    {{ $slot }}

                
                </div>
            </div>
            </main>
        </div>

        @stack('modals')

        @livewireScripts
        <script>
            var sidebar = document.getElementById('sidebar');

            function sidebarToggle() {
                if(sidebar.style.display!="none") {
                    sidebar.style.display="none";
                }
                else{
                    sidebar.style.display="block";
                }
            }    
            function estado_cuenta_interno()
            {
                empleado=document.getElementById("numero_empleado_menu").value;
                if(empleado!="" && empleado!="0")
                {
                 window.open('/estado_cuenta_interno/0/'+empleado+'/0','popup','width=1300,height=900, location=no, addressbar=no');
                }
                if(empleado=="" || empleado=="0")
                {
                    alert("Por favor indique un numero de empleado para consultar");
                } 
            }    
            
        </script>
    </body>
    
</html>
