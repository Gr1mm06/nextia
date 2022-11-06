<?php

namespace App\Http\Controllers;

use Exception as ExceptionAlias;
use App\Http\Controllers\Controller;
use App\Models\BienesModel;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;

class BienesController extends Controller
{
    protected $user;
    public function __construct(Request $request)
    {
        $token = $request->header('Authorization');
        if($token != '')
            /*En caso de que requiera autentifiación la ruta obtenemos el usuario y lo almacenamos 
            en una variable*/
            $this->user = JWTAuth::parseToken()->authenticate();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    //Funcion la cual guarda la informacion de los bienes
    public function store(Request $request)
    {
        //Utilizamos try catch de entrada para cualquier tipo de error lo podamos visualizar
        try {
            $data = $request->only('articulo', 'descripcion', 'id_usuario');
      
            $validator = Validator::make($data, [
                'articulo' => 'required|string',
                'descripcion' => 'required|string',
                'id_usuario' => 'required',
            ]);

            //Le pasamos los valores a la funcion que esta declarada en el modelo Bienes
            BienesModel::crearBien(
                $request->articulo, 
                $request->descripcion, 
                $request->id_usuario
            );

            //Obtenemos los datos del usuario
            $user = JWTAuth::authenticate($request->token);

            return response()->json([
                'message' => 'Bien creado',
                'user' => $user
            ], Response::HTTP_OK);
            
        } catch (ExceptionAlias $e) {
            //Mostramos cualquier error que pueda surgir
            return response()->json([
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    //funcion para actualizar los registros
    public function update(Request $request, $id)
    {
        try {
            $data = $request->only('articulo', 'descripcion', 'id_usuario');
      
            $validator = Validator::make($data, [
                'articulo' => 'required|string',
                'descripcion' => 'required|string',
                'id_usuario' => 'required',
            ]);

            //Pasamos informacion a la funcion actualizarBien que se declaro en el modelo bienes
            BienesModel::actualizarBien(
                $id, 
                $request->articulo, 
                $request->descripcion, 
                $request->id_usuario
            );

            $user = JWTAuth::authenticate($request->token);

            return response()->json([
                'message' => 'Bien actualizado',
                'user' => $user
            ], Response::HTTP_OK);
            
        } catch (ExceptionAlias $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    //Eliminar registro
    public function destroy(Request $request,$id)
    {
        try {
            $user = JWTAuth::authenticate($request->token);

            BienesModel::borrarBien($id);

            return response()->json([
                'message' => 'Bien eliminado',
                'user' => $user
            ], Response::HTTP_OK);
            
        } catch (ExceptionAlias $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    //Funcion de consulta para obtener informacion requerida
    public function detalleBienes()
    {
        try {
            $arrayBienes = array();
            $id_bienes = request('id');
            //hacemos explode a la informacion para que nos la convierta en un array
            $id_bienes = explode( ',', $id_bienes);

            $bienes = BienesModel::getBienes($id_bienes);

            //Lo metemos a un ciclo para poder armar el array
            foreach ($bienes as $b) {
                $arrayBienes[] = 
                [
                    'id' => $b->id,
                    'articulo' => $b->articulo,
                    'descripcion' => $b->descripcion,
                    'id_usuario' => $b->id_usuario,
                ];
            }
            
            //Devolvemos el array
            return response()->json([
                'detalle' => $arrayBienes,
            ], Response::HTTP_OK);
            
        } catch (ExceptionAlias $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
