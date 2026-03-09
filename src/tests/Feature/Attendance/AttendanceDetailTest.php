<?php

namespace Tests\Feature\Attendance;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;
use Tests\TestCase;

class AttendanceDetailTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    use RefreshDatabase;

    /**
     *@test
     *勤怠詳細画面の「名前」がログインユーザーの氏名になっている
     */
    public function test_attendance_detail_shows_logged_in_user_name()
    {
        $user = User::factory()->create([
            'name' => '山田太郎',
            'email_verified_at' => now(),
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_in' => now(),
        ]);

        $response = $this->actingAs($user)->get("/attendance/detail/{$attendance->id}");

        $response->assertStatus(200);
        $response->assertSee('山田太郎');
    }

    /**
     *@test
     *勤怠詳細画面の「日付」が選択した日付になっている
     */
    public function test_attendance_detail_shows_selected_date()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-03-09',
            'clock_in' => now(),
        ]);

        $response = $this->actingAs($user)->get("/attendance/detail/{$attendance->id}");

        $response->assertStatus(200);
        $response->assertSee('2026年3月9日');
    }

    /**
     *@test
     *出勤・退勤欄にログインユーザーの打刻時間が表示されている
     */
    public function test_attendance_detail_shows_clock_in_and_clock_out_time()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_in' => '2026-03-09 09:00:00',
            'clock_out' => '2026-03-09 18:00:00',
        ]);

        $response = $this->actingAs($user)->get("/attendance/detail/{$attendance->id}");

        $response->assertStatus(200);
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    /**
     *@test
     *休憩欄にログインユーザーの打刻時間が表示されている
     */
    public function test_attendance_detail_shows_break_time()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_in' => '2026-03-09 09:00:00',
            'clock_out' => '2026-03-09 18:00:00',
        ]);

        $attendance->breaks()->create([
            'break_start' => '2026-03-09 12:00:00',
            'break_end' => '2026-03-09 13:00:00',
        ]);

        $response = $this->actingAs($user)->get("/attendance/detail/{$attendance->id}");

        $response->assertStatus(200);
        $response->assertSee('12:00');
        $response->assertSee('13:00');
    }


}
