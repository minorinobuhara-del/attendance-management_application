<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\Admin;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceRequest;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminStampCorrectionRequestTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    use RefreshDatabase;

    /**
     *@test
     *承認待ちの修正申請が全て表示されている
     */
    public function test_pending_correction_requests_are_displayed_for_admin()
    {
        $admin = Admin::create([
            'name' => '管理者',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
        ]);

        $user1 = User::factory()->create(['name' => '山田太郎']);
        $user2 = User::factory()->create(['name' => '佐藤花子']);

        $attendance1 = Attendance::create([
            'user_id' => $user1->id,
            'work_date' => '2026-03-09',
            'clock_in' => '2026-03-09 09:00:00',
            'clock_out' => '2026-03-09 18:00:00',
        ]);

        $attendance2 = Attendance::create([
            'user_id' => $user2->id,
            'work_date' => '2026-03-09',
            'clock_in' => '2026-03-09 10:00:00',
            'clock_out' => '2026-03-09 19:00:00',
        ]);

        AttendanceRequest::create([
            'attendance_id' => $attendance1->id,
            'user_id' => $user1->id,
            'status' => 'pending',
            'payload' => ['note' => '山田申請'],
        ]);

        AttendanceRequest::create([
            'attendance_id' => $attendance2->id,
            'user_id' => $user2->id,
            'status' => 'pending',
            'payload' => ['note' => '佐藤申請'],
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->get('/admin/stamp_correction_request/list');

        $response->assertStatus(200);
        $response->assertSee('承認待ち');
        $response->assertSee('山田太郎');
        $response->assertSee('佐藤花子');
        $response->assertSee('山田申請');
        $response->assertSee('佐藤申請');
    }

    /**
     *@test
     *承認済みの修正申請が全て表示されている
     */
    public function test_approved_correction_requests_are_displayed_for_admin()
    {
        $admin = Admin::create([
            'name' => '管理者',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
        ]);

        $user = User::factory()->create(['name' => '山田太郎']);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-03-09',
            'clock_in' => '2026-03-09 09:00:00',
            'clock_out' => '2026-03-09 18:00:00',
        ]);

        AttendanceRequest::create([
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'status' => 'approved',
            'payload' => ['note' => '承認済み申請'],
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->get('/admin/stamp_correction_request/list');

        $response->assertStatus(200);
        $response->assertSee('承認済み');
        $response->assertSee('山田太郎');
        $response->assertSee('承認済み申請');
    }

    /**
     *@test
     *修正申請の詳細内容が正しく表示されている
     */
    public function test_admin_can_see_correction_request_detail()
    {
        $admin = Admin::create([
            'name' => '管理者',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
        ]);

        $user = User::factory()->create(['name' => '山田太郎']);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-03-09',
            'clock_in' => '2026-03-09 09:00:00',
            'clock_out' => '2026-03-09 18:00:00',
        ]);

        $request = AttendanceRequest::create([
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'status' => 'pending',
            'payload' => [
                'clock_in' => '09:30',
                'clock_out' => '18:30',
                'breaks' => [
                    ['start' => '12:00', 'end' => '13:00'],
                ],
                'note' => '遅刻修正',
            ],
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->get("/admin/stamp_correction_request/approve/{$request->id}");

        $response->assertStatus(200);
        $response->assertSee('山田太郎');
        $response->assertSee('2026年3月9日');
        $response->assertSee('09:30');
        $response->assertSee('18:30');
        $response->assertSee('12:00');
        $response->assertSee('13:00');
        $response->assertSee('遅刻修正');
    }

    /**
     *@test
     *修正申請の承認処理が正しく行われる
     */
    public function test_admin_can_approve_correction_request_and_attendance_is_updated()
    {
        $admin = Admin::create([
            'name' => '管理者',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
        ]);

        $user = User::factory()->create(['name' => '山田太郎']);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-03-09',
            'clock_in' => '2026-03-09 09:00:00',
            'clock_out' => '2026-03-09 18:00:00',
            'note' => '元データ',
        ]);

        $request = AttendanceRequest::create([
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'status' => 'pending',
            'payload' => [
                'clock_in' => '09:30',
                'clock_out' => '18:30',
                'breaks' => [
                    ['start' => '12:15', 'end' => '13:15'],
                ],
                'note' => '承認後備考',
            ],
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->put("/admin/stamp_correction_request/approve/{$request->id}");

        $response->assertRedirect("/admin/stamp_correction_request/approve/{$request->id}");

        $this->assertDatabaseHas('attendance_requests', [
            'id' => $request->id,
            'status' => 'approved',
        ]);

        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'note' => '承認後備考',
        ]);

        $this->assertStringContainsString('09:30', (string) $attendance->fresh()->clock_in);
        $this->assertStringContainsString('18:30', (string) $attendance->fresh()->clock_out);

        $this->assertDatabaseHas('break_times', [
            'attendance_id' => $attendance->id,
        ]);
    }
}
