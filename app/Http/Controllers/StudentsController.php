<?php

namespace App\Http\Controllers;

use App\Models\Students;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class StudentsController extends Controller
{
    //create data start
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), 
        [
            'student_name' => 'required',
            'date_of_birth' => 'required',
            'gender' => 'required',
            'address' => 'required',
            'class_id' => 'required'
        ]);

        if($validator -> fails()){
            return Response() -> json($validator -> errors());
        }

        $store = Students::create([
            'student_name' => $request->student_name,
            'date_of_birth' => $request->date_of_birth,
            'gender' => $request->gender,
            'address' => $request->address,
            'class_id' => $request->class_id
        ]);

        $data = Students::where('student_name', '=', $request->student_name)->get();
        if($store){
            return Response() -> json([
                'status' => 1,
                'message' => 'Succes create new data!',
                'data' => $data
            ]);
        } else 
        {
            return Response() -> json([
                'status' => 0,
                'message' => 'Failed create new data!'
            ]);
        }
    }

    public function upload_photo(Request $request, $id)
    {
        $validator = Validator::make($request->all(), 
        [
            'student_photo' => 'required|file|mimes:jpeg,png,jpg,jfif|max:2048'
        ]);

        if($validator -> fails()){
            return Response() -> json($validator -> errors());
        }

        //rename book_cover to unique name
        $student_photo_name = time().'.'.$request->student_photo->extension();

        //process upload
        $request->student_photo->move(public_path('student_images'), $student_photo_name);

        $store=DB::table('students')
                ->where('student_id', '=', $id)
                ->update([
                    'image' =>$student_photo_name
                ]);

        $data = Students::where('student_id', '=', $request->$id)-> get();
        if($store){
            return Response() -> json([
                'status' => 1,
                'message' => 'Succes upload student photo!',
                'data' => $data
            ]);
        } else 
        {
            return Response() -> json([
                'status' => 0,
                'message' => 'Failed upload student photo!'
            ]);
        }
    }
    //create data end

    //read data start
    public function show(){
        $data = DB::table('students')
                ->select('students.*', 'grade.class_name', 'grade.group')
                ->join('grade', 'grade.class_id', '=', 'students.class_id')
                ->get();

        // $data = Students::with(['grade'])->where('students.class_id', '=', 'grade.class_id')->get();
        return Response()->json($data);          
    }

    public function detail($id){
        if(DB::table('students')->where('student_id', $id)->exists()){
            $detail_student = DB::table('students')
            ->select('students.*')
            ->where('student_id', '=', $id)
            ->get();
            return Response() -> json($detail_student);
        } else {
            return Response()-> json(['message' => 'Could not find the data']);
        }
    }
    //read data end

    //update data start
    public function update($id, Request $request ){
        $validator = Validator::make($request->all(),
        [
            'student_name' => 'required',
            'date_of_birth' => 'required',
            'gender' => 'required',
            'address' => 'required',
            'class_id' => 'required'
        ]);

        if($validator -> fails()){
            return Response()->json($validator->errors());
        }

        $update=DB::table('students')
        ->where('student_id', '=', $id)
        ->update(
            [
                'student_name' => $request->student_name,
                'date_of_birth' => $request->date_of_birth,
                'gender' => $request->gender,
                'address' => $request->address,
                'class_id' => $request->class_id
            ]
        );

        $data=Students::where('student_id', '=', $id) ->get();
        if($update){
            return Response() -> json([
                'status' => 1,
                'message' => 'Success updating data!',
                'data' => $data  
            ]);
        } else {
            return Response() -> json([
                'status' => 0,
                'message' => 'Failed updating data!'
            ]);
        }

    }
    //update data end

    //delete data start
    public function delete($id){
        $delete = DB::table('students')
        ->where('student_id', '=', $id)
        ->delete();

        if($delete){
            return Response() -> json([
                'status' => 1,
                'message' => 'Succes delete data!'
        ]);
        } else {
            return Response() -> json([
                'status' => 0,
                'message' => 'Failed delete data!'
        ]);
        }

    }
    //delete data end

}
