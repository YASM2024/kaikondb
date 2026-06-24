<x-kaikon::app-layout>
    @slot('header')
        メンテナンス管理
    @endslot

    <div class="container py-2">
        <div class="bg-light p-3 p-sm-5 mb-4 rounded">
            <h2 class="mb-4">分野別メンテナンス管理</h2>

            <div class="mb-4">
                <a href="{{ route('dashboard') }}">&larr; 管理者メニューへ戻る</a>
            </div>

            @if (session('status'))
                <div class="alert alert-success">{{ session('status') }}</div>
            @endif

            <p class="text-muted small mb-4">
                各分野をメンテナンスモードにすると、一般ユーザーとモデレーターはその分野のページにアクセスできなくなります。
                管理者および開発者は引き続きアクセスできます。ログイン自体はブロックされません。
            </p>

            <div class="row g-4">
                @foreach ($sections as $section)
                    @php
                        $featureKey = \Kaikon2\Kaikondb\Support\SectionMaintenanceGate::CONFIG_KEYS[$section->section] ?? null;
                        $featureEnabled = $featureKey && (int) config("kaikon.{$featureKey}", 0) === 1;
                    @endphp
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <strong>{{ \Kaikon2\Kaikondb\Support\SectionMaintenanceGate::label($section->section) }}</strong>
                                @if ($section->enabled)
                                    <span class="badge text-bg-warning">メンテナンス中</span>
                                @else
                                    <span class="badge text-bg-success">公開中</span>
                                @endif
                            </div>
                            <div class="card-body">
                                @if (! $featureEnabled)
                                    <p class="text-muted small">この分野は設定上無効（<code>{{ $featureKey }}=0</code>）のため、ルート自体が登録されていません。</p>
                                @endif

                                <form method="post" action="{{ route('admin.section_maintenance.update') }}">
                                    @csrf
                                    <input type="hidden" name="section" value="{{ $section->section }}">

                                    <div class="form-check form-switch mb-3">
                                        <input
                                            class="form-check-input"
                                            type="checkbox"
                                            role="switch"
                                            id="enabled-{{ $section->section }}"
                                            name="enabled"
                                            value="1"
                                            @checked($section->enabled)
                                            @disabled(! $featureEnabled)
                                        >
                                        <label class="form-check-label" for="enabled-{{ $section->section }}">
                                            メンテナンスモード
                                        </label>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label" for="message-ja-{{ $section->section }}">表示メッセージ（日本語）</label>
                                        <textarea
                                            class="form-control"
                                            id="message-ja-{{ $section->section }}"
                                            name="message_ja"
                                            rows="2"
                                            maxlength="500"
                                            @disabled(! $featureEnabled)
                                        >{{ old('message_ja', $section->message_ja) }}</textarea>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label" for="message-en-{{ $section->section }}">表示メッセージ（English）</label>
                                        <textarea
                                            class="form-control"
                                            id="message-en-{{ $section->section }}"
                                            name="message_en"
                                            rows="2"
                                            maxlength="500"
                                            @disabled(! $featureEnabled)
                                        >{{ old('message_en', $section->message_en) }}</textarea>
                                    </div>

                                    @if ($section->updated_at)
                                        <p class="text-muted small mb-3">
                                            最終更新: {{ $section->updated_at->format('Y-m-d H:i') }}
                                        </p>
                                    @endif

                                    <button type="submit" class="btn btn-primary" @disabled(! $featureEnabled)>保存</button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-kaikon::app-layout>
