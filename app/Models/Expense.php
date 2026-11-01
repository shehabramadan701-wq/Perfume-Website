<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    public const CATEGORIES = ['General', 'Stock', 'Marketing', 'Shipping', 'Rent', 'Salaries', 'Fees'];

    protected $fillable = ['title', 'category', 'amount', 'spent_at', 'notes'];

    protected $casts = [
        'amount' => 'decimal:2',
        'spent_at' => 'date',
    ];
}
