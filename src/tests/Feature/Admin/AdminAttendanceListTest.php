<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\Admin;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Tests\TestCase;

class AdminAttendanceListTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    use RefreshDatabase;

    /**
     *@test
     *その日になされた全ユーザーの勤怠情報が正確に確認できる
     */
    public function test_admin_can_see_all_users_attendance_for_the_day()
    {
        $admin = Admin::create([
            'name' => '管理者',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
        ]);

        $user1 = User::factory()->create(['name' => '山田太郎']);
        $user2 = User::factory()->create(['name' => '佐藤花子']);

        Attendance::create([
            'user_id' => $user1->id,
            'work_date' => '2026-03-09',
            'clock_in' => '2026-03-09 09:00:00',
            'clock_out' => '2026-03-09 18:00:00',
        ]);

        Attendance::create([
            'user_id' => $user2->id,
            'work_date' => '2026-03-09',
            'clock_in' => '2026-03-09 10:00:00',
            'clock_out' => '2026-03-09 19:00:00',
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->get('/admin/attendance/list?date=2026-03-09');

        $response->assertStatus(200);
        $response->assertSee('山田太郎');
        $response->assertSee('佐藤花子');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertSee('10:00');
        $response->assertSee('19:00');

    }

    /**
     *@test
     *遷移した際に現在の日付が表示される
     */
    public function test_current_date_is_displayed_on_admin_attendance_list()
    {
        $admin = Admin::create([
            'name' => '管理者',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->get('/admin/attendance/list');

        $response->assertStatus(200);
        $response->assertSee(Carbon::today()->format('Y/m/d'));
    }

    /**
     *@test
     *前日ボタンを押すと前日の勤怠情報が表示される
     */
    public function test_previous_day_button_shows_previous_day_attendance()
    {
        $admin = Admin::create([
            'name' => '管理者',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
        ]);

        $user = User::factory()->create(['name' => '山田太郎']);

        $prevDate = Carbon::today()->subDay()->toDateString();

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => $prevDate,
            'clock_in' => Carbon::parse($prevDate . ' 09:00:00'),
            'clock_out' => Carbon::parse($prevDate . ' 18:00:00'),
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->get('/admin/attendance/list?date=' . $prevDate);

        $response->assertStatus(200);
        $response->assertSee(Carbon::parse($prevDate)->format('Y/m/d'));
        $response->assertSee('山田太郎');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    /**
     *@test
     *翌日ボタンを押すと翌日の勤怠情報が表示される
     */
    public function test_next_day_button_shows_next_day_attendance()
    {
        $admin = Admin::create([
            'name' => '管理者',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
        ]);

        $user = User::factory()->create(['name' => '佐藤花子']);

        $nextDate = Carbon::today()->addDay()->toDateString();

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => $nextDate,
            'clock_in' => Carbon::parse($nextDate . ' 10:00:00'),
            'clock_out' => Carbon::parse($nextDate . ' 19:00:00'),
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->get('/admin/attendance/list?date=' . $nextDate);

        $response->assertStatus(200);
        $response->assertSee(Carbon::parse($nextDate)->format('Y/m/d'));
        $response->assertSee('佐藤花子');
        $response->assertSee('10:00');
        $response->assertSee('19:00');
    }
}
