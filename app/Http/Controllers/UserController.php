<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\User;
use App\Models\Course;

class UserController extends Controller
{
    /**
     * Registrar nuevo usuario en la bbdd
     */
    public function register(Request $req){

        $response = ["status" => 1, "msg" => ""];
                
        $data = $req->getContent();
        $data = json_decode($data);
       
        $user = new User();

        $user->email = $data->email;
        $user->name = $data->name;
        $user->password = $data->password;        
        $user->photo = $data->photo;

        // Comprobar si el usuario ya está registrado
        $exists = User::where('email', '=', $data->email)->first();

        try {
            if(!$exists){
                $user->save();
                $response
                ['msg'] = "Usuario guardado con email ".$user->email;
            } else {
                $response
                ['msg'] = "Este correo electrónico ya está registrado: ".$user->email;
                $response
                ['status'] = 2;
            }
        } catch(\Exception $e){
            $response
            ['msg'] = $e->getMessage();
            $response
            ['status'] = 0;
        }

        return response()->json($response);
    }

    /**
     * Modificar usuario existente en la bbdd
     */
    public function modify(Request $req, $id){
        $respuesta = ["status" => 1, "msg" => ""];
        // Buscar el usuario a modificar
        $user = User::find($id);

        $data = $req->getContent();
        $data = json_decode($data);

       if($user){
           $dataChanged = false;
           $emailAttempt = false;

            if(isset($data->id)){
                $user->id = $data->id;
                $dataChanged = true;
            }

            if(isset($data->email)){
                $dataChanged = false;
                $emailAttempt = true;
            }

            if(isset($data->name)){
                $user->name = $data->name;
                $dataChanged = true;
            }

            if(isset($data->password)){
                $user->password = $data->password;
                $dataChanged = true;
            }

            if(isset($data->photo)){
                $user->photo = $data->photo;
                $dataChanged = true;
            }

            try{
                if($dataChanged){
                    $user->save();
                    if($emailAttempt) {
                        $respuesta['msg'] = "No se puede modificar el correo electrónico, el resto de datos se han guardado correctamente.";
                    } else {
                        $respuesta['msg'] = "Usuario modificado correctamente";
                    }
                }else {
                    if($emailAttempt) {
                        $respuesta["status"] = 3;
                        $respuesta['msg'] = "No se puede modificar el correo electrónico, no se modificaron datos.";
                    } else {
                        $respuesta["status"] = 3;
                        $respuesta["msg"] = "No se modificaron datos";
                    }
                    
                }
            }catch(\Exception $e){
                $respuesta['msg'] = $e->getMessage();
                $respuesta['status'] = 0;
                $respuesta['msg'] = "Se ha producido un error: ".$e->getMessage();
            }
        }else {
            $respuesta["status"] = 2;
            $respuesta["msg"] = "Usuario no encontrado, por favor introduzca un ID correcto.";
        }

        return response()->json($respuesta);
    }

    /**
     * Desactivar usuario
     */
    public function disable(Request $req, $id){
        $respuesta = ["status" => 1, "msg" => ""];
        // Buscar el usuario a desactivar
        $user = User::find($id);

       if($user){
           // Query para saber si el usuario está activado o desactivado
           $activated = DB::table('users')
                                ->where('id', '=', $id)
                                ->where('disabled', '=', 1)
                                ->first();

            // Si está activado, se desactiva
            if(!$activated){
                $user->disabled = 1;
            }

            try{
                if(!$activated){
                    $user->save();
                    $respuesta['msg'] = "Usuario desactivado correctamente";
                }else {
                    if($activated){
                        $respuesta["status"] = 3;
                        $respuesta["msg"] = "El usuario ya está desactivado, no se modificaron datos";
                    } else {
                        $respuesta["status"] = 3;
                        $respuesta["msg"] = "No se modificaron datos";
                    }
                }
            }catch(\Exception $e){
                $respuesta['msg'] = $e->getMessage();
                $respuesta['status'] = 0;
                $respuesta['msg'] = "Se ha producido un error: ".$e->getMessage();
            }
        }else {
            $respuesta["status"] = 2;
            $respuesta["msg"] = "Usuario no encontrado, por favor introduzca un ID correcto.";
        }

        return response()->json($respuesta);
    }

