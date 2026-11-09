<?php

namespace App\Models;

use Database\Factories\CommentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    /** @use HasFactory<CommentFactory> */
    use HasFactory;

    // Comment

    protected $fillable = ['product_id', 'user_id', 'body'];

    public function product()
    {
        return $this->belongsTo(Product::class,'product_id');
    }

     public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }
}
