<x-kaikon::app-layout>
    <x-slot:header>文献情報 一括管理</x-slot:header>

    <div class="container py-2">
        <div class="bg-light p-3 p-sm-5 mb-4 rounded">
            <h2 class="mb-4">文献情報 一括管理</h2>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <section class="mb-4">
                <h5 class="my-1 px-0 ps-3 py-3 me-3 bg-secondary text-light">データエクスポート</h5>
                <div class="px-3 py-3">
                    <a href="{{ route('admin.literatures.export') }}" class="btn btn-outline-success btn-sm">CSVエクスポート</a>
                </div>
            </section>

            <section class="mb-4">
                <h5 class="my-1 px-0 ps-3 py-3 me-3 bg-secondary text-light">データインポート</h5>
                <div class="px-3 py-3">
                    <div>
                        <button type="button" id="literatureCsvImportButton" class="btn btn-outline-primary btn-sm">CSVインポート</button>
                        <input type="file" id="literatureCsvImportInput" accept=".csv,text/csv" class="d-none">
                    </div>
                    <p class="mt-2 text-muted">
                        区切り文字：カンマ（<code>,</code>）  囲い文字：二重引用符（<code>"</code>）  文字コード：UTF-8    改行コード：LF<br>
                        追加、更新、削除が可能です。（削除の場合には <code>delete_flg</code> カラムを 1 に指定してください）<br>
                        <code>order_ids</code> は目 ID をセミコロン区切りで指定します（例: <code>3;7</code>）。
                    </p>
                    <p class="mb-3">
                        <a href="{{ route('admin.literatures.import-format') }}">取込フォーマット（.csv）</a>
                    </p>
                </div>
            </section>

            <section class="mb-4">
                <h5 class="my-1 px-0 ps-3 py-3 me-3 bg-secondary text-light">データチェック</h5>
                <div class="px-3 py-3">
                    <button type="button" id="literatureCheckButton" class="btn btn-outline-secondary btn-sm">チェック実行</button>
                    <div class="form-check mt-2 mb-3">
                        <input
                            class="form-check-input"
                            type="checkbox"
                            name="checks[]"
                            value="duplicate"
                            id="checkDuplicate"
                            checked
                        >
                        <label class="form-check-label" for="checkDuplicate">文献重複チェック（著者・発行年・表題・雑誌が同一の文献）</label>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <x-slot:scripts>
        <script>
            (function () {
                const importUrl = @json(route('admin.literatures.import'));
                const checkUrl = @json(route('admin.literatures.check'));
                const importInput = document.getElementById('literatureCsvImportInput');
                const importButton = document.getElementById('literatureCsvImportButton');
                const checkButton = document.getElementById('literatureCheckButton');

                function getCsrfToken() {
                    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
                }

                function extractErrorMessage(response, data) {
                    if (data?.message) {
                        return data.message;
                    }

                    if (data?.errors) {
                        const messages = Object.values(data.errors).flat();
                        if (messages.length > 0) {
                            return messages.join('\n');
                        }
                    }

                    return `HTTP ${response.status}`;
                }

                function downloadBase64Csv(base64, filename) {
                    const binary = atob(base64);
                    const bytes = new Uint8Array(binary.length);

                    for (let i = 0; i < binary.length; i++) {
                        bytes[i] = binary.charCodeAt(i);
                    }

                    const blob = new Blob([bytes], { type: 'text/csv;charset=utf-8' });
                    const url = URL.createObjectURL(blob);
                    const anchor = document.createElement('a');
                    anchor.href = url;
                    anchor.download = filename;
                    anchor.click();
                    URL.revokeObjectURL(url);
                }

                async function importLiteratureCsv(file) {
                    if (!file) {
                        return;
                    }

                    const body = new FormData();
                    body.append('csv_file', file);

                    importButton.disabled = true;

                    try {
                        const response = await fetch(importUrl, {
                            method: 'POST',
                            credentials: 'same-origin',
                            headers: {
                                'X-CSRF-TOKEN': getCsrfToken(),
                                'Accept': 'application/json',
                            },
                            body,
                        });

                        const data = await response.json().catch(() => ({}));

                        if (!response.ok) {
                            throw new Error(extractErrorMessage(response, data));
                        }

                        alert(
                            [
                                'CSV取込が完了しました。',
                                `追加: ${data.createdCount ?? 0}件`,
                                `更新: ${data.updatedCount ?? 0}件`,
                                `削除: ${data.deletedCount ?? 0}件`,
                                `スキップ: ${data.skippedCount ?? 0}件`,
                            ].join('\n')
                        );
                    } catch (error) {
                        alert(`CSV取込に失敗しました: ${error.message}`);
                    } finally {
                        importButton.disabled = false;
                        importInput.value = '';
                    }
                }

                importButton.addEventListener('click', function () {
                    importInput.click();
                });

                importInput.addEventListener('change', function () {
                    importLiteratureCsv(importInput.files[0] ?? null);
                });

                async function runLiteratureCheck() {
                    const checks = [...document.querySelectorAll('input[name="checks[]"]:checked')]
                        .map((checkbox) => checkbox.value);

                    if (checks.length === 0) {
                        alert('チェック項目を1つ以上選択してください。');
                        return;
                    }

                    const body = new FormData();
                    checks.forEach((check) => body.append('checks[]', check));

                    checkButton.disabled = true;

                    try {
                        const response = await fetch(checkUrl, {
                            method: 'POST',
                            credentials: 'same-origin',
                            headers: {
                                'X-CSRF-TOKEN': getCsrfToken(),
                                'Accept': 'application/json',
                            },
                            body,
                        });

                        const data = await response.json().catch(() => ({}));

                        if (!response.ok) {
                            throw new Error(extractErrorMessage(response, data));
                        }

                        if (!data.hasIssues) {
                            alert(data.message ?? 'エラーはありませんでした');
                            return;
                        }

                        alert((data.messages ?? []).join('\n'));

                        if (data.csv) {
                            downloadBase64Csv(
                                data.csv,
                                data.filename ?? 'literatures_check_result.csv'
                            );
                        }
                    } catch (error) {
                        alert(`チェックに失敗しました: ${error.message}`);
                    } finally {
                        checkButton.disabled = false;
                    }
                }

                checkButton.addEventListener('click', runLiteratureCheck);
            })();
        </script>
    </x-slot:scripts>
</x-kaikon::app-layout>
