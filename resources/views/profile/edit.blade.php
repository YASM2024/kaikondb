<x-kaikon::app-layout>
  @slot('header')
    プロフィール編集
  @endslot

<style>
        /* 画像とSVGを重ねるための親要素 */
        .image-container {
            position: relative;
            width: 7em;
            height: 7em;
            display: inline-block;
        }
        .image-gradient {
            position: relative;
            width: 7em;
            height: 7em;
            display: block;
            
            /* グラデーションの適用 */
            mask-image: linear-gradient(to bottom right, rgba(0, 0, 0, 4) 60%, rgba(0, 0, 0, 1) 70%, rgba(0, 0, 0, 0) 100%);
            -webkit-mask-image: linear-gradient(to bottom right, rgba(0, 0, 0, 4) 60%, rgba(0, 0, 0, 1) 70%, rgba(0, 0, 0, 0) 100%);
        }
        .svg-overlay {
            position: absolute;
            bottom: 5px; /* 画像の右下へ配置 */
            right: 5px;
            width: 1.2em;
            height: 1.2em;
            fill: #000; /* SVGの色を調整（必要なら変更） */
        }
        .click-overlay {
            position: absolute;
            width: 100%;
            height: 100%;
            background: transparent;
            top: 0;
            left: 0;
        }
    </style>
    <svg xmlns="http://www.w3.org/2000/svg" class="d-none">
        <symbol id="edit" viewBox="0 0 24 24">
        <g>
            <path class="editIcon" d="M 19.171875 2 C 18.448125 2 17.724375 2.275625 17.171875 2.828125 L 16 4 L 20 8 L 21.171875 6.828125 
            C 22.275875 5.724125 22.275875 3.933125 21.171875 2.828125 C 20.619375 2.275625 19.895625 2 19.171875 2 z M 14.5 5.5 L 3 17 
            L 3 21 L 7 21 L 18.5 9.5 L 14.5 5.5 z"></path>
        </g>
        </symbol>
    </svg>

    <div class="container mt-4 py-2">
        <div class="text-left bg-light p-1 p-sm-5 rounded">
            <h2 class="mb-4">マイページ（登録情報）</h2>
            <div class="row px-2">
                <div class="col-12 col-sm-4">
                    <div class="image-container">
                        <img id="userIcon" src="{{url('/')}}/storage/profile/{{ $profile->icon }}" class="image-gradient" style="width: 7em;">
                        <svg id="editIcon" class="svg-overlay bi cursor-pointer" width="1.2em" height="1.2em"><use xlink:href="#edit"></use></svg>
                        <input id="icon" type="file" class="d-none"></input>
                    </div>
                </div>
                <div class="col ps-sm-0">
                    <div class="row py-2">
                        <div class="col d-flex justify-content-between align-items-center">
                            <div class="h3 flex-grow-1" data-field="name" id="name">{{ $user->name }}</div>
                            <button id="submit" class="btn btn-secondary btn-sm w-auto">保存</button>
                        </div>
                    </div>
                    <div class="row py-2 bg-body-secondary">
                        <div class="col-4">メール</div>
                        <div class="col-8 px-0 d-inline-flex align-items-center">
                            <input class="form-control me-2" data-field="email" placeholder="{{ $user->email }}">
                        </div>
                    </div>
                    <div class="row py-2">
                        <div class="col-4">パスワード</div>
                        <div class="col-8 px-0">
                            <div class="me-2">
                                <button class="btn btn-secondary btn-sm" data-bs-toggle="collapse" href="#change_passwd" role="button" aria-expanded="false" aria-controls="change_passwd">更新</button>
                            </div>
                            <div class="me-2 mt-2 collapse" id="change_passwd">
                                <form method="post" action="{{ route('password.update') }}" class="mt-6 space-y-6">
                                    @csrf
                                    @method('put')

                                    <x-text-input id="update_password_current_password" name="current_password" type="password" class="form-control mb-3" autocomplete="current-password" placeholder="現パスワード" />
                                    <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
                                    <x-text-input id="update_password_password" name="password" type="password" class="form-control mb-2" placeholder="新パスワード" autocomplete="new-password" />
                                    <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
                                    <x-text-input id="update_password_password_confirmation" name="password_confirmation" type="password" class="form-control mb-2" placeholder="新パスワード（確認）" autocomplete="new-password" />
                                    <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />

                                    <div class="flex items-center g-4">
                                    <button class="btn btn-danger btn-sm">登録</button>

                                        @if (session('status') === 'password-updated')
                                            <p
                                                x-data="{ show: true }"
                                                x-show="show"
                                                x-transition
                                                x-init="setTimeout(() => show = false, 2000)"
                                                class="text-sm text-gray-600"
                                            >{{ __('Saved.') }}</p>
                                        @endif
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="row py-2 bg-body-secondary">
                        <div class="col-4">権限</div>
                        <div class="col-8 px-0 d-inline-flex align-items-center">
                            <div class="mx-2" data-field="roles">
                            @if($user->email_verified_at)
                            {{ implode('；', $user->roles->pluck('name')->toArray()) ?: '権限なし' }}
                            @else
                            <a href="{{ route('verification.notice') }}">メール認証してください</a>
                            @endif
                        </div>
                        </div>
                    </div>
                    <div class="row py-2">
                        <div class="col-4">ステータス</div>
                        <div class="col-8 px-0 d-inline-flex align-items-center">
                            <div class="mx-2" data-field="is_active">有効</div>
                        </div>
                    </div>
                    <div class="row py-2 bg-body-secondary">
                        <div class="col-4">ログイン</div>
                        <div class="col-8 px-0" data-field="last_login">{{ $user->last_login }}</div>
                    </div>
                    <div class="row py-2">
                        <div class="col-4">表示名*</div>
                        <div class="col-8 px-0 d-inline-flex align-items-center">
                            <input class="form-control me-2" data-field="show_name" value="{{ $profile->show_name }}">
                        </div>
                    </div>
                    <div class="row py-2 bg-body-secondary">
                        <div class="col-4">自己紹介*</div>
                        <div class="col-8 ps-0 pe-2">
                            <textarea class="form-control" style="field-sizing: content;" data-field="description">{{ $profile->description }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
            <hr>
            <div><small>* アイコン画像、表示名、自己紹介は公開されます。</small></div>
            <div><small>認証済みの方がメールを変更した場合、認証がリセットされます。</small></div>
            <hr>
            <div class="d-inline-flex align-items-center">
                <button type="submit" class="btn btn-danger btn-sm">アカウント削除</button>
                <span class="ms-2">アカウントを削除した場合、元に戻すことはできません。</span>
            </div>
        </div>
    </div>
    @slot('scripts')
    <script>
    const editIcon = document.getElementById('editIcon');
    editIcon.addEventListener('click', openFileDialog);
    function openFileDialog() {
        let input = document.getElementById('icon');
        input.click();
        input.addEventListener('change', function() {
            if (!this.files.length) return;
            const file = this.files[0];
            const fr = new FileReader();
            const userIcon = document.getElementById('userIcon');
            userIcon.src = `${CONFIG.baseUrl}/storage/img/wait.png`;
            fr.onload = function() { userIcon.src = this.result; }
            fr.readAsDataURL(file);
        });
    }


    //投稿アクション
    const submitBtn = document.getElementById('submit')
    submitBtn.addEventListener('click', function() {

        const url =`${CONFIG.baseUrl}/profile`
        let body = new FormData();
        const inputDescriptionEle = document.querySelector('textarea[data-field="description"]');
        const inputShowNameEle = document.querySelector('input[data-field="show_name"]');
        const inputEmailEle = document.querySelector('input[data-field="email"]');
        if (inputEmailEle.value) alert('メールを更新すると、認証がリセットされます。');

        body.append('description', (inputDescriptionEle ? inputDescriptionEle.value : null));
        body.append('show_name', (inputShowNameEle ? inputShowNameEle.value : null));
        body.append('email', (inputEmailEle ? inputEmailEle.value : null));
        const inputIconFile = document.getElementById('icon')?.files[0];
        if (inputIconFile) body.append('icon', inputIconFile);

        const result = confirm('プロフィールを変更します。よろしいですか？');
        if (!result) {
            return;
        } else {
            // サーバにファイルをアップする
            fetch(url, {
                method: "POST", // *GET, POST, PUT, DELETE, etc.
                mode: "cors", 
                cache: "no-cache",
                credentials: "same-origin",
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                },
                redirect: "follow",
                referrerPolicy: "no-referrer",
                body
            })
            .then(response => response.json())
            .then((data) => {
                if (data.res === 0) {
                    alert("更新しました。");
                    location.reload();
                } else if (data.res === 1) {
                    throw new Error("更新に失敗しました。");
                } else {
                    throw new Error("不明なレスポンス");
                }
            })
            .catch(error => console.error('Error:', error));
        }

    }, false);
    
    </script>
    @endslot

</x-kaikon::app-layout>
