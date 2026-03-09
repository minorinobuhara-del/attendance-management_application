<?php

namespace Tests\Feature\Attendance;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceRequest;
use Tests\TestCase;

class AttendanceCorrectionRequestTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    use RefreshDatabase;

    /**
     *@test
     *出勤時間が退勤時間より後の場合エラーになる
     */
    public function test_clock_in_cannot_be_after_clock_out()
    {
        $user = User::factory()->create(['email_verified_at'=>now()]);

        $attendance = Attendance::create([
            'user_id'=>$user->id,
            'work_date' => '2026-03-09',
            'clock_in' => '2026-03-09 09:00:00',
            'clock_out' => '2026-03-09 18:00:00',
        ]);

        $response = $this->actingAs($user)->post(
            "/attendance/detail/{$attendance->id}",
            [
                'clock_in'=>'19:00',
                'clock_out'=>'18:00',
                'note'=>'修正テスト'
            ]
        );

        $response->assertSessionHasErrors([
            'clock_in'=>'出勤時間もしくは退勤時間が不適切な値です'
        ]);
    }

    /**
     *@test
     *休憩開始時間が退勤時間より後の場合エラーになる
     */
    public function test_break_start_cannot_be_after_clock_out()
    {
        $user = User::factory()->create(['email_verified_at'=>now()]);

        $attendance = Attendance::create([
            'user_id'=>$user->id,
            'work_date' => '2026-03-09',
            'clock_in' => '2026-03-09 09:00:00',
            'clock_out' => '2026-03-09 18:00:00',
        ]);

        $response = $this->actingAs($user)->post(
            "/attendance/detail/{$attendance->id}",
            [
                'clock_in'=>'09:00',
                'clock_out'=>'18:00',
                'breaks'=>[
                    ['start'=>'19:00','end'=>'20:00']
                ],
                'note'=>'テスト'
            ]
        );

        $response->assertSessionHasErrors([
            'breaks.0.start'=>'休憩時間が不適切な値です'
        ]);
    }

    /**
     *@test
     *休憩終了時間が退勤時間より後の場合エラーになる
     */
    public function test_break_end_cannot_be_after_clock_out()
    {
        $user = User::factory()->create(['email_verified_at'=>now()]);

        $attendance = Attendance::create([
            'user_id'=>$user->id,
            'work_date' => '2026-03-09',
            'clock_in' => '2026-03-09 09:00:00',
            'clock_out' => '2026-03-09 18:00:00',
        ]);

        $response = $this->actingAs($user)->post(
            "/attendance/detail/{$attendance->id}",
            [
                'clock_in'=>'09:00',
                'clock_out'=>'18:00',
                'breaks'=>[
                    ['start'=>'12:00','end'=>'19:00']
                ],
                'note'=>'テスト'
            ]
        );

        $response->assertSessionHasErrors([
            'breaks.0.end'=>'休憩時間もしくは退勤時間が不適切な値です'
        ]);
    }

    /**
     *@test
     *備考未入力の場合エラーになる
     */
    public function test_note_is_required_for_correction_request()
    {
        $user = User::factory()->create(['email_verified_at'=>now()]);

        $attendance = Attendance::create([
            'user_id'=>$user->id,
            'work_date' => '2026-03-09',
            'clock_in' => '2026-03-09 09:00:00',
            'clock_out' => '2026-03-09 18:00:00',
        ]);

        $response = $this->actingAs($user)->post(
            "/attendance/detail/{$attendance->id}",
            [
                'clock_in'=>'09:00',
                'clock_out'=>'18:00',
                'note'=>''
            ]
        );

        $response->assertSessionHasErrors([
            'note'=>'備考を記入してください'
        ]);
    }

    /**
     *@test
     *修正申請が作成される
     */
    public function test_correction_request_is_created()
    {
        $user = User::factory()->create(['email_verified_at'=>now()]);

        $attendance = Attendance::create([
            'user_id'=>$user->id,
            'work_date' => '2026-03-09',
            'clock_in' => '2026-03-09 09:00:00',
            'clock_out' => '2026-03-09 18:00:00',
        ]);

        $this->actingAs($user)->post(
            "/attendance/detail/{$attendance->id}",
            [
                'clock_in'=>'09:30',
                'clock_out'=>'18:00',
                'note'=>'修正申請テスト'
            ]
        );

        $this->assertDatabaseHas('attendance_requests',[
            'user_id'=>$user->id,
            'status'=>'pending'
        ]);
    }

    /**
     *@test
     *承認待ち一覧に表示される
     */
    public function test_pending_request_is_visible_on_request_list()
    {
        $user = User::factory()->create(['email_verified_at'=>now()]);

        $attendance = Attendance::create([
            'user_id'=>$user->id,
            'work_date'=>today(),
        ]);

        AttendanceRequest::create([
            'attendance_id'=>$attendance->id,
            'user_id'=>$user->id,
            'status'=>'pending',
            'payload'=>['note'=>'テスト']
        ]);

        $response = $this->actingAs($user)
            ->get('/stamp_correction_request/list');

        $response->assertSee('承認待ち');
    }

    /**
     *@test
     *承認済み一覧に表示される
     */
    public function test_approved_request_is_visible_on_request_list()
    {
        $user = User::factory()->create(['email_verified_at'=>now()]);

        $attendance = Attendance::create([
            'user_id'=>$user->id,
            'work_date'=>today(),
        ]);

        AttendanceRequest::create([
            'attendance_id'=>$attendance->id,
            'user_id'=>$user->id,
            'status'=>'approved',
            'payload'=>['note'=>'承認テスト']
        ]);

        $response = $this->actingAs($user)
            ->get('/stamp_correction_request/list');

        $response->assertSee('承認済み');
    }

    /**
     *@test
     *詳細ボタンから勤怠詳細画面へ遷移できる
     */
    public function test_user_can_open_request_detail_page()
    {
        $user = User::factory()->create(['email_verified_at'=>now()]);

        $attendance = Attendance::create([
            'user_id'=>$user->id,
            'work_date'=>today(),
        ]);

        $request = AttendanceRequest::create([
            'attendance_id'=>$attendance->id,
            'user_id'=>$user->id,
            'status'=>'pending',
            'payload'=>['note'=>'テスト']
        ]);

        $response = $this->actingAs($user)
            ->get("/attendance/detail/{$attendance->id}");

        $response->assertStatus(200);
    }


}
