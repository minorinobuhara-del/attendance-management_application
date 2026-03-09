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

class AdminStaffTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    use RefreshDatabase;

    /**
     *@test
     *管理者ユーザーが全一般ユーザーの氏名とメールアドレスを確認できる
     */
    public function test_admin_can_see_all_users_name_and_email()
    {
        $admin = Admin::create([
            'name' => '管理者',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
        ]);

        $user1 = User::factory()->create([
            'name' => '山田太郎',
            'email' => 'yamada@example.com',
        ]);

        $user2 = User::factory()->create([
            'name' => '佐藤花子',
            'email' => 'sato@example.com',
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->get('/admin/staff/list');

        $response->assertStatus(200);
        $response->assertSee('山田太郎');
        $response->assertSee('yamada@example.com');
        $response->assertSee('佐藤花子');
        $response->assertSee('sato@example.com');
    }

    /**
     *@test
     *ユーザーの勤怠情報が正しく表示される
     */
    public function test_admin_can_see_selected_user_attendance_list()
    {
        $admin = Admin::create([
            'name' => '管理者',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
        ]);

        $user = User::factory()->create([
            'name' => '山田太郎',
        ]);

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-03-09',
            'clock_in' => '2026-03-09 09:00:00',
            'clock_out' => '2026-03-09 18:00:00',
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->get('/admin/attendance/staff/' . $user->id . '?month=2026-03');

        $response->assertStatus(200);
        $response->assertSee('山田太郎');
        $response->assertSee('03/09');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    /**
     *@test
     *前月を押下した時に表示月の前月の情報が表示される
     */
    public function test_previous_month_button_shows_previous_month_data_for_selected_user()
    {
        $admin = Admin::create([
            'name' => '管理者',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
        ]);

        $user = User::factory()->create([
            'name' => '山田太郎',
        ]);

        $prevMonthDate = Carbon::create(2026, 2, 10);

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => $prevMonthDate->toDateString(),
            'clock_in' => '2026-02-10 09:00:00',
            'clock_out' => '2026-02-10 18:00:00',
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->get('/admin/attendance/staff/' . $user->id . '?month=2026-02');

        $response->assertStatus(200);
        $response->assertSee('2026/02');
        $response->assertSee('02/10');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    /**
     *@test
     *翌月を押下した時に表示月の翌月の情報が表示される
     */
    public function test_next_month_button_shows_next_month_data_for_selected_user()
    {
        $admin = Admin::create([
            'name' => '管理者',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
        ]);

        $user = User::factory()->create([
            'name' => '山田太郎',
        ]);

        $nextMonthDate = Carbon::create(2026, 4, 10);

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => $nextMonthDate->toDateString(),
            'clock_in' => '2026-04-10 10:00:00',
            'clock_out' => '2026-04-10 19:00:00',
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->get('/admin/attendance/staff/' . $user->id . '?month=2026-04');

        $response->assertStatus(200);
        $response->assertSee('2026/04');
        $response->assertSee('04/10');
        $response->assertSee('10:00');
        $response->assertSee('19:00');
    }

    /**
     *@test
     *詳細を押下するとその日の勤怠詳細画面に遷移する
     */
    public function test_admin_can_open_attendance_detail_from_user_attendance_list()
    {
        $admin = Admin::create([
            'name' => '管理者',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
        ]);

        $user = User::factory()->create([
            'name' => '山田太郎',
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-03-09',
            'clock_in' => '2026-03-09 09:00:00',
            'clock_out' => '2026-03-09 18:00:00',
            'note' => '通常勤務',
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->get('/admin/attendance/' . $attendance->id);

        $response->assertStatus(200);
        $response->assertSee('勤怠詳細');
        $response->assertSee('山田太郎');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }
}
