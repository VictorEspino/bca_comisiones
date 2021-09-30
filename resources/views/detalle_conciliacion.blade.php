<x-app-layout>
    <x-slot name="header">
         {{ __('Detalle conciliacion ATT') }}
    </x-slot>
    <div class="flex flex-col w-full space-y-5 px-3">
        <div class="text-2xl font-bold">Alertas <span class="text-lg font-thin italic">({{$periodo}})</span></div>
        <div class="flex flex-row bg-gray-100 border-b-2 bg-blue-200 rounded-lg shadow-lg py-3 px-3">
            <div class="text-xl font-thin w-2/3 flex flex-col">
                <div class="font-bold">Lineas en ERP sin correspondencia en ATT</div>
                <div class="text-xs font-thin">Contratos activados/renovados en {{$periodo}} que no fueron pagados en el corte de comisiones ATT</div> 
                <div class="text-xs italic font-thin">(Se emite formato de aclaracion)</div>
            </div>
            <div class="text-3xl font-bold w-1/6 flex justify-center">{{$erp_att}}</div>
            <div class="text-3xl font-bold w-1/6 flex justify-center"><a href="{{route('aclaracion', ['periodo' => $periodo, 'conciliacion_id' => $conciliacion_id, 'concepto' => 'Comision no Pagada'])}}"><i class="fas fa-file-invoice-dollar text-green-500"></i></a></div>
        </div>
        <div class="flex flex-row bg-gray-100 border-b-2 bg-blue-200 rounded-lg shadow-lg py-3 px-3">
            <div class="text-xl font-thin w-2/3 flex flex-col">
                <div class="font-bold">Residual - Regla 45 dias - Mes (-2)</div>
                <div class="text-xs font-thin">Contratos activados/renovados en {{$periodo_menos_2}} que <span class="font-bold">no aparecen medidos</span> para comision residual en {{$periodo}}</div> 
                <div class="text-xs italic font-thin">(Se emite formato de aclaracion)</div>
            </div>
            <div class="text-3xl font-bold w-1/6 flex justify-center">{{$regla_45d}}</div>
            <div class="text-3xl font-bold w-1/6 flex justify-center"><a href="{{route('aclaracion', ['periodo' => $periodo, 'conciliacion_id' => $conciliacion_id, 'concepto' => 'Residual Inicial NO Pagado'])}}"><i class="fas fa-file-invoice-dollar text-green-500"></i></a></div>
        </div>
        <div class="flex flex-row bg-gray-100 border-b-2 bg-blue-200 rounded-lg shadow-lg py-3 px-3">
            <div class="text-xl font-thin w-2/3 flex flex-col">
                <div class="font-bold text-red-600">* Alerta Fraude - Aviso 1 *</div>
                <div class="text-xs font-thin">Contratos activados/renovados en {{$periodo_menos_2}} que al corte de {{$periodo}} <span class="font-bold">fueron medidos</span> y han marcado 2 eventos SIN PAGO por el cliente</div> 
                <div class="text-xs italic font-thin">(Se emite listado de lineas)</div>
            </div>
            <div class="text-3xl font-bold w-1/6 flex justify-center">{{$fraude_aviso1}}</div>
            <div class="text-3xl font-bold w-1/6 flex justify-center"><a href="{{route('alerta', ['periodo' => $periodo, 'conciliacion_id' => $conciliacion_id, 'tipo' => '1'])}}"><i class="fas fa-file-invoice-dollar text-green-500"></i></a></div>
        </div>
        <div class="flex flex-row bg-gray-100 border-b-2 bg-blue-200 rounded-lg shadow-lg py-3 px-3">
            <div class="text-xl font-thin w-2/3 flex flex-col">
                <div class="font-bold text-red-600">* Alerta Fraude - Aviso 2 *</div>
                <div class="text-xs font-thin">Contratos activados/renovados en {{$periodo_menos_3}} que al corte de {{$periodo}} <span class="font-bold">fueron medidos</span> y han marcado 3 eventos SIN PAGO por el cliente</div> 
                <div class="text-xs italic font-thin">(Se emite listado de lineas)</div>
            </div>
            <div class="text-3xl font-bold w-1/6 flex justify-center">{{$fraude_aviso2}}</div>
            <div class="text-3xl font-bold w-1/6 flex justify-center"><a href="{{route('alerta', ['periodo' => $periodo, 'conciliacion_id' => $conciliacion_id, 'tipo' => '2'])}}"><i class="fas fa-file-invoice-dollar text-green-500"></i></a></div>
        </div>
        <div class="flex flex-row bg-gray-100 border-b-2 bg-blue-200 rounded-lg shadow-lg py-3 px-3">
            <div class="text-xl font-thin w-2/3 flex flex-col">
                <div class="font-bold text-red-600">* Alerta Charge-Back *</div>
                <div class="text-xs font-thin">Contratos activados/renovados en <span class="font-bold">{{$periodo_menos_4}}</span> que al corte de <span class="font-bold">{{$periodo}}</span> <span class="font-bold">fueron medidos</span> y han marcado 4 eventos SIN PAGO por el cliente</div> 
                <div class="text-xs italic font-thin">(Se emite listado de lineas)</div>
            </div>
            <div class="text-3xl font-bold w-1/6 flex justify-center">{{$alerta_cb}}</div>
            <div class="text-3xl font-bold w-1/6 flex justify-center"><a href="{{route('alerta', ['periodo' => $periodo, 'conciliacion_id' => $conciliacion_id, 'tipo' => '3'])}}"><i class="fas fa-file-invoice-dollar text-green-500"></i></a></div>
        </div>
    </div>
</x-app-layout>