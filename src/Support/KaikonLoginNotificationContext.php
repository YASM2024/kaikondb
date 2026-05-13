<?php

namespace Kaikon2\Kaikondb\Support;

use Carbon\CarbonInterface;

/**
 * ログイン通知メール用のペイロードを、HTTP リクエスト単位で保持する（コンテナの scoped 登録）。
 * {@see \Kaikon2\Kaikondb\Listeners\LogUserLogin} が DB と同一の値を {@see store()} し、
 * {@see \Kaikon2\Kaikondb\Mail\LoginNotificationMailer} が {@see pull()} で取得する。
 *
 * Request::attributes への複製は行わない（複数 Request インスタンス問題を避ける）。
 */
final class KaikonLoginNotificationContext
{
    /**
     * @var array{email: string|null, login_at: CarbonInterface, ip_address: string|null, user_agent: string|null, user_id: int|string|null}|null
     */
    private ?array $payload = null;

    /**
     * @param  array{email: string|null, login_at: CarbonInterface, ip_address: string|null, user_agent: string|null, user_id: int|string|null}  $context
     */
    public function store(array $context): void
    {
        $this->payload = $context;
    }

    /**
     * @return array{email: string|null, login_at: CarbonInterface, ip_address: string|null, user_agent: string|null, user_id: int|string|null}|null
     */
    public function pull(): ?array
    {
        $out = $this->payload;
        $this->payload = null;

        return $out;
    }
}
