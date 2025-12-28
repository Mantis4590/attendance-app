<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DateTimeTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 勤怠打刻画面に現在日時が表示される()
    {
        // 1) 時間を固定（テストの中だけ「今」を止める）
        Carbon::setTestNow(Carbon::create(2025, 12, 28, 9, 0, 0));

        // 2) ログイン状態を作る（/attendance が auth 必須）
        $user = User::factory()->create();
        $this->actingAs($user);

        // 3) 勤怠打刻画面を開く
        $response = $this->get('/attendance');
        $response->assertStatus(200);

        // 4) UIと同じ形式で期待値を作る
        //    画面は「2025年12月28日(日)」+「09:00」みたいに分かれて表示されている
        Carbon::setLocale('ja');

        $expectedDate = Carbon::now()->isoFormat('YYYY年M月D日(ddd)'); // 2025年12月28日(日)
        $expectedTime = Carbon::now()->format('H:i');                 // 09:00

        // 5) 画面に出ているか確認
        $response->assertSee($expectedDate);
        $response->assertSee($expectedTime);

        // 6) 後片付け（他テストに影響させない）
        Carbon::setTestNow();
    }
}
