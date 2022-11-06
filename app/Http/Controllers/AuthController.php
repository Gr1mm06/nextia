<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use JWTAuth;
use App\Models\User;
use App\Models\BienesModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
     //Función que utilizaremos para registrar al usuario
     public function register(Request $request)
     {
        $handle = fopen('file.csv', "r");
        $header = true;
         //Indicamos que solo queremos recibir nombre, usuario y password de la request
         $data = $request->only('nombre', 'usuario', 'password');
      
         //Realizamos las validaciones
         $validator = Validator::make($data, [
             'nombre' => 'required|string',
             'usuario' => 'required',
             'password' => 'required|string|min:6|max:50',
         ]);
        

         //Devolvemos un error si fallan las validaciones
         if ($validator->fails()) {
             return response()->json(['error' => $validator->messages()], 400);
         }


         //Creamos el nuevo usuario
         $user = User::create([
             "nombre" => $request->nombre,
             "usuario" => $request->usuario,
             "password" => bcrypt($request->password)
         ]);

         //Se carga el archivo al momento de crear el usuario(aqui mi duda era si cada vez o no, lo deje asi pero si fue una duda que tuve)
         while ($csvLine = fgetcsv($handle, 0, ",")) {
            if ($header) {
                $header = false;
            } else {
                BienesModel::create([
                    'id' => $csvLine[0],
                    'articulo' => $csvLine[1],
                    'descripcion' => $csvLine[2],
                    'id_usuario' => $user['id']
                ]);
            }
        }

         //Nos guardamos el usuario y la contraseña para realizar la petición de token a JWTAuth
         $credentials = $request->only("usuario", 'password');

         //Devolvemos la respuesta con el token del usuario
         return response()->json([
             'message' => 'Usuario creado.',
             'token' => JWTAuth::attempt($credentials),
             'user' => $user
         ], Response::HTTP_OK);
     }

     //Funcion que utilizaremos para hacer login
    public function authenticate(Request $request)
    {
        //Indicamos que solo queremos recibir usuario y password de la request
        $credentials = $request->only('usuario', 'password');
        //Validaciones
        $validator = Validator::make($credentials, [
            'usuario' => 'required|string',
            'password' => 'required|string|min:6|max:50'
        ]);

        //Devolvemos un error de validación en caso de fallo en las verificaciones
        if ($validator->fails()) {
            return response()->json(
                [
                    'error' => $validator->messages()
                ],
                 400
                );
        }

        //Intentamos hacer login
        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                //Credenciales incorrectas.
                return response()->json([
                    'message' => 'Login failed',
                ], 401);
            }
        } catch (JWTException $e) {
           
            return response()->json([
                'message' => 'Error',
            ], 500);
        }

        //Devolvemos el token
        return response()->json([
            'token' => $token,
            'user' => Auth::user()
        ]);
    }

    //Función que utilizaremos para eliminar el token y desconectar al usuario
    public function logout(Request $request)
    {
        //Validamos que se nos envie el token
        $validator = Validator::make(
            $request->only('token'), 
            [
                'token' => 'required'
            ]
        );
        //Si falla la validación
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 400);
        }
        try {
            //Si el token es valido eliminamos el token desconectando al usuario.
            JWTAuth::invalidate($request->token);
            return response()->json(
                [
                    'success' => true,
                    'message' => 'Usuario desconectado'
                ]
            );
        } catch (JWTException $exception) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Error'
                ], 
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
