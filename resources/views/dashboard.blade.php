<x-app-layout>
    <x-slot name="header">
         {{ __('Principal') }}
    </x-slot>
@if(Auth::user()->tipo=="distribuidor")
<div class="w-full flex flex-col">
    <div class="w-full text-lg font-bold text-gray-700">
        Consideraciones
    </div>
    <div class="w-full text-base text-gray-700 pt-3">
        Si existe alguna aclaración por alguna venta no reportada por parte del distribuidor, comisión por venta de prepago y/o cualquier otro concepto, deberán ser enviadas en el reporte de ventas, conforme las fechas de aclaraciones según calendario.
    </div>
    <div class="w-full text-base text-gray-700 pt-3">
        Dentro de su estado de cuenta y como actualización de datos de nuestra base solicitamos la CLABE interbancaria y el nombre del titular al momento de enviar la factura, esto para agilizar el pago de comisiones.
    </div>
    <div class="w-full text-lg font-bold text-gray-700 pt-5">
        Facturacion
    </div>
    <div class="w-full text-lg font-bold text-gray-700 p-5 flex flex-col">
        <div class="w-full bg-gray-300 font-semibold p-3 flex justify-center">Datos de Facturacion</div>
        <div class="w-full text-xl font-bold p-3 flex justify-center">
            Business Corporate Asociation, S.A. de C.V.
        </div>
        <div class="w-full text-xl font-bold p-1 flex justify-center">
            RFC BCA000407LG9
        </div>
        <div class="w-full text-lg font-semibold p-1 flex justify-center">
            C. Miguel Angel de Quevedo 474 Piso 2, Col. Barrio Santa Catarina
        </div>
        <div class="w-full text-lg font-semibold p-1 flex justify-center">
            Delegación Coyoacán,    México, D.F,    C.P. 04010
        </div>
    </div>
    <div class="w-full text-base text-gray-700 pt-3">
        Es muy importante que las facturas incluyan todos los requisitos fiscales siguientes, ya que por disposición oficial deberán verse dentro del documento. En caso contrario, las facturas serán rechazadas y la fecha de pago será postergada. 
    </div>
    <div class="w-full text-base text-gray-700 pt-5 flex justify-center font-bold">
        Exclusivo para llenado de factura de comisiones (Ejemplo)
    </div>
    <div class="w-full text-base text-gray-700 pt-3 flex flex-row">
        <div class="w-1/2 flex flex-col">
            <div class="w-full p-1 bg-blue-700 text-gray-100 flex justify-center">
                Concepto
            </div>
            <div class="w-full p-1 text-gray-700 font-bold flex justify-center">
                Descripcion
            </div>
        </div>
        <div class="w-1/2 flex flex-col">
            <div class="w-full p-1 bg-blue-700 text-gray-100 flex justify-center">
                Debe decir
            </div>
            <div class="w-full p-1 text-gray-700 font-bold flex justify-center">
                Comisiones Generadas por venta de planes AT&T correspondientes al periodo / mes de _________________
            </div>
        </div>
    </div>
    <div class="w-full text-base text-gray-700 pt-3">
        Estimado Distribuidor.  Si usted utiliza a el SAT como proveedor de facturación electrónica, favor de considerar los siguientes valores al momento de estar generando su factura (la única versión que se acepta es 3.3
    </div>
    <div class="w-full text-2xl font-bold text-gray-700 pt-3 flex flex-col">
        <div class="w-full flex flex-row">
            <div class="w-1/3">
                Clave de unidad:
            </div>
            <div class="w-2/3">
                E48 Unidad de servicio 
            </div>
        </div>
        <div class="w-full flex flex-row">    
            <div class="w-1/3">
                Clave del producto/servicio:
            </div>
            <div class="w-2/3">
                80141628 Servicio de distribuidores por comisión
            </div>
        </div>   
        <div class="w-full flex flex-row">    
            <div class="w-1/3">
                Método de pago:
            </div>
            <div class="w-2/3">
                PUE Pago en una sola exhibición
            </div>
        </div>   
        <div class="w-full flex flex-row">    
            <div class="w-1/3">
                Forma de pago:
            </div>
            <div class="w-2/3">
                03 Transferencia Electrónica 
            </div>
        </div> 
        <div class="w-full flex flex-row">    
            <div class="w-1/3">
                Uso del CFDI:
            </div>
            <div class="w-2/3">
                G03 Gastos en general
            </div>
        </div> 
    </div>
    <div class="w-full text-3xl text-red-700 pt-5 font-bold">
        Nota: DE NO CUMPLIR CON TODOS LOS REQUISITOS, NO SE PAGARA LA FACTURA, SE DEBERA REFACTURAR Y SE PAGARA EN EL PROXIMO PAGO SEMANAL.
    </div>

</div>
@endif
</x-app-layout>
