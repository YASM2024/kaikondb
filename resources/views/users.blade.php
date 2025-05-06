<x-kaikon::app-layout>
    @slot('header')
    ユーザ・権限管理
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

    <div class="container py-2">
        <div class="text-left bg-light p-3 p-sm-5 rounded">
            <h2 class="mb-4">ユーザ管理</h2>

            <h4 class="my-4">ユーザ・権限一覧</h4>

            <table class="table table-striped">
                <thead class="py-2 fw-bold">
                    <th>#</th>
                    <th>ユーザ名</th>
                    <th>権限区分</th>
                    <th>ステータス</th>
                </thead>
                <tbody>
                    @foreach($users as $user)
                    <tr>
                        <td class="{{ !isset($user->email_verified_at) ? 'text-muted': ''}}">{{ $loop->iteration }}</td>
                        <td data-user-id="{{@$user->id}}" data-bs-toggle="modal" data-bs-target="#userModal"
                            class="open-modal cursor-pointer {{ !isset($user->email_verified_at) ? 'text-muted': ''}}">
                            {{@$user->name ?: '削除済ユーザ'}}</td>
                        <td class="align-items-center">
                        @if($user->email_verified_at)
                            {{ implode('; ', $user->roles) }}
                        @endif
                        </td>
                        <td>
                            @if(isset($user->email_verified_at))
                            <div class="form-check form-switch">
                                <input class="form-check-input form-check-custom cursor-pointer" id="index-{{$user->id}}" type="checkbox" role="switch" @checked($user->is_active)>
                                <label class="form-check-label cursor-pointer" for="index-{{$user->id}}">{{ $user->is_active ? '有効' : '無効' }}</label>
                            </div>
                            @else
                            未認証
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <script>

        </script>

        @slot('modal')
            <!-- モーダルの設定 -->
            <div class="modal fade" id="userModal" tabindex="-1" aria-labelledby="userLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5" id="userLabel">登録情報</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div>
                                <div class="row px-2">
                                    <div class="col-12 col-sm-4">
                                        <div class="image-container">
                                            <img id="userIcon" src="{{ url('/') }}/storage/profile/anonymousIcon.svg" class="image-gradient">
                                            <svg id="editIcon" class="svg-overlay bi cursor-pointer" width="1.2em" height="1.2em"><use xlink:href="#edit"></use></svg>
                                        </div>
                                        <div class="d-none" data-field="id"></div>
                                    </div>
                                    <div class="col ps-sm-0">
                                        <div class="row py-2">
                                            <div class="col d-flex justify-content-between align-items-center">
                                                <div class="h3 flex-grow-1" data-field="name" id="name"></div>
                                                <button id="submit" class="btn btn-secondary btn-sm w-auto">保存</button>
                                            </div>
                                        </div>
                                        <div class="row py-2 bg-body-secondary">
                                            <div class="col-4">表示名</div>
                                            <div class="col-8 px-0 d-inline-flex align-items-center">
                                                <input class="form-control me-2" data-field="show_name"></input>
                                            </div>
                                        </div>
                                        <div class="row py-2">
                                            <div class="col-4">パスワード</div>
                                            <div class="col-8 px-0 d-inline-flex align-items-center">
                                                <button class="btn btn-secondary btn-sm">再発行</button>
                                            </div>
                                        </div>
                                        <div class="row py-2 bg-body-secondary">
                                            <div class="col-4">メール</div>
                                            <div class="col-8 px-0 d-inline-flex align-items-center">
                                                <input class="form-control me-2" data-field="email"></input>
                                            </div>
                                        </div>
                                        <div class="row py-2" id="statusRow">
                                            <div class="col-4">ステータス</div>
                                            <div class="col-8 px-0">
                                                <div class="form-check form-switch" data-field="is_active">
                                                    <input class="form-check-input cursor-pointer" type="checkbox" role="switch" id="is_active">
                                                    <label class="form-check-label cursor-pointer" id="statusLabel" for="is_active"></label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row py-2 bg-body-secondary" id="rolesRow">
                                            <div class="col-4">権限</div>
                                            <div class="col-8 px-0 d-inline-flex align-items-center">
                                                <div class="mx-2" name="editBtn" data-field="roles">
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input cursor-pointer" type="checkbox" name="roles[]" id="role-001" value="001">
                                                        <label class="form-check-label cursor-pointer" for="role-001">User</label>
                                                    </div>
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input cursor-pointer" type="checkbox" name="roles[]" id="role-010" value="010">
                                                        <label class="form-check-label cursor-pointer" for="role-010">Moderator</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row py-2">
                                            <div class="col-4">ログイン</div>
                                            <div class="col-8 px-0" data-field="last_login"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div><!-- /.modal-content -->
                </div><!-- /.modal-dialog -->
            </div><!-- /.modal -->
        @endslot


    </div>


    @slot('scripts')
    <script>

    const userModalEle = document.getElementById('userModal');

    document.addEventListener('DOMContentLoaded', function () {
        // モーダルを開くボタンがクリックされた時の処理
        document.querySelectorAll('[data-user-id]').forEach(button => {
            button.addEventListener('click', function () {
                const userId = this.dataset.userId;
                fetch(`${CONFIG.baseUrl}/admin/users/${userId}`)
                    .then(response => response.json())
                    .then(data => {
                        updateModal(data);
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert(`更新に失敗しました。`);
                    });
            });
        });
    });

    userModalEle.addEventListener('shown.bs.modal', function () {
        // モーダルが開いた時の処理
        const editIcon = document.getElementById('editIcon');
        if (editIcon) { editIcon.addEventListener('click', openFileDialog); }

        const editPasswd = document.getElementById('edit_passwd');
        if (editPasswd){
            editPasswd.addEventListener('click', function () {
                console.log('パスワード編集アイコンがクリックされました！');
                // 他の処理を追加
            });
        }

    });

    userModalEle.addEventListener('hidden.bs.modal', function () {
        // モーダルが閉じた時の処理
        resetInputForm();
        document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
    });

    function fetchWithTimeout(url, options, timeout = 5000) {
        return new Promise((resolve, reject) => {
            const timer = setTimeout(() => reject(new Error("通信がタイムアウトしました。")), timeout);

            fetch(url, options)
                .then(response => {
                    clearTimeout(timer);
                    return response;
                })
                .then(resolve)
                .catch(reject);
        });
    }

    function updateModal(data) {

        const name = document.getElementById('name');
        const userIcon = document.getElementById('userIcon');
        userIcon.src = `${CONFIG.baseUrl}/storage/profile/${data.icon ?? 'anonymousIcon.svg'}`;

        document.querySelectorAll('[data-field]').forEach(el => {
        
            [statusRow, rolesRow].forEach(
                row => row.classList.toggle('d-none', !data.email_verified)
            );
        
            if (el.dataset.field == 'id') el.textContent = data[el.dataset.field] ?? 'N/A';

            else if (el.dataset.field == 'name'){
                el.textContent = data[el.dataset.field] + (data.email_verified ? '' : '（未認証ユーザ）') ?? 'N/A';
            }

            else if (el.dataset.field == 'show_name') el.value = data[el.dataset.field];

            else if (el.dataset.field == 'email') el.placeholder = data[el.dataset.field];

            else if (el.dataset.field == 'is_active'){

                const target = document.querySelector('input#is_active');
                const label = document.querySelector('label[for="is_active"]');

                target.checked = data[el.dataset.field];
                label.textContent = data[el.dataset.field] ? "有効" : "無効";

                target.addEventListener("change", function() {
                    label.textContent = target.checked ? "有効" : "無効";
                });

            }

            else if (el.dataset.field == 'roles') {
                arrRoles = data[el.dataset.field].split(',');
                if( arrRoles.includes('999')){
                    el.querySelectorAll('input').forEach(role => {
                        role.disabled = true;
                        role.checked = true;
                    });
                } else {
                    el.querySelectorAll('input').forEach(role => {
                        role.disabled = false;
                        role.checked = arrRoles.includes(role.value);
                    });
                }
            }

            else if (el.dataset.field == 'last_login') el.textContent = data[el.dataset.field] ?? 'N/A';
            
            else console.error(el);

        });

        // モーダルを表示
        const modal = new bootstrap.Modal(userModalEle);
        modal.show();
    }

    function openFileDialog() {
        let input = document.getElementById('hiddenFileInput');
        if (!input) {
            input = document.createElement('input');
            input.type = 'file';
            input.style.display = 'none'; // 非表示にする
            input.id = 'hiddenFileInput';
            document.body.appendChild(input);
        }
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

    function resetInputForm() {
        document.querySelectorAll('input[type="file"]').forEach((ele)=>{
            ele.value = null;
        })
    }
        
    //投稿アクション
    const submitBtn = document.getElementById('submit')
    submitBtn.addEventListener('click', function() {

        const id = document.querySelector('div[data-field="id"]').textContent;
        const url =`${CONFIG.baseUrl}/admin/users/${id}`

        let body = new FormData();
        const inputShowNameEle = document.querySelector('input[data-field="show_name"]');
        const inputEmailEle = document.querySelector('input[data-field="email"]');
        const inputIsActivelEle = document.querySelector('input#is_active');
        const inputRolesEle = [...document.querySelectorAll('input[name="roles[]"]:checked')].map(input => input.value);
        body.append('show_name', (inputShowNameEle ? inputShowNameEle.value : null));
        body.append('email', (inputEmailEle ? inputEmailEle.value : null));
        body.append('is_active', (inputIsActivelEle ? inputIsActivelEle.checked : null));
        body.append('roles', JSON.stringify(inputRolesEle));
        const fileInput = document.getElementById('hiddenFileInput')?.files[0];
        if (fileInput) body.append('icon', fileInput);

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
        .catch(error => {
            console.error('Error:', error);
            alert(`更新に失敗しました。`);
        });

    }, false);


    // スイッチの状態に応じてラベルを変更する(モーダルは別に定義する)
    const toggles = document.querySelectorAll(".form-check-custom");
    toggles.forEach((ele, index) => {
        let previousState = {};
        ele.addEventListener("mousedown", function() {
            previousState[index] = this.checked;
        });
        ele.addEventListener("change", function() {
            const toggleActiveUserId = this.id.split("-").pop();
            const url =`${CONFIG.baseUrl}/admin/users/${toggleActiveUserId}`

            let body = new FormData();
            const inputIsActivelEle = document.querySelector('input#is_active');
            body.append('is_active', (this ? this.checked : null));

            fetchWithTimeout(url, {
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
            }, 5000) //5秒タイムアウト
            .then(response => response.json())
            .then((data) => {
                if (data.res === 0) {
                    alert("更新しました。");
                    let label = ele.closest(".form-check").querySelector("label");
                    label.textContent = ele.checked ? "有効" : "無効";
                    // location.reload();
                } else if (data.res === 1) {
                    throw new Error(data.errors);
                } else {
                    throw new Error("不明なレスポンス");
                }
            })
            .catch(error => {
                console.error("Error");
                alert(`更新に失敗しました。`);
                this.checked = previousState[index]; // エラー時に元の状態に戻す
            });


        }, false);
    });


    </script>
    @endslot

</x-kaikon::app-layout>
