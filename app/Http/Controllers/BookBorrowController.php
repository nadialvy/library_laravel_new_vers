<?php

namespace App\Http\Controllers;

use App\Models\BookBorrow;
use App\Models\BookBorrowDetails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class BookBorrowController extends Controller
{
    //create data start 
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'student_id' => 'required',
            'date_of_borrowing' => 'required',
            'date_of_returning'  => 'required',
        ]);

        if($validator->fails()){
            return Response() -> json($validator->errors());
        }

        //insert borrow
        $borrow = new BookBorrow();
        $borrow->student_id = $request->student_id;
        $borrow->date_of_borrowing = $request->date_of_borrowing;
        $borrow->date_of_returning = $request->date_of_returning;
        $borrow->save();

        //insert detail
        for($i = 0; $i < count($request->detail); $i++){
            $detail = new BookBorrowDetails();
            $detail->book_borrow_id = $borrow->book_borrow_id;
            $detail->book_id = $request->detail[$i]['book_id'];
            $detail->qty = $request->detail[$i]['qty'];
            $detail->save();
        }

        $dataBook = BookBorrow::where('book_borrow_id', $borrow->book_borrow_id)->first();
        $dataDetails = BookBorrowDetails::where('book_borrow_id', $borrow->book_borrow_id)->get();
        if($borrow && $detail){
            return Response() -> json([
                'status' => 1,
                'message' => 'Succes create new data!',
                'data borrow' => $dataBook,
                'detail borrow' => $dataDetails
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

    //read data start
    public function show(){
        $data = DB::table('book_borrow')
        ->select('book_borrow.book_borrow_id', 'book_borrow.student_id', 'students.student_name', 'book_borrow.date_of_borrowing', 'book_borrow.date_of_returning')
        ->join('students', 'students.student_id', '=', 'book_borrow.student_id')
        ->get(); 
        return Response()->json($data);
    }

    public function detail($id){
        if(DB::table('book_borrow')->where('book_borrow_id', $id)->exists()){
            $detail_book_borrow = DB::table('book_borrow')
            ->select('book_borrow.book_borrow_id', 'book_borrow.student_id', 'students.student_name', 'book_borrow.date_of_borrowing', 'book_borrow.date_of_returning')
            ->join('students', 'students.student_id', '=', 'book_borrow.student_id')
            ->where('book_borrow_id', $id)
            ->get();
            return Response()->json($detail_book_borrow);
        }else {
            return Response()->json(['message' => 'Couldnt find the data']);
        }
    }
    //read data end

    //update data start
    public function update($id, Request $request){
        $validator=Validator::make($request->all(),
        [
            'student_id' => 'required',
            'date_of_borrowing' => 'required',
            'date_of_returning'  => 'required'
        ]);

        if($validator->fails()){
            return Response()->json($validator->errors());
        }

        $update=DB::table('book_borrow')
        ->where('book_borrow_id', '=', $id)
        ->update([
            'student_id' => $request->student_id,
            'date_of_borrowing' => $request->date_of_borrowing,
            'date_of_returning' => $request->date_of_returning
        ]);

        $data=BookBorrow::where('book_borrow_id', '=', $id)->get();
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
        $delete=DB::table('book_borrow')
        ->where('book_borrow_id', '=', $id)
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
