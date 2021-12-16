<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Models\Cuota;
use App\Models\Transaccion;
use App\Models\Calculo;
use App\Models\CalculoDistribuidores;
use App\Models\ChargeBackInterno;
use App\Models\ComisionATT;
use App\Models\Residual;
use App\Models\ChargeBackAtt;
use App\Models\Conciliacion;
use App\Models\PaymentDistribuidor;

class FileUploadController extends Controller

{
    // function to store file in 'upload' folder
    public function fileStoreCuotas(Request $request)
    {

        $request->validate([
            'calculo_id' => 'required',
            'file' => 'required|mimes:txt'
            ]);


        $respuesta=array(
                'success'=>'Archivo cargado con exito',
                'activaciones'=>0,
                'aep'=>0,
                'renovaciones'=>0,
                'rep'=>0
        );
        $deletedRows = Cuota::where('calculo_id', $request->calculo_id)->delete();

        Calculo::where('id',$request->calculo_id)
                ->update(['cuotas'=>false,'terminado'=>false]);

        $upload_path = public_path('upload');
        $file_name = $request->file->getClientOriginalName();
        $generated_new_name = time() . '.' . $request->file->getClientOriginalExtension();
        $request->file->move($upload_path, $generated_new_name);

        $contenido="";
        $filename=$upload_path."/".$generated_new_name;
        $fp = fopen($filename, "r");
        $content = fread($fp, filesize($filename));
        $lines = explode("\n", $content);
        $activaciones=0;
        $renovaciones=0;
        $aep=0;
        $rep=0;
        foreach ($lines as $linea) {
            
            $row_cuota=new Cuota();
            $campos=explode("\t",$linea);
            $row_cuota->director=$campos[0];
            $row_cuota->regional=$campos[1];
            $row_cuota->region=$campos[2];
            $row_cuota->id_gerente=$campos[3];
            $row_cuota->udn=$campos[4];
            $row_cuota->pdv=$campos[5];
            $row_cuota->esquema=$campos[6];
            $row_cuota->activaciones=$campos[7];
            $row_cuota->aep=$campos[8];
            $row_cuota->renovaciones=$campos[9];
            $row_cuota->rep=str_replace("\r","",$campos[10]);
            $row_cuota->calculo_id=$request->calculo_id;
            $row_cuota->save();
            $activaciones=$activaciones+$row_cuota->activaciones;
            $aep=$aep+$row_cuota->aep;
            $renovaciones=$renovaciones+$row_cuota->renovaciones;
            $rep=$rep+$row_cuota->rep;
           }
        fclose($fp);

        $respuesta['activaciones']=$activaciones;
        $respuesta['aep']=$aep;
        $respuesta['renovaciones']=$renovaciones;
        $respuesta['rep']=$rep;
        Calculo::where('id',$request->calculo_id)
                ->update(['cuotas'=>true]);
        return json_encode($respuesta);
    }
    public function fileStoreCC(Request $request)
    {

        $request->validate([
            'calculo_id' => 'required',
            'file' => 'required|mimes:txt'
            ]);


        $respuesta=array(
                'success'=>'Archivo cargado con exito',
                'pedidos'=>0,
        );

        Calculo::where('id',$request->calculo_id)
                ->update(['cc'=>false,'terminado'=>false]);

        $updatedRows = Transaccion::where('calculo_id', $request->calculo_id)->update(['ejecutivoCC'=>0,'supervisorCC'=>0]);


        $upload_path = public_path('upload');
        $file_name = $request->file->getClientOriginalName();
        $generated_new_name = time() . '.' . $request->file->getClientOriginalExtension();
        $request->file->move($upload_path, $generated_new_name);

        $contenido="";
        $filename=$upload_path."/".$generated_new_name;
        $fp = fopen($filename, "r");
        $content = fread($fp, filesize($filename));
        $lines = explode("\n", $content);
        $pedidos=0;

        foreach ($lines as $linea) {
            $campos=explode("\t",$linea);
            $updatedRows = Transaccion::where('calculo_id', $request->calculo_id)
                            ->where('pedido',$campos[0])
                            ->update(['ejecutivoCC'=>$campos[1],'supervisorCC'=>str_replace("\r","",$campos[2])]);
            $pedidos=$pedidos+$updatedRows;
           }
        fclose($fp);

        $respuesta['pedidos']=$pedidos;
        Calculo::where('id',$request->calculo_id)
                ->update(['cc'=>true]);
        return json_encode($respuesta);
    }
    public function fileStoreEQ0(Request $request)
    {

        $request->validate([
            'calculo_id' => 'required',
            'file' => 'required|mimes:txt'
            ]);


        $respuesta=array(
                'success'=>'Archivo cargado con exito',
                'pedidos'=>0,
        );

        Calculo::where('id',$request->calculo_id)
                ->update(['eq0'=>false,'terminado'=>false]);

        $updatedRows = Transaccion::where('calculo_id', $request->calculo_id)->update(['eq_sin_costo'=>false]);


        $upload_path = public_path('upload');
        $file_name = $request->file->getClientOriginalName();
        $generated_new_name = time() . '.' . $request->file->getClientOriginalExtension();
        $request->file->move($upload_path, $generated_new_name);

        $contenido="";
        $filename=$upload_path."/".$generated_new_name;
        $fp = fopen($filename, "r");
        $content = fread($fp, filesize($filename));
        $lines = explode("\n", $content);
        $pedidos=0;

        foreach ($lines as $linea) {
            $campos=str_replace("\r","",$linea); //LA LINEA SOLO ES EL CONTRATO
            $updatedRows = Transaccion::where('calculo_id', $request->calculo_id)
                            ->where('pedido',str_replace("\r","",$linea))
                            ->update(['eq_sin_costo'=>true]);
            $pedidos=$pedidos+$updatedRows;
           }
        fclose($fp);

        $respuesta['pedidos']=$pedidos;
        Calculo::where('id',$request->calculo_id)
                ->update(['eq0'=>true]);
        return json_encode($respuesta);
    }
    public function fileStoreCR0(Request $request)
    {

        $request->validate([
            'calculo_id' => 'required',
            'file' => 'required|mimes:txt'
            ]);


        $respuesta=array(
                'success'=>'Archivo cargado con exito',
                'pedidos'=>0,
        );

        Calculo::where('id',$request->calculo_id)
                ->update(['cr0'=>false,'terminado'=>false]);

        $updatedRows = Transaccion::where('calculo_id', $request->calculo_id)->update(['credito'=>'1']);

        $upload_path = public_path('upload');
        $file_name = $request->file->getClientOriginalName();
        $generated_new_name = time() . '.' . $request->file->getClientOriginalExtension();
        $request->file->move($upload_path, $generated_new_name);

        $contenido="";
        $filename=$upload_path."/".$generated_new_name;
        $fp = fopen($filename, "r");
        $content = fread($fp, filesize($filename));
        $lines = explode("\n", $content);
        $pedidos=0;
        

        foreach ($lines as $linea) {
            $campos=explode("\t",$linea);
            $updatedRows = Transaccion::where('calculo_id', $request->calculo_id)
                            ->where('pedido',str_replace("\r","",$campos[0]))
                            ->where('contrato',str_replace("\r","",$campos[1]))
                            ->update(['credito'=>'0','razon_cr0'=> str_replace("\r","",$campos[2])]);
            $pedidos=$pedidos+$updatedRows;
           }
        fclose($fp);

        $respuesta['pedidos']=$pedidos;
        Calculo::where('id',$request->calculo_id)
                ->update(['cr0'=>true]);
        return json_encode($respuesta);
    }
    public function fileStoreCB_Interno(Request $request)
    {

        $request->validate([
            'calculo_id' => 'required',
            'file' => 'required|mimes:txt'
            ]);


        $respuesta=array(
                'success'=>'Archivo cargado con exito',
                'recibidos'=>0,
                'aplicados'=>0,
        );
        $deletedRows = ChargeBackInterno::where('calculo_id', $request->calculo_id)->delete();

        Calculo::where('id',$request->calculo_id)
                ->update(['cb'=>false,'terminado'=>false]);

        $upload_path = public_path('upload');
        $file_name = $request->file->getClientOriginalName();
        $generated_new_name = time() . '.' . $request->file->getClientOriginalExtension();
        $request->file->move($upload_path, $generated_new_name);

        $filename=$upload_path."/".$generated_new_name;
        $fp = fopen($filename, "r");
        $content = fread($fp, filesize($filename));
        $lines = explode("\n", $content);

        $recibidos=0;
        $aplicados=0;

        foreach ($lines as $linea) {

            $linea_consulta=str_replace("\r","",$linea);
            //$transacciones=DB::table('transaccions')
            //        ->leftJoin('cuotas', 'transaccions.calculo_id', '=', 'cuotas.calculo_id')
            //        ->leftJoin('calculos', 'transaccions.calculo_id', '=', 'calculos.id')
            //        ->select('transaccions.calculo_id as calculo_origen','calculos.descripcion as pagado_en',
            //        'numero_empleado','fecha','servicio','importe','transaccions.pedido','transaccions.contrato',
            //        'transaccions.tipo_venta','transaccions.udn','transaccions.pdv','cuotas.director','cuotas.regional',
            //        'cuotas.id_gerente')
            //        ->where('transaccions.udn','cuotas.udn')
            //        ->where('transaccions.contrato',$linea_consulta)
            //        ->get();

            $transacciones=DB::select(DB::raw("select transaccions.calculo_id as calculo_origen,calculos.descripcion as pagado_en,numero_empleado,fecha,servicio,importe,transaccions.pedido,transaccions.contrato,transaccions.tipo_venta,transaccions.udn,transaccions.pdv,cuotas.director,cuotas.regional,cuotas.id_gerente FROM transaccions LEFT JOIN cuotas ON transaccions.calculo_id=cuotas.calculo_id LEFT JOIN calculos ON transaccions.calculo_id=calculos.id WHERE cuotas.udn=transaccions.udn and transaccions.contrato='$linea_consulta'"));
        

                    foreach($transacciones as $transaccion)
                    {
                        if(
                          $transaccion->tipo_venta=="Activación" ||
                          $transaccion->tipo_venta=="Activacion" ||
                          $transaccion->tipo_venta=="Renovación" ||
                          $transaccion->tipo_venta=="Renovacion" ||
                          $transaccion->tipo_venta=="Renovación Empresarial" ||
                          $transaccion->tipo_venta=="Renovacion Empresarial"
                          )
                        {
                            if($transaccion->numero_empleado!="")
                            {
                                $aplicados=$aplicados+1;
                                $registro=new ChargeBackInterno();
                                $registro->calculo_origen=$transaccion->calculo_origen;
                                $registro->pagado_en=$transaccion->pagado_en;
                                $registro->fecha=$transaccion->fecha;
                                $registro->servicio=$transaccion->servicio;
                                $registro->importe=$transaccion->importe;
                                $registro->pedido=$transaccion->pedido;
                                $registro->contrato=$transaccion->contrato;
                                $registro->tipo_venta=$transaccion->tipo_venta;
                                $registro->udn=$transaccion->udn;
                                $registro->pdv=$transaccion->pdv;
                                $registro->numero_empleado=$transaccion->numero_empleado;
                                $registro->cb=1000+$transaccion->importe*1.3;
                                $registro->rol="Vendedor";
                                $registro->calculo_id=$request->calculo_id;
                                $registro->save();
                            }
                            if($transaccion->id_gerente!="")
                            {
                                $registro=new ChargeBackInterno();
                                $registro->calculo_origen=$transaccion->calculo_origen;
                                $registro->pagado_en=$transaccion->pagado_en;
                                $registro->fecha=$transaccion->fecha;
                                $registro->servicio=$transaccion->servicio;
                                $registro->importe=$transaccion->importe;
                                $registro->pedido=$transaccion->pedido;
                                $registro->contrato=$transaccion->contrato;
                                $registro->tipo_venta=$transaccion->tipo_venta;
                                $registro->udn=$transaccion->udn;
                                $registro->pdv=$transaccion->pdv;
                                $registro->numero_empleado=$transaccion->id_gerente;
                                $registro->cb=$transaccion->importe*0.3;
                                $registro->rol="Gerente";
                                $registro->calculo_id=$request->calculo_id;
                                $registro->save();
                            }
                            if($transaccion->regional!="")
                            {
                                $registro=new ChargeBackInterno();
                                $registro->calculo_origen=$transaccion->calculo_origen;
                                $registro->pagado_en=$transaccion->pagado_en;
                                $registro->fecha=$transaccion->fecha;
                                $registro->servicio=$transaccion->servicio;
                                $registro->importe=$transaccion->importe;
                                $registro->pedido=$transaccion->pedido;
                                $registro->contrato=$transaccion->contrato;
                                $registro->tipo_venta=$transaccion->tipo_venta;
                                $registro->udn=$transaccion->udn;
                                $registro->pdv=$transaccion->pdv;
                                $registro->numero_empleado=$transaccion->regional;
                                $registro->cb=$transaccion->importe*0.05;
                                $registro->rol="Regional";
                                $registro->calculo_id=$request->calculo_id;
                                $registro->save();
                            }
                            if($transaccion->director!="")
                            {
                                $registro=new ChargeBackInterno();
                                $registro->calculo_origen=$transaccion->calculo_origen;
                                $registro->pagado_en=$transaccion->pagado_en;
                                $registro->fecha=$transaccion->fecha;
                                $registro->servicio=$transaccion->servicio;
                                $registro->importe=$transaccion->importe;
                                $registro->pedido=$transaccion->pedido;
                                $registro->contrato=$transaccion->contrato;
                                $registro->tipo_venta=$transaccion->tipo_venta;
                                $registro->udn=$transaccion->udn;
                                $registro->pdv=$transaccion->pdv;
                                $registro->numero_empleado=$transaccion->director;
                                $registro->cb=$transaccion->importe*0.05;
                                $registro->rol="Director";
                                $registro->calculo_id=$request->calculo_id;
                                $registro->save();
                            }
                        }
                        $recibidos=$recibidos+1;
                    }
            
           }
        fclose($fp);

        $respuesta['recibidos']=$recibidos;
        $respuesta['aplicados']=$aplicados;
        Calculo::where('id',$request->calculo_id)
                ->update(['cb'=>true]);
        return json_encode($respuesta);
    }


//CARGA DE ARCHIVOS DE CONCILIACION

