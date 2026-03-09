<?php

namespace Tests\Feature\Attendance;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Attendance;
use Tests\TestCase;

class AttendanceStampTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    use RefreshDatabase;

    /**
     *@test
     *出勤ボタンが正しく機能する
     */
    public function test_user_can_clock_in()
    {
        $user = User::factory()->create([
            'email_verified_at' => now()
        ]);

        $response = $this->actingAs($user)->post('/attendance/clock-in');

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertSee('出勤中');

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id
        ]);
    }

    /**
     *@test
     *出勤は一日一回のみできる
     */
    public function test_user_cannot_clock_in_twice()
    {
        $user = User::factory()->create([
            'email_verified_at' => now()
        ]);

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_in' => now(),
            'clock_out' => now()
        ]);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertDontSee('>出勤<');
    }

    /**
     *@test
     *出勤時刻が勤怠一覧画面で確認できる
     */
    public function test_clock_in_time_is_visible_on_attendance_list()
    {
        $user = User::factory()->create([
            'email_verified_at' => now()
        ]);

        $this->actingAs($user)->post('/attendance/clock-in');

        $response = $this->actingAs($user)->get('/attendance/list');

        $response->assertSee(Carbon::now()->format('H'));
    }

    /**
     *@test
     *休憩入ボタンが正しく機能する
     */
    public function test_user_can_start_break()
    {
        $user = User::factory()->create([
            'email_verified_at' => now()
        ]);

        $attendance = Attendance::create([
            'user_id'=>$user->id,
            'work_date'=>today(),
            'clock_in'=>now()
        ]);

        $this->actingAs($user)->post('/attendance/break-in');

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertSee('休憩中');
    }

    /**
     *@test
     *休憩は一日に何回でもできる
     */
    public function test_user_can_take_multiple_breaks()
    {
        $user = User::factory()->create([
            'email_verified_at' => now()
        ]);

        Attendance::create([
            'user_id'=>$user->id,
            'work_date'=>today(),
            'clock_in'=>now()
        ]);

        $this->actingAs($user)->post('/attendance/break-in');
        $this->actingAs($user)->post('/attendance/break-out');

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertSee('休憩入');
    }

    /**
     *@test
     *休憩戻ボタンが正しく機能する
     */
    public function test_user_can_end_break()
    {
        $user = User::factory()->create([
            'email_verified_at' => now()
        ]);

        $attendance = Attendance::create([
            'user_id'=>$user->id,
            'work_date'=>today(),
            'clock_in'=>now()
        ]);

        $this->actingAs($user)->post('/attendance/break-in');
        $this->actingAs($user)->post('/attendance/break-out');

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertSee('出勤中');
    }

    /**
     *@test
     *休憩時刻が勤怠一覧画面で確認できる
     */
    public function test_break_time_is_visible_on_attendance_list()
    {
        $user = User::factory()->create([
            'email_verified_at' => now()
        ]);

        Attendance::create([
            'user_id'=>$user->id,
            'work_date'=>today(),
            'clock_in'=>now()
        ]);

        $this->actingAs($user)->post('/attendance/break-in');
        $this->actingAs($user)->post('/attendance/break-out');

        $response = $this->actingAs($user)->get('/attendance/list');

        $response->assertSee(Carbon::now()->format('H'));
    }

    /**
     *@test
     *退勤ボタンが正しく機能する
     */
    public function test_user_can_clock_out()
    {
        $user = User::factory()->create([
            'email_verified_at' => now()
        ]);

        Attendance::create([
            'user_id'=>$user->id,
            'work_date'=>today(),
            'clock_in'=>now()
        ]);

        $this->actingAs($user)->post('/attendance/clock-out');

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertSee('退勤済');
    }

    /**
     *@test
     *退勤時刻が勤怠一覧画面で確認できる
     */
    public function test_clock_out_time_is_visible_on_attendance_list()
    {
        $user = User::factory()->create([
            'email_verified_at' => now()
        ]);

        $this->actingAs($user)->post('/attendance/clock-in');
        $this->actingAs($user)->post('/attendance/clock-out');

        $response = $this->actingAs($user)->get('/attendance/list');

        $response->assertSee(Carbon::now()->format('H'));
    }
}
