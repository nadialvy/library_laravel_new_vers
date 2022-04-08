<?php

namespace App\Http\Controllers;
use App\Models\BookReturn;
use App\Models\BookBorrow;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class BookReturnController extends Controller
{
    //create data start
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'book_borrow_id' => 'required'
        ]);

        if($validator->fails()){
            return Response()->json($validator->errors());
        }

        $borrowCheck = BookReturn::where('book_borrow_id', '=', $request->book_borrow_id);
        if($borrowCheck->count() == false){
            $data_borrow = BookBorrow::where('book_borrow_id', '=', $request -> book_borrow_id)->first(); 
            
            $date_of_returning = Carbon::parse($data_borrow->date_of_returning); //parse date returnng from db to carbon
            $current_date = Carbon::parse(date('Y-m-d')); //get current date

            $fine_per_day = 2000; //initialize fine pe day

            if(strtotime($current_date) > strtotime($date_of_returning)){ //check if current date is greater than date of returning
                $total_days = $date_of_returning->diffInDays($current_date);
                $fine = $total_days * $fine_per_day;
            } else {
                $fine = 0;
            }
            
            $store = BookReturn::create([
                'book_borrow_id' => $request->book_borrow_id,
                'date_of_returning' => $current_date,
                'fine' => $fine
            ]);

            $data = BookReturn::where('book_borrow_id', '=', $request->book_borrow_id)->first();
            if(strtotime($current_date) > strtotime($date_of_returning)){ //check if current date is greater than date of returning
                $total_days = $date_of_returning->diffInDays($current_date);
            } else {
                $total_days = 0;
            }
            if($store){
                $data_return = ([
                    'status' => 1,
                    'message' => 'Succes create new data!',
                    'late for(days)' => $total_days,
                    'data' => $data
                ]);
            }else {
                $data_return = ([
                    'status' => 0,
                    'message' => 'Failed create new data!'
                ]);
            }
        }
        else {
            $data_return = [
                'status' => 0,
                'message' => 'The book is already returned'
            ];
        }

        return Response()->json($data_return);

    }
    //create data end

    //read data start
    public function show(){
        $data = DB::table('book_return')
        ->select('book_return.*', 'students.student_name', 'grade.class_name')
        ->join('book_borrow', 'book_borrow.book_borrow_id', '=', 'book_return.book_borrow_id')
        ->join('students', 'students.student_id', '=', 'book_borrow.student_id')
        ->join('grade', 'grade.class_id', '=', 'students.class_id')
        ->get();

        return Response()->json($data);
    }

    public function detail($id){
        if(DB::table('book_borrow')->where('book_borrow_id', $id)->exists()){ //id 30
            $detail = DB::table('book_borrow')
            ->select( 'book_return.*')
            ->join('book_return', 'book_return.book_borrow_id', '=', 'book_borrow.book_borrow_id')
            ->where('book_borrow.book_borrow_id', $id)
            ->first();

            return Response()->json([
                $detail
            ]);
        }else{
            return Response()->json(['message' => 'Couldnt find the data']);
        }
    
    }
    //read data end

    //update data start
    public function update($id, Request $request){
        $validator=Validator::make($request->all(),
        [
            'book_borrow_id' => 'required',
            'date_of_returning' => 'required',
            'fine' => 'required'
        ]);

        if($validator->fails()){
            return Response()->json($validator->errors());
        }

        $update=DB::table('book_return')
        ->where('book_return_id', '=', $id)
        ->update([
            'book_borrow_id' => $request->book_borrow_id,
            'date_of_returning' => $request->date_of_returning,
            'fine' => $request->fine
        ]);

        $data=BookReturn::where('book_return_id', '=', $id)->get();
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
        $delete = DB::table('book_return')
        ->where('book_return_id', '=', $id)
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
