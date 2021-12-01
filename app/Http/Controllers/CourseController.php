<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\Course;

class CourseController extends Controller
{
    /**
     * Registrar nuevo usuario en la bbdd
     */
    public function register(Request $req){

        $response = ["status" => 1, "msg" => ""];
                
        $data = $req->getContent();
        $data = json_decode($data);
       
        $course = new Course();

        $course->title = $data->title;
        $course->description = $data->description;
        $course->photo = $data->photo;        

        try {
            $course->save();
            $response
            ['msg'] = "Curso " .$course->title. " guardado";
        } catch(\Exception $e){
            $response
            ['msg'] = $e->getMessage();
            $response
            ['status'] = 0;
        }

        return response()->json($response);
    }

    /**
     * Buscar un curso, si se pasa el parametro "filter", se puede buscar un curso concreto, sin necesidad de incluir el nombre completo
     */
    public function search(Request $req){

        $response = ["status" => 1, "msg" => ""];

        if($req->has('filter')){
            $filter = $req->input('filter');
        } else {
            $filter = "";
        }
           
        // Se puede utilizar el parametro "filter" para buscar un curso concreto
        try {
            $courses = DB::table('courses')
                        ->leftJoin('videos', 'courses.id', '=', 'videos.course_id')
                        ->selectRaw('courses.title, courses.photo, COUNT(videos.id) as "videos"')
                        ->where('courses.title', 'LIKE', '%' .$filter. '%')
                        ->groupBy('courses.title')
                        ->get();
            $response['msg'] = $courses;
        } catch(\Exception $e){
            $response
            ['msg'] = $e->getMessage();
            $response
            ['status'] = 0;
        }

        return response()->json($response);
    }
}
