<?php

namespace App\Http\Controllers;

use App\Models\BookBorrowDetails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class BookBorrowDetailsController extends Controller
{
    //create data start
    //cuma detail aja
   
    public function detail($id){
        if(DB::table('book_borrow_details')->where('book_borrow_detail_id', $id)->exists()){
            $detail = DB::table('book_borrow_details')
            ->select('book_borrow_details.*')
            ->join('book_borrow', 'book_borrow.book_borrow_id', '=', 'book_borrow_details.book_borrow_id')
            ->join('book', 'book.book_id', '=', 'book_borrow_details.book_id')
            ->get();
            return Response()->json($detail);
        }else {
            return Response()->json(['message'=>'Couldnt find the data']);
        }
    }
    //read data end
}
