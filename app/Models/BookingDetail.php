<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingDetail extends Model
{
    use HasFactory;

    protected $table = 'booking_details';

    protected $fillable = ['booking_id', 'schedule_seat_id', 'passenger_name', 'passenger_phone', 'ticket_price'];

    protected $casts = [
        'ticket_price' => 'decimal:2',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function scheduleSeat()
    {
        return $this->belongsTo(ScheduleSeat::class);
    }
}
