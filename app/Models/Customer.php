<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = ['name', 'email', 'phone', 'city', 'address', 'notes'];

    public function orders()
    {
        return $this->hasMany(Order::class)->latest();
    }

    /** Total money this customer actually paid. */
    public function getTotalSpentAttribute(): float
    {
        return (float) $this->orders()->where('payment_status', 'paid')->sum('total');
    }
}
