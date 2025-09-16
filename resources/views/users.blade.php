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
                                    <div class="col ps-sm-0 zebra-container">
                                        <div class="row py-2 zebra">
                                            <div class="h3 flex-grow-1" data-field="name" id="name"></div>
                                        </div>
                                        <div class="row py-2 zebra">
                                            <div class="col-4">表示名</div>
                                            <div class="col-8 px-0 d-inline-flex align-items-center">
                                                <input class="form-control me-2" data-field="show_name"></input>
                                            </div>
                                        </div>
                                        <div class="row py-2 zebra">
                                            <div class="col-4">メール</div>
                                            <div class="col-8 px-0 d-inline-flex align-items-center">
                                                <input class="form-control me-2" data-field="email"></input>
                                            </div>
                                        </div>
                                        <div class="row py-2 zebra" id="statusRow">
                                            <div class="col-4">ステータス</div>
                                            <div class="col-8 px-0">
                                                <div class="form-check form-switch" data-field="is_active">
                                                    <input class="form-check-input cursor-pointer" type="checkbox" role="switch" id="is_active">
                                                    <label class="form-check-label cursor-pointer" id="statusLabel" for="is_active"></label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row py-2 zebra" id="rolesRow">
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
                                        <div class="row py-2 zebra">
                                            <div class="col-4">ログイン</div>
                                            <div class="col-8 px-0" data-field="last_login"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer d-flex justify-content-between">
                            <!-- 左寄せグループ -->
                            <div>
                            </div>

                            <!-- 右寄せグループ（横並び） -->
                            <div class="d-flex align-items-center gap-3">
                                <button id="submit" class="btn btn-secondary btn-sm">保 存</button>
                                <button id="delete" type="button" class="btn btn-sm btn-danger">削 除</button>
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

    document.addEventListener('DOMContentLoaded', () => {
        // [data-user-id] 要素に対してクリックイベントを設定
        document.querySelectorAll('[data-user-id]').forEach(button => {
            button.addEventListener('click', () => {
            const userId = button.dataset.userId;
            fetch(`{{route('admin.showUsers')}}/${userId}`)
                .then(response => response.json())
                .then(updateModal)
                .catch(error => {
                console.error('Error:', error);
                alert('更新に失敗しました。');
                });
            });
        });
    });

    userModalEle.addEventListener('shown.bs.modal', function () {
        // モーダルが開いた時の処理
        const editIcon = document.getElementById('editIcon');
        if (editIcon) { editIcon.addEventListener('click', openFileDialog); }

        const deleteBtn = document.getElementById('delete');
        if (deleteBtn){
            deleteBtn.replaceWith(deleteBtn.cloneNode(true));
            const newDeleteBtn = document.getElementById('delete');
            newDeleteBtn.addEventListener('click', function () {
                console.log('アカウント削除がクリックされました！');
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
        // 共通の更新処理：ユーザーアイコンの更新
        document.getElementById('userIcon').src = `../storage/profile/${data.icon || 'anonymousIcon.svg'}`;

        // rolesRowの表示切替
        rolesRow?.classList.toggle('d-none', !data.email_verified);

        // 各フィールド更新の処理をマッピングしておく
        const fieldActions = {
            id: el => el.textContent = data.id || 'N/A',
            name: el => el.textContent = (data.name || 'N/A'),
            show_name: el => el.value = data.show_name,
            email: el => el.placeholder = data.email,

            // is_active フィールドの場合は input と label を更新
            is_active: () => {
                
                const target = document.querySelector('input#is_active'),
                    label = document.querySelector('label[for="is_active"]');

                if (target && label) {
                    if (!data.email_verified) {
                        // 未認証ユーザの場合はスイッチを不可視にし、ラベルに「無効化」と表示
                        target.style.display = 'none';
                        label.textContent = '未認証';
                    } else {
                        // 認証済みユーザの場合はスイッチを可視化し、状態に応じてラベルを更新
                        target.style.display = 'inline-block'; // または 'block' でもOK
                        target.checked = data.is_active;
                        label.textContent = data.is_active ? '有効' : '無効';
                        target.onchange = () => {
                            label.textContent = target.checked ? '有効' : '無効';
                        };
                    }
                }

            },
            roles: el => {
                const arrRoles = data.roles.split(',');
                el.querySelectorAll('input').forEach(role => {
                    if(arrRoles.includes('999')) {
                        role.disabled = true;
                        role.checked = true;
                    } else {
                        role.disabled = false;
                        role.checked = arrRoles.includes(role.value);
                    }
                });
            },
            last_login: el => el.textContent = data.last_login || 'N/A'
        };

        // [data-field] を持った各要素に対して、対応する処理を実行
        document.querySelectorAll('[data-field]').forEach(el => {
            const field = el.dataset.field;
            (fieldActions[field] || (() => console.error('Unmapped field:', el)))(el);
        });

        // ゼブラスタイルを再適用
        applyZebraStripes();

        // モーダルを表示
        new bootstrap.Modal(userModalEle).show();
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
            userIcon.src = `../storage/img/wait.png`;
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
        const url =`{{route('admin.showUsers')}}/${id}`

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
            const url =`{{route('admin.showUsers')}}/${toggleActiveUserId}`

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

    // ゼブラスタイルを適用する関数
    function applyZebraStripes() {
        const rows = document.querySelectorAll('.zebra-container > .zebra');
        let visibleIndex = 0;
        rows.forEach(row => {
            if (row.classList.contains('d-none')) {
                return; // 非表示はスキップ
            }
            row.style.backgroundColor = (visibleIndex % 2 === 0) ? 'white' : '#e0e0e0';
            visibleIndex++;
        });
    }


    </script>
    @endslot

</x-kaikon::app-layout>
