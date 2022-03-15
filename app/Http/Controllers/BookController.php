<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class BookController extends Controller
{
    
    //create data start
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(),
            [
                'book_name' => 'required',
                'author' => 'required',
                'desc' => 'required'
            ]
        );

        if($validator -> fails()) {
            return Response() -> json($validator->errors());
        }

        $store = Book::create([
            'book_name' =>$request->book_name,
            'author' => $request->author,
            'desc' => $request->desc
        ]);

        $data = Book::where('book_name', '=', $request->book_name)->get();
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
                'message' => 'Failed create data!'
            ]);
        }
    }
    //create data end

    //upload book cover
    public function upload_book_cover(Request $request, $id)
    {
        $validator = Validator::make($request->all(),
            [
                'book_cover' => 'required|file|mimes:jpeg,png,jpg,jfif|max:2048'
            ]
        );

        if($validator -> fails()) {
            return Response() -> json($validator->errors());
        }

        //rename book_cover to unique name
        $book_cover_name = time().'.'.$request->book_cover->extension();

        //process upload
        $request->book_cover->move(public_path('images'), $book_cover_name);
 
        $store=DB::table('book')
                ->where('book_id', '=', $id)
                ->update([
                    'image' =>$book_cover_name
                ]);

        $data = Book::where('book_id', '=', $request->$id)-> get();
        if($store){
            return Response() -> json([
                'status' => 1,
                'message' => 'Succes upload book cover!',
                'data' => $data
            ]);
        } else 
        {
            return Response() -> json([
                'status' => 0,
                'message' => 'Failed upload book cover!'
            ]);
        }
    }
    //create data end


    //read data start
    public function show(){
        return Book::all();
    }

    public function detail($id){
        if(DB::table('book')->where('book_id', $id)->exists()){
            $detail_book = DB::table('book')
                            ->select('book.*')
                            ->where('book_id', $id)
                            ->get();
                            return Response()->json($detail_book);
        }else {
            return Response()->json(['message' => 'Couldnt find the data']);
        }
    }
    //read data end

    //update data start
    public function update($id, Request $request){
        $validator=Validator::make($request->all(),
        [
            'book_name' => 'required',
            'author' => 'required',
            'desc' => 'required'
        ]);

        if($validator->fails()){
            return Response()->json($validator->errors());
        }

        $update=DB::table('book')
        ->where('book_id', '=', $id)
        ->update([
            'book_name' =>$request->book_name,
            'author' => $request->author,
            'desc' => $request->desc
        ]);

        $data=Book::where('book_id', '=', $id)->get();
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
        $delete=DB::table('book')
        ->where('book_id', '=', $id)
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
