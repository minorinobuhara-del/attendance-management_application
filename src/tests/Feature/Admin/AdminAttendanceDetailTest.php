<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\Admin;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminAttendanceDetailTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    use RefreshDatabase;

    /**
     *@test
     *勤怠詳細画面に表示されるデータが選択したものになっている
     */
    public function test_admin_can_see_selected_attendance_detail()
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

        $attendance->breaks()->create([
            'break_start' => '2026-03-09 12:00:00',
            'break_end' => '2026-03-09 13:00:00',
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->get("/admin/attendance/{$attendance->id}");

        $response->assertStatus(200);
        $response->assertSee('山田太郎');
        $response->assertSee('2026年3月9日');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertSee('12:00');
        $response->assertSee('13:00');
        $response->assertSee('通常勤務');
    }

    /**
     *@test
     *出勤時間が退勤時間より後になっている場合、エラーメッセージが表示される
     */
    public function test_clock_in_cannot_be_after_clock_out_for_admin()
    {
        $admin = Admin::create([
            'name' => '管理者',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
        ]);

        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-03-09',
            'clock_in' => '2026-03-09 09:00:00',
            'clock_out' => '2026-03-09 18:00:00',
            'note' => '通常勤務',
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->from("/admin/attendance/{$attendance->id}")
            ->put("/admin/attendance/{$attendance->id}", [
                'clock_in' => '19:00',
                'clock_out' => '18:00',
                'breaks' => [],
                'note' => '修正テスト',
            ]);

        $response->assertRedirect("/admin/attendance/{$attendance->id}");
        $response->assertSessionHasErrors([
            'clock_in' => '出勤時間もしくは退勤時間が不適切な値です',
        ]);
    }

    /**
     *@test
     *休憩開始時間が退勤時間より後になっている場合、エラーメッセージが表示される
     */
    public function test_break_start_cannot_be_after_clock_out_for_admin()
    {
        $admin = Admin::create([
            'name' => '管理者',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
        ]);

        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-03-09',
            'clock_in' => '2026-03-09 09:00:00',
            'clock_out' => '2026-03-09 18:00:00',
            'note' => '通常勤務',
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->from("/admin/attendance/{$attendance->id}")
            ->put("/admin/attendance/{$attendance->id}", [
                'clock_in' => '09:00',
                'clock_out' => '18:00',
                'breaks' => [
                    ['start' => '19:00', 'end' => '19:30'],
                ],
                'note' => '修正テスト',
            ]);

        $response->assertRedirect("/admin/attendance/{$attendance->id}");
        $response->assertSessionHasErrors([
            'breaks.0.start' => '休憩時間が不適切な値です',
        ]);
    }

    /**
     *@test
     *休憩終了時間が退勤時間より後になっている場合、エラーメッセージが表示される
     */
    public function test_break_end_cannot_be_after_clock_out_for_admin()
    {
        $admin = Admin::create([
            'name' => '管理者',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
        ]);

        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-03-09',
            'clock_in' => '2026-03-09 09:00:00',
            'clock_out' => '2026-03-09 18:00:00',
            'note' => '通常勤務',
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->from("/admin/attendance/{$attendance->id}")
            ->put("/admin/attendance/{$attendance->id}", [
                'clock_in' => '09:00',
                'clock_out' => '18:00',
                'breaks' => [
                    ['start' => '12:00', 'end' => '19:00'],
                ],
                'note' => '修正テスト',
            ]);

        $response->assertRedirect("/admin/attendance/{$attendance->id}");
        $response->assertSessionHasErrors([
            'breaks.0.end' => '休憩時間もしくは退勤時間が不適切な値です',
        ]);
    }

    /**
     *@test
     *備考欄が未入力の場合のエラーメッセージが表示される
     */
    public function test_note_is_required_for_admin_attendance_update()
    {
        $admin = Admin::create([
            'name' => '管理者',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
        ]);

        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-03-09',
            'clock_in' => '2026-03-09 09:00:00',
            'clock_out' => '2026-03-09 18:00:00',
            'note' => '通常勤務',
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->from("/admin/attendance/{$attendance->id}")
            ->put("/admin/attendance/{$attendance->id}", [
                'clock_in' => '09:00',
                'clock_out' => '18:00',
                'breaks' => [],
                'note' => '',
            ]);

        $response->assertRedirect("/admin/attendance/{$attendance->id}");
        $response->assertSessionHasErrors([
            'note' => '備考を記入してください',
        ]);
    }
}
