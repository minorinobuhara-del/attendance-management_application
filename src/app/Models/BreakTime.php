<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BreakTime extends Model
{
    protected $table = 'break_times';
    protected $fillable = ['attendance_id','break_start','break_end'];
    protected $casts = [
        'break_start' => 'datetime',
        'break_end' => 'datetime',
    ];

    public function attendance()
    {
        return $this->belongsTo(\App\Models\Attendance::class, 'attendance_id');
    }
}
