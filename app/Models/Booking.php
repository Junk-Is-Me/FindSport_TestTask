<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = ['user_id'];

    public function user(){
        return $this->BelongsTo(User::class);
    }

    public function slot(){
        return $this->hasMany(BookingSlot::class);
    }

    public $timestamps = true;
}
