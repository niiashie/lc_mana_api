<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    public function book()
    {
        return $this->hasOne(Book::class, "id","book_id");
    }

    public function user()
    {
        return $this->hasOne(User::class,"id", "user_id");
    }
}
