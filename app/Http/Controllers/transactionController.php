<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BookBorrow;
use App\Models\BookReturn;
use App\Models\BookBorrowDetails;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use DB;

class transactionController extends Controller
{
    //create data for Book Borrow
    public function bookBorrow(Request $request)
    {
        //request data to use
        $validator = Validator::make($request->all(), [
            'student_id' => 'required',
            'date_of_borrowing' => 'required',
            'date_of_returning'  => 'required'
        ]);

        if($validator->fails()){
            return Response() -> json($validator->errors());
        }

        //store input data to database
        $store = BookBorrow::create([
            'student_id' => $request->student_id,
            'date_of_borrowing' => $request->date_of_borrowing,
            'date_of_returning' => $request->date_of_returning
        ]);

        //make a nice return form 
        $data = BookBorrow::where('student_id', '=', $request->student_id)->first();
        if($store){
            return Response() -> json([
                'status' => 1,
                'message' => 'Succes create new data!',
                'data' => $data
            ]);
        }else
        {
            return Response() -> json([
                'status' => 0,
                'message' => 'Failed create new data!'
            ]);
        }
    }
    //create data end

    //create data on detail borrow
    public function detailBorrow(Request $request, $id)
    {
        $validator = Validator::make($request->all(),[
            'book_id' => 'required',
            'qty' => 'required'
        ]);

        if($validator->fails()){
            return Response() -> json($validator->errors());
        }

        $store = BookBorrowDetails::create([
            'book_borrow_id' => $id,
            'book_id' => $request->book_id,
            'qty' => $request->qty
        ]);

        $data = BookBorrowDetails::where('book_borrow_id', '=', $id)->first();
        if($store){ 
            return Response()->json([
                'status' => 1,
                'message' => 'Succes create new data!',
                'data' => $data
            ]);
        }else {
            return Response() -> json([
                'status' => 0,
                'message' => 'Failed create new data!'
            ]);
        }
    }

    //create data on returning book
    public function bookReturn(Request $request)
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
            
            $date_of_returning = Carbon::parse($data_borrow->date_of_returning);
            $current_date = Carbon::parse(date('Y-m-d'));            

            $fine_per_day = 1500;

            if(strtotime($current_date) > strtotime($date_of_returning)){ 
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
}
