<?php

namespace Tests\Feature\Attendance;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;
use Tests\TestCase;

class AttendanceStatusTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    use RefreshDatabase;

    /**
     *@test
     *現在の日時情報がUIと同じ形式で出力されている
     */
    public function test_current_datetime_is_displayed_on_attendance_page()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)->get(route('attendance.index'));

        $today = Carbon::now()->translatedFormat('Y年n月j日');

        $response->assertStatus(200);
        $response->assertSee('attendance-date');
        $response->assertSee('attendance-time');
    }

    /**
     *@test
     *勤務外の場合、勤怠ステータスが「勤務外」と表示される
     */
    public function test_status_is_off_duty_when_no_attendance_record()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)->get(route('attendance.index'));

        $response->assertSee('勤務外');
    }

    /**
     *@test
     *出勤中の場合、勤怠ステータスが「出勤中」と表示される
     */
    public function test_status_is_working_when_clocked_in()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_in' => now(),
            'clock_out' => null,
        ]);

        $response = $this->actingAs($user)->get(route('attendance.index'));

        $response->assertSee('出勤中');
    }

    /**
     *@test
     *休憩中の場合、勤怠ステータスが「休憩中」と表示される
     */
    public function test_status_is_breaking_when_user_is_on_break()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_in' => now(),
            'clock_out' => null,
        ]);

        $attendance->breaks()->create([
            'break_start' => now(),
            'break_end' => null,
        ]);

        $response = $this->actingAs($user)->get(route('attendance.index'));

        $response->assertSee('休憩中');
    }

    /**
     *@test
     *退勤済の場合、勤怠ステータスが「退勤済」と表示される
     */
    public function test_status_is_finished_when_user_has_clocked_out()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_in' => now()->subHours(8),
            'clock_out' => now(),
        ]);

        $response = $this->actingAs($user)->get(route('attendance.index'));

        $response->assertSee('退勤済');
    }
}