    public function fileStoreComisionesATT(Request $request)
    {

        $request->validate([
            'conciliacion_id' => 'required',
            'periodo' => 'required',
            'file' => 'required|mimes:txt'
            ]);
        $respuesta=array(
                'success'=>'Archivo cargado con exito',
                'registros'=>0,
        );
    
        $deletedRows = ComisionATT::where('conciliacion_id', $request->conciliacion_id)->delete();
        Conciliacion::where('id',$request->conciliacion_id)
                ->update(['comisiones_att'=>false,'terminado'=>false]);

        $upload_path = public_path('upload');
        $file_name = $request->file->getClientOriginalName();
        $generated_new_name = time() . '.' . $request->file->getClientOriginalExtension();
        $request->file->move($upload_path, $generated_new_name);

        $contenido="";
        $filename=$upload_path."/".$generated_new_name;
        $fp = fopen($filename, "r");
        $content = fread($fp, filesize($filename));
        $lines = explode("\n", $content);
        $registros=0;

        foreach ($lines as $linea) {
            $linea_limpia=str_replace("\r","",$linea); //LA LINEA SOLO ES EL CONTRATO

            $registro_comision=new ComisionATT();
            $registro_comision->contrato=$linea_limpia;
            $registro_comision->periodo=$request->periodo;
            $registro_comision->conciliacion_id=$request->conciliacion_id;
            $registro_comision->save();

            $registros=$registros+1;
           }
        fclose($fp);
        Conciliacion::where('id',$request->conciliacion_id)
                ->update(['comisiones_att'=>true]);
        $respuesta['registros']=$registros;
        return json_encode($respuesta);
    }
    public function fileStoreResidualATT(Request $request)
    {

        $request->validate([
            'conciliacion_id' => 'required',
            'periodo' => 'required',
            'file' => 'required|mimes:txt'
            ]);
        $respuesta=array(
                'success'=>'Archivo cargado con exito',
                'registros'=>0,
        );
    
        $deletedRows = Residual::where('conciliacion_id', $request->conciliacion_id)->delete();
        Conciliacion::where('id',$request->conciliacion_id)
                ->update(['residual_att'=>false,'terminado'=>false]);

        $upload_path = public_path('upload');
        $file_name = $request->file->getClientOriginalName();
        $generated_new_name = time() . '.' . $request->file->getClientOriginalExtension();
        $request->file->move($upload_path, $generated_new_name);

        $contenido="";
        $filename=$upload_path."/".$generated_new_name;
        $fp = fopen($filename, "r");
        $content = fread($fp, filesize($filename));
        $lines = explode("\n", $content);
        $registros=0;

        foreach ($lines as $linea) {
            $linea_limpia=str_replace("\r","",$linea);

            $campos=explode("\t",$linea_limpia);

            $registro_residual=new Residual();
            $registro_residual->contrato=$campos[0];
            $registro_residual->plan=$campos[1];
            $registro_residual->comision=$campos[2];
            $registro_residual->estatus=$campos[3];
            $registro_residual->marca=$campos[4];
            $registro_residual->periodo=$request->periodo;
            $registro_residual->conciliacion_id=$request->conciliacion_id;
            $registro_residual->save();

            $registros=$registros+1;
           }
        fclose($fp);
        Conciliacion::where('id',$request->conciliacion_id)
                ->update(['residual_att'=>true]);
        $respuesta['registros']=$registros;
        return json_encode($respuesta);
    }
    public function fileStoreCBATT(Request $request)
    {

        $request->validate([
            'conciliacion_id' => 'required',
            'periodo' => 'required',
            'file' => 'required|mimes:txt'
            ]);
        $respuesta=array(
                'success'=>'Archivo cargado con exito',
                'registros'=>0,
        );
    
        $deletedRows = ChargeBackAtt::where('conciliacion_id', $request->conciliacion_id)->delete();

        Conciliacion::where('id',$request->conciliacion_id)
                ->update(['charge_back_att'=>false,'terminado'=>false]);

        $upload_path = public_path('upload');
        $file_name = $request->file->getClientOriginalName();
        $generated_new_name = time() . '.' . $request->file->getClientOriginalExtension();
        $request->file->move($upload_path, $generated_new_name);

        $contenido="";
        $filename=$upload_path."/".$generated_new_name;
        $fp = fopen($filename, "r");
        $content = fread($fp, filesize($filename));
        $lines = explode("\n", $content);
        $registros=0;

        foreach ($lines as $linea) {
            $linea_limpia=str_replace("\r","",$linea);

            $campos=explode("\t",$linea_limpia);

            $registro_cb=new ChargeBackAtt();
            $registro_cb->contrato=$campos[0];
            $registro_cb->fecha=$campos[1];
            $registro_cb->periodo=$request->periodo;
            $registro_cb->conciliacion_id=$request->conciliacion_id;
            $registro_cb->save();

            $registros=$registros+1;
           }
        fclose($fp);
        Conciliacion::where('id',$request->conciliacion_id)
                ->update(['charge_back_att'=>true]);
        $respuesta['registros']=$registros;
        return json_encode($respuesta);
    }
    public function cargar_factura_distribuidor(Request $request)
    {
        //return($request);
        $request->validate([
            'pdf_file' => 'required|mimes:pdf',
            'xml_file' => 'required|mimes:xml',
            'clabe'=>'required|digits:18',
            'titular'=>'required|max:50',
           ]);

        $upload_path = public_path('facturas');
        $file_name = $request->file("pdf_file")->getClientOriginalName();
        $generated_new_name_pdf = $request->numero_distribuidor.'_'.$request->calculo_id.'_'.time() . '.' . $request->file("pdf_file")->getClientOriginalExtension();
        $request->file("pdf_file")->move($upload_path, $generated_new_name_pdf);

        $upload_path = public_path('facturas');
        $file_name = $request->file("xml_file")->getClientOriginalName();
        $generated_new_name_xml = $request->numero_distribuidor.'_'.$request->calculo_id.'_'.time() . '.' . $request->file("xml_file")->getClientOriginalExtension();
        $request->file("xml_file")->move($upload_path, $generated_new_name_xml);

        $ultimo_limite=CalculoDistribuidores::orderBy('id','desc')->get()->first()->fecha_limite;
        if(now()>=$ultimo_limite)
        {
            return(back()->withErrors(['error', 'Fuera del limite de facturacion']));
        }

        PaymentDistribuidor::where('calculo_id',$request->calculo_id)
                            ->where('numero_distribuidor',$request->numero_distribuidor)
                            ->update([
                                'pdf'=>$generated_new_name_pdf,
                                'xml'=>$generated_new_name_xml,
                                'clabe'=>$request->clabe,
                                'titular'=>$request->titular,
                                'carga_factura'=>now()->toDateTimeString(),
                            ]);

        return(back()->withStatus('Datos de facturacion OK'));
    }
}