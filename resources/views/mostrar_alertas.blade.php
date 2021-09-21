<x-app-layout>
    <x-slot name="header">
         {{ __('Alertas ATT') }}
    </x-slot>
    <div class="flex flex-col w-full space-y-5 px-3">
        <div class="text-2xl font-bold">Alertas <span class="text-lg font-thin italic">(Febrero 2021)</span></div>
        <div class="flex flex-row bg-gray-100 border-b-2 bg-blue-200 rounded-lg shadow-lg py-3 px-3">
            <div class="text-xl font-thin w-2/3 flex flex-col">
                <div class="font-bold">Lineas en ERP sin correspondencia en ATT</div>
                <div class="text-xs font-thin">Contratos activados/renovados en Feb-21 que no fueron pagados en el corte de comisiones ATT</div> 
                <div class="text-xs italic font-thin">(Se emite formato de aclaracion)</div>
            </div>
            <div class="text-3xl font-bold w-1/6 flex justify-center">12</div>
            <div class="text-3xl font-bold w-1/6 flex justify-center"><i class="fas fa-file-invoice-dollar text-green-500"></i></div>
        </div>
        <div class="flex flex-row bg-gray-100 border-b-2 bg-blue-200 rounded-lg shadow-lg py-3 px-3">
            <div class="text-xl font-thin w-2/3 flex flex-col">
                <div class="font-bold">Residual - Regla 45 dias - Mes (-1)</div>
                <div class="text-xs font-thin">Contratos activados/renovados en Ene-21 que <span class="font-bold">no aparecen medidos</span> para comision residual en Feb-21</div> 
                <div class="text-xs italic font-thin">(Se emite formato de aclaracion)</div>
            </div>
            <div class="text-3xl font-bold w-1/6 flex justify-center">45</div>
            <div class="text-3xl font-bold w-1/6 flex justify-center"><i class="fas fa-file-invoice-dollar text-green-500"></i></div>
        </div>
        <div class="flex flex-row bg-gray-100 border-b-2 bg-blue-200 rounded-lg shadow-lg py-3 px-3">
            <div class="text-xl font-thin w-2/3 flex flex-col">
                <div class="font-bold text-red-600">* Alerta Fraude - Aviso 1 *</div>
                <div class="text-xs font-thin">Contratos activados/renovados en Dic-20 que al corte de Feb-21 <span class="font-bold">fueron medidos</span> y han marcado 2 eventos SIN PAGO por el cliente</div> 
                <div class="text-xs italic font-thin">(Se emite listado de lineas)</div>
            </div>
            <div class="text-3xl font-bold w-1/6 flex justify-center">14</div>
            <div class="text-3xl font-bold w-1/6 flex justify-center"><i class="fas fa-file-invoice-dollar text-green-500"></i></div>
        </div>
        <div class="flex flex-row bg-gray-100 border-b-2 bg-blue-200 rounded-lg shadow-lg py-3 px-3">
            <div class="text-xl font-thin w-2/3 flex flex-col">
                <div class="font-bold text-red-600">* Alerta Fraude - Aviso 2 *</div>
                <div class="text-xs font-thin">Contratos activados/renovados en Nov-20 que al corte de Feb-21 <span class="font-bold">fueron medidos</span> y han marcado 3 eventos SIN PAGO por el cliente</div> 
                <div class="text-xs italic font-thin">(Se emite listado de lineas)</div>
            </div>
            <div class="text-3xl font-bold w-1/6 flex justify-center">78</div>
            <div class="text-3xl font-bold w-1/6 flex justify-center"><i class="fas fa-file-invoice-dollar text-green-500"></i></div>
        </div>
        <div class="flex flex-row bg-gray-100 border-b-2 bg-blue-200 rounded-lg shadow-lg py-3 px-3">
            <div class="text-xl font-thin w-2/3 flex flex-col">
                <div class="font-bold text-red-600">* Alerta Charge-Back *</div>
                <div class="text-xs font-thin">Contratos activados/renovados en <span class="font-bold">Oct-20 y Sep-20</span> que al corte de <span class="font-bold">Feb-21</span> <span class="font-bold">fueron medidos</span> y han marcado 4 y 5 eventos SIN PAGO por el cliente</div> 
                <div class="text-xs italic font-thin">(Se emite listado de lineas)</div>
            </div>
            <div class="text-3xl font-bold w-1/6 flex justify-center">65</div>
            <div class="text-3xl font-bold w-1/6 flex justify-center"><i class="fas fa-file-invoice-dollar text-green-500"></i></div>
        </div>
        <div class="flex flex-row bg-gray-100 border-b-2 bg-blue-200 rounded-lg shadow-lg py-3 px-3">
            <div class="text-xl font-thin w-2/3 flex flex-col">
                <div class="font-bold">Eventos 0</div>
                <div class="text-xs font-thin">Registros en la base de residual de Feb-21 que aparecen con pago 0, cuya marca es AT&T y cuenta con estatus SUSPENDIDO</div> 
                <div class="text-xs italic font-thin">(Se emite formato de aclaracion)</div>
            </div>
            <div class="text-3xl font-bold w-1/6 flex justify-center">435</div>
            <div class="text-3xl font-bold w-1/6 flex justify-center"><i class="fas fa-file-invoice-dollar text-green-500"></i></div>
        </div>

    </div>
</x-app-layout>