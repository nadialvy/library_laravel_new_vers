<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookBorrow extends Model
{
    protected $table = 'book_borrow';
    protected $primaryKey = 'book_borrow_id'; //biar bisa query pakai model
    public $timestamps = true;

    protected $fillable = ['student_id', 'date_of_borrowing', 'date_of_returning'];

    public function student(){
        return $this->belongsTo('App\Models\Student', 'student_id', 'student_id');
    }
    // public function details(){
    //     return $this->belongsToMany('App\Models\BookBorrowDetails', 'book_borrow_id', 'book_borrow_id');
    // }
}
