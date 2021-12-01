<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\Video;

class VideoController extends Controller
{
    /**
     * Registrar nuevo vídeo en la bbdd
     */
    public function upload(Request $req){

        $response = ["status" => 1, "msg" => ""];
                
        $data = $req->getContent();
        $data = json_decode($data);
       
        $video = new Video();

        $video->title = $data->title;
        $video->photo = $data->photo;
        $video->video_link = $data->video_link;
        $video->course_id = $data->course_id;

        try {
            $video->save();
            $response
            ['msg'] = "Vídeo " .$video->title. " guardado";
        } catch(\Exception $e){
            $response
            ['msg'] = $e->getMessage();
            $response
            ['status'] = 0;
        }

        return response()->json($response);
    }

    /**
     * Registrar vídeo visto y mostrar info del vídeo
     */
    public function watch(Request $req){
        $response = ["status" => 1, "msg" => "", "query" => ""];
                
        // Se utiliza el parametro "user_id" para seleccionar el usuario
        if($req->has('user_id')){
            $user = $req->input('user_id');
        } else {
            $user = "";
        }

        // Se utiliza el parametro "video_id" para registrar el vídeo que ha visto el usuario
        if ($req->has('video_id')){
            $video = $req->input('video_id');
        }else {
            $video = "";
        }
           
        if($user != "" && $video != ""){
             try{
                // Conocer de que curso es el vídeo seleccionado
                $course = DB::table('videos')
                            ->where('id', '=', $video)
                            ->first();
                // Comprobar si el usuario tiene acceso al vídeo
                if($course){
                    $joined = DB::table('user_course')
                                ->where('user_id', '=', $user)
                                ->where('course_id', '=', $course->course_id)
                                ->first();
                } else {
                    $joined = false;
                }

                // Query para saber si el usuario ya ha visto el vídeo con anterioridad
                $watched = DB::table('user_video')
                            ->where('user_id', '=', $user)
                            ->where('video_id', '=', $video)
                            ->first(); 

                 // Si no tiene acceso al vídeo, se notifica
                 if(!$joined){
                     $respuesta['msg'] = "El usuario no tiene acceso a este video";                
                 }else {
                     // Si tiene acceso al curso, se comprueba si lo vió con anterioridad
                     if($joined && $watched){
                         $videoInfo = DB::table('videos')
                            ->where('id', '=', $video)
                            ->select('id', 'video_link')
                            ->first();

                            $respuesta["query"] = $videoInfo;
                            $respuesta['msg'] = "El usuario ya vio el video con anterioridad.";
                    // Si el usuario tiene acceso al curso y no ha visto el vídeo con anterioridad...
                     } else if ($joined && !$watched) {
                        DB::table('user_video')->insert([
                            'user_id' => $user,
                            'video_id' => $video,
                            'created_at' => \Carbon\Carbon::now(),
                            "updated_at" => \Carbon\Carbon::now()
                        ]);

                        $videoInfo = DB::table('videos')
                            ->where('id', '=', $video)
                            ->select('id', 'video_link')
                            ->first();

                            $respuesta["query"] = $videoInfo;
                            $respuesta['msg'] = "Se registra video como visto.";
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
             } else if ($video == ""){
                 $respuesta["status"] = 2;
                 $respuesta["msg"] = "Vídeo no encontrado, por favor introduzca un ID correcto.";
             }
         }
 
         return response()->json($respuesta);
    }

    /**
     * Listar videos de un curso y ver si estos se han visto
     */
    public function listVideos(Request $req){
        $response = ["status" => 1, "msg" => "", "query" => ""];
                
        // Se utiliza el parametro "user_id" para seleccionar el usuario
        if($req->has('user_id')){
            $user = $req->input('user_id');
        } else {
            $user = "";
        }
        // Se utiliza el parametro "course_id" para seleccionar el curso que se quiere ver
        if ($req->has('course_id')){
            $course = $req->input('course_id');
        }else {
            $course = "";
        }

        if($user != "" && $course != ""){
             try{
                // Comprobar si el usuario tiene acceso al vídeo
                if($course){
                    $joined = DB::table('user_course')
                                ->where('user_id', '=', $user)
                                ->where('course_id', '=', $course)
                                ->first();
                } else {
                    $joined = false;
                }
                 // Si no tiene acceso al vídeo, se notifica
                 if(!$joined){
                     $respuesta['msg'] = "El usuario no tiene acceso a este video";                
                 }else if($joined){
                    // Listar los videos de un curso concreto de un usuario y mostrar cuando lo ha visto
                    $videos = DB::table('videos')
                    ->leftJoin('user_video', 'videos.id', '=', 'user_video.video_id')
                    ->select('videos.title', 'videos.photo', 'user_video.created_at as visto')
                    ->where('user_video.user_id', '=', $user)
                    ->where('videos.course_id', '=', $course)
                    ->orWhereNotExists(function($query)
                    {
                        $query->select(\DB::raw('*'))
                            ->from('user_video')
                            ->whereRaw('videos.id = user_video.video_id');
                    })->where('videos.course_id', '=', $course)
                    ->get();

                    $respuesta['msg'] = $videos;
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
                 $respuesta["msg"] = "Vídeo no encontrado, por favor introduzca un ID correcto.";
             }
         }
 
         return response()->json($respuesta);
    }
}
