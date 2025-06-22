<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $table = 'transactions';

    protected $fillable = [
        'user_id',
        'type',
        'amount',
        'description',
        'transaction_date',
    ];

    public function user() {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
