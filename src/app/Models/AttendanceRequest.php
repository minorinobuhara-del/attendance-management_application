<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceRequest extends Model
{
    protected $fillable = ['attendance_id','user_id','status','payload'];
    protected $casts = ['payload' => 'array'];

    public function user() { return $this->belongsTo(User::class); }
    public function attendance() { return $this->belongsTo(Attendance::class); }
}
