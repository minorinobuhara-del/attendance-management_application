<?php

namespace Tests\Feature\Attendance;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;
use Tests\TestCase;

class AttendanceListTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    use RefreshDatabase;

    /**
     *@test
     *自分が行った勤怠情報が全て表示されている
     */
    public function test_user_can_see_all_of_their_attendance_records()
    {
        $user = User::factory()->create([
            'email_verified_at' => now()
        ]);

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2024-03-01',
            'clock_in' => now(),
        ]);

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2024-03-02',
            'clock_in' => now(),
        ]);

        $response = $this->actingAs($user)->get('/attendance/list');

        $response->assertStatus(200);

        $response->assertSee('03/01');
        $response->assertSee('03/02');
    }

    /**
     *@test
     *勤怠一覧画面に遷移した際に現在の月が表示される
     */
    public function test_current_month_is_displayed_on_attendance_list()
    {
        $user = User::factory()->create([
            'email_verified_at' => now()
        ]);

        $response = $this->actingAs($user)->get('/attendance/list');

        $month = Carbon::now()->format('Y/m');

        $response->assertStatus(200);
        $response->assertSee($month);
    }

    /**
     *@test
     *前月ボタンを押すと前月の勤怠情報が表示される
     */
    public function test_previous_month_button_shows_previous_month_data()
    {
        $user = User::factory()->create([
            'email_verified_at' => now()
        ]);

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => Carbon::now()->subMonth()->format('Y-m-d'),
            'clock_in' => now(),
        ]);

        $prevMonth = Carbon::now()->subMonth()->format('Y-m');

        $response = $this->actingAs($user)->get("/attendance/list?month={$prevMonth}");

        $response->assertStatus(200);

        $response->assertSee(Carbon::now()->subMonth()->format('Y/m'));
    }

    /**
     *@test
     *翌月ボタンを押すと翌月の勤怠情報が表示される
     */
    public function test_next_month_button_shows_next_month_data()
    {
        $user = User::factory()->create([
            'email_verified_at' => now()
        ]);

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => Carbon::now()->addMonth()->format('Y-m-d'),
            'clock_in' => now(),
        ]);

        $nextMonth = Carbon::now()->addMonth()->format('Y-m');

        $response = $this->actingAs($user)->get("/attendance/list?month={$nextMonth}");

        $response->assertStatus(200);

        $response->assertSee(Carbon::now()->addMonth()->format('Y/m'));
    }

    /**
     *@test
     *詳細ボタンを押すと勤怠詳細画面に遷移する
     */
    public function test_user_can_go_to_attendance_detail_page()
    {
        $user = User::factory()->create([
            'email_verified_at' => now()
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_in' => now(),
        ]);

        $response = $this->actingAs($user)->get("/attendance/detail/{$attendance->id}");

        $response->assertStatus(200);

        $response->assertSee('勤怠詳細');
    }
}
