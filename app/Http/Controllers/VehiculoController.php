<?php

namespace App\Http\Controllers;

use App\Models\Vehiculo;
use Illuminate\Http\Request;
use App\Models\Cliente;

class VehiculoController extends Controller
{
    public function createVehiculo(Request $request)
    {
        $request -> validate([
            'marca' => 'required',
            'color' => 'required',
            'modelo' => 'required',
            'placa' => 'required',
        ]);

        $id_usuario = auth() -> user() -> id;
        $id_cliente = Cliente::where('id_usuario', $id_usuario)->first();

        $vehiculo = new Vehiculo();
        $vehiculo -> id_cliente = $id_cliente -> id_cliente;
        $vehiculo -> marca = $request -> marca;
        $vehiculo -> color = $request -> color;
        $vehiculo -> modelo = $request -> modelo;

        $placa = $request -> placa;
        $placa = preg_replace('/[^A-Z0-9]/', '', $placa);
        $placa = str_replace(' ', '', $placa);
        $vehiculo -> placa = $placa;

        $vehiculo->save();

        //respuesta de la api

        return response([
            'status' => '1',
            'msg' => 'Vehiculo registrado',
        ]);
    }

    public function listVehiculo()
    {
        $id_usuario = auth()->user()->id;
        $id_cliente = (Cliente::where('id_usuario', $id_usuario)->first())->id_cliente;
        $vehiculos = Vehiculo::where('id_cliente', $id_cliente)
        ->where(function ($query) {
            $query->where('observacion', '!=', 'eliminado')
                  ->orWhereNull('observacion');
        })
        ->get();
    
        // Ocultar el campo "id_cliente" en cada vehículo
        $vehiculos->each(function ($vehiculo) {
            $vehiculo->makeHidden('id_cliente');
            $vehiculo->makeHidden('observacion');
        });
    
        return $vehiculos;
    }

    public function showVehiculo($id){
        $id_usuario = auth()->user()->id;
        $id_cliente = (Cliente::where('id_usuario', $id_usuario)->first())->id_cliente;
        if(Vehiculo::where(['id_cliente'=>$id_cliente, "id_vehiculo"=>$id]) -> exists()){
            $vehiculo = Vehiculo::where('id_vehiculo', $id)->first();
            $vehiculo->makeHidden('id_cliente');
            $vehiculo->makeHidden('observacion');
            return $vehiculo;
        }
    }

    public function updateVehiculo(Request $request, $id){
        $id_usuario = auth()->user()->id;
        $id_cliente = (Cliente::where('id_usuario', $id_usuario)->first())->id_cliente;
        if(Vehiculo::where(['id_cliente'=>$id_cliente, "id_vehiculo"=>$id]) -> exists()){
            $vehiculo = Vehiculo::where('id_vehiculo', $id)->first();

            $vehiculo->marca = isset($request->marca) ? $request->marca : $vehiculo->marca;
            $vehiculo->color = isset($request->color) ? $request->color : $vehiculo->color;
            $vehiculo->modelo = isset($request->modelo) ? $request->modelo : $vehiculo->modelo;
            $vehiculo->placa = isset($request->placa) ? $request->placa : $vehiculo->placa;
        
            $vehiculo->save();

            return response([
                'status' => '1',
                'msg' => 'Vehiculo modificado',
            ]);           
        }else{
            return response([
                'status' => '0',
                'msg' => 'Error',
            ],404);      
        }
    }

    public function deleteVehiculo($id){
        $id_usuario = auth()->user()->id;
        $id_cliente = (Cliente::where('id_usuario', $id_usuario)->first())->id_cliente;
        if(Vehiculo::where(['id_cliente'=>$id_cliente, "id_vehiculo"=>$id]) -> exists()){
            $vehiculo = Vehiculo::where('id_vehiculo', $id)->first();
            $vehiculo->observacion = 'eliminado';
            $vehiculo->save();

            return response([
                'status' => '1',
                'msg' => 'Vehiculo eliminado',
            ]);           
        }else{
            return response([
                'status' => '0',
                'msg' => 'Error',
            ],404);      
        }        
    }

    // public function deleteVehiculo($id){
    //     $id_usuario = auth()->user()->id;
    //     $id_cliente = (Cliente::where('id_usuario', $id_usuario)->first())->id_cliente;
    //     if(Vehiculo::where(['id_cliente'=>$id_cliente, "id_vehiculo"=>$id]) -> exists()){
    //         $vehiculo = Vehiculo::where(['id_cliente'=>$id_cliente, "id_vehiculo"=>$id])->first();
    //         $vehiculo->delete();

    //         return response([
    //             'status' => '1',
    //             'msg' => 'Vehiculo eliminado',
    //         ]);           
    //     }else{
    //         return response([
    //             'status' => '0',
    //             'msg' => 'Error',
    //         ],404);      
    //     }        
    // }

}