    /**
     * Activar usuario
     */
    public function enable(Request $req, $id){
        $respuesta = ["status" => 1, "msg" => ""];
        // Buscar el usuario a activar
        $user = User::find($id);

       if($user){
           // Query para saber si el usuario está activado o desactivado
           $activated = DB::table('users')
                                ->where('id', '=', $id)
                                ->where('disabled', '=', 0)
                                ->first();

            // Si está desactivado, se activa
            if(!$activated){
                $user->disabled = 0;
            }

            try{
                if(!$activated){
                    $user->save();
                    $respuesta['msg'] = "Usuario activado correctamente";
                }else {
                    if($activated){
                        $respuesta["status"] = 3;
                        $respuesta["msg"] = "El usuario ya está activado, no se modificaron datos";
                    } else {
                        $respuesta["status"] = 3;
                        $respuesta["msg"] = "No se modificaron datos";
                    }
                }
            }catch(\Exception $e){
                $respuesta['msg'] = $e->getMessage();
                $respuesta['status'] = 0;
                $respuesta['msg'] = "Se ha producido un error: ".$e->getMessage();
            }
        }else {
            $respuesta["status"] = 2;
            $respuesta["msg"] = "Usuario no encontrado, por favor introduzca un ID correcto.";
        }

        return response()->json($respuesta);
    }

    /**
     * Añadir usuario a un curso (unirse a un curso) 
     */
    public function join(Request $req){
        $respuesta = ["status" => 1, "msg" => ""];
        // Se utiliza el parametro "user_id" para seleccionar un usuario
        if($req->has('user_id')){
            $user = $req->input('user_id');
        } else {
            $user = "";
        }
        
        // Se utiliza el parametro "course_id" para registrar el usuario seleccionado en un curso
        if ($req->has('course_id')){
            $course = $req->input('course_id');
        }else {
            $course = "";
        }
        
       if($user != "" && $course != ""){
           // Query para saber si el usuario está ya unido o no al curso
           $joined = DB::table('user_course')
                                ->where('user_id', '=', $user)
                                ->where('course_id', '=', $course)
                                ->first();

            try{
                // Si no tiene este curso, se registra
                if(!$joined){
                    DB::table('user_course')->insert([
                        'user_id' => $user,
                        'course_id' => $course,
                        'created_at' => \Carbon\Carbon::now(),
                        "updated_at" => \Carbon\Carbon::now()
                    ]);
                    $respuesta['msg'] = "Registrado correctamente al curso";                
                }else {
                    // Si ya está registrado a ese curso se envía un mensaje para notificar
                    if($joined){
                        $respuesta["status"] = 3;
                        $respuesta["msg"] = "El usuario ya está registrado a este curso";
                    } else {
                        $respuesta["status"] = 3;
                        $respuesta["msg"] = "No se modificaron datos";
                    }
                }
            }catch(\Exception $e){
                $respuesta['msg'] = $e->getMessage();
                $respuesta['status'] = 0;
                $respuesta['msg'] = "Se ha producido un error: ".$e->getMessage();
            }
        }else {
            if($user == ""){
                $respuesta["status"] = 2;
                $respuesta["msg"] = "Usuario no encontrado, por favor introduzca un ID correcto.";
            } else if ($course == ""){
                $respuesta["status"] = 2;
                $respuesta["msg"] = "Curso no encontrado, por favor introduzca un ID correcto.";
            }
        }

        return response()->json($respuesta);
    }

    /**
     * Ver los cursos a los que está registrado un usuario
     */
    public function joined(Request $req, $id){
        $respuesta = ["status" => 1, "msg" => ""];
        // Buscar el usuario introducido
        $user = User::find($id);

        if ($user){
            try{
                // Query que muestra los cursos en los que está registrado el usuario utilizando su $id
                $joined = DB::table('user_course')
                            ->where('user_id', '=', $id)
                            ->get();
                // Si el usuario tiene cursos registrados, se muestran en la respuesta
                if(!$joined->isEmpty()){
                    $respuesta['msg'] = $joined;
                } else { // Si el usuario no tiene cursos registrados, se notifica
                    $respuesta['msg'] = "El usuario no tiene cursos registrados.";
                }
            }catch(\Exception $e){
                $respuesta['msg'] = $e->getMessage();
                $respuesta['status'] = 0;
                $respuesta['msg'] = "Se ha producido un error: ".$e->getMessage();
            }
        }else {
            if(!$user){
                $respuesta["status"] = 2;
                $respuesta["msg"] = "Usuario no encontrado, por favor introduzca un ID correcto.";
            }
        }

        return response()->json($respuesta);
    }
}
