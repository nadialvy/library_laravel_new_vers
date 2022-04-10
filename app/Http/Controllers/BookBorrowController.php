<?php

namespace App\Http\Controllers;

use App\Models\BookBorrow;
use App\Models\BookBorrowDetails;
use Carbon\Carbon;
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
            // 'date_of_borrowing' => 'required',
            // 'date_of_returning'  => 'required',
            'detail' => 'required',
        ]);

        if($validator->fails()){
            return Response() -> json($validator->errors());
        }

        //insert borrow
        $borrow = new BookBorrow();
        $borrow->student_id = $request->student_id;
        $borrow->date_of_borrowing = Carbon::now();
        $borrow->date_of_returning = Carbon::now()->addDays(7);
        // $borrow->date_of_borrowing = $request->date_of_borrowing;
        // $borrow->date_of_returning = $request->date_of_returning;
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
        ->select('book_borrow.*', 'students.student_name')
        ->join('students', 'students.student_id', 'book_borrow.student_id')
        ->whereNotIn('book_borrow_id', function($query){
            $query->select('book_borrow_id')
            ->from('book_return');
        })
        ->orderBy('book_borrow_id')
        ->get();
        // return Response() -> json([
        //     'data' => $data
        // ]);

        $data = DB::table('book_borrow')
        ->select('book_borrow.*', 'students.student_name')
        ->join('students', 'students.student_id', 'book_borrow.student_id')
        ->rightJoin('book_return', 'book_borrow.book_borrow_id', 'book_return.book_borrow_id')
        ->orderBy('book_borrow_id')
        ->get();

        // return Response()->json($data);
        $result = [];
        for($i = 0; $i < count($data); $i++){
            $result[$i]['student_name'] = $data[$i]->student_name;
            $result[$i]['date_of_returning'] = $data[$i]->date_of_returning;
            $result[$i]['date_of_borrowing'] = $data[$i]->date_of_borrowing;
            $result[$i]['book_borrow_id'] = $data[$i]->book_borrow_id;

            $status = '';
            $dateReturn = $data[$i]->date_of_returning;
            $current_date = Carbon::parse(date('Y-m-d')); 
            if(strtotime($current_date) > strtotime($dateReturn)){
                $status = 'Late';
            }else {
                $status = 'Not Late';
            }

            $result[$i]['status'] = $status;

        }
        return Response()->json($result);
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

    public function detailReturn($id){
        if(DB::table('book_borrow')->where('book_borrow_id', $id)->exists()){
            $data_borrow = BookBorrow::where('book_borrow_id', '=', $id)->first(); 
            return Response()->json($data_borrow);
            
            $date_of_returning = Carbon::parse($data_borrow->date_of_returning); //parse date returnng from db to carbon
            $current_date = Carbon::parse(date('Y-m-d')); //get current date

            $fine_per_day = 2000; //initialize fine pe day

            if(strtotime($current_date) > strtotime($date_of_returning)){ //check if current date is greater than date of returning
                $total_days = $date_of_returning->diffInDays($current_date);
                $fine = $total_days * $fine_per_day;

                return Response()->json([[
                    'fine' => $fine,
                    'late_for' =>$total_days
                ]]);
            } else {
                $fine = 0;

                return Response()->json([
                    'fine' => $fine,
                    'late for' => 'Not late'
                ]);
            }
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
