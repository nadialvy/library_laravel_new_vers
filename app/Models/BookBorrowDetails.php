<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookBorrowDetails extends Model
{
    protected $table = 'book_borrow_details';
    protected $primaryKey = 'book_borrow_details_id';
    public $timestamps = true;

    protected $fillable = ['book_borrow_id', 'book_id', 'qty'];
    // public function details(){
    //     return $this->belongsToMany('App\Models\BookBorrowDetails', 'book_borrow_id', 'book_borrow_id');
    // }
    
}
