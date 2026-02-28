<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Attendance extends Model
{
    protected $fillable = ['user_id','work_date','clock_in','clock_out'];
    protected $casts = [
        'work_date' => 'date',
        'clock_in' => 'datetime',
        'clock_out' => 'datetime',
    ];

    public function breaks()
    {
        return $this->hasMany(\App\Models\BreakTime::class, 'attendance_id');
    }

    public function latestOpenBreak(): ?BreakTime
    {
        return $this->breaks()->whereNull('break_end')->latest('break_start')->first();
    }

    public function statusLabel(): string
    {
        if (!$this->clock_in) return '勤務外';
        if ($this->clock_out) return '退勤済';
        if ($this->latestOpenBreak()) return '休憩中';
        return '出勤中';
    }
}
