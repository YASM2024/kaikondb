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

    <div class="container py-2" id="admin-users-app"
        data-api-base="{{ route('admin.showUsers') }}"
        data-wait-image="{{ url('/storage/img/wait.png') }}"
        data-profile-dir="{{ url('/storage/profile') }}">
        <div class="text-left bg-light p-3 p-sm-5 rounded">
            <h2 class="mb-4">ユーザ管理</h2>

            <h4 class="my-4">ユーザ・権限一覧</h4>

            <table class="table table-striped">
                <thead class="py-2 fw-bold">
                    <th>#</th>
                    <th>ユーザ名</th>
                    <th>権限区分</th>
                    <th class="d-none d-lg-table-cell">担当タグ</th>
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
                        <td class="d-none d-lg-table-cell">
                        @if($user->email_verified_at)
                            {{ $user->tags_display }}
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
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input cursor-pointer" type="checkbox" name="roles[]" id="role-900" value="900">
                                                        <label class="form-check-label cursor-pointer" for="role-900">Developer</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row py-2 zebra d-none" id="tagsRow">
                                            <div class="col-4">担当タグ</div>
                                            <div class="col-8 px-0 d-inline-flex align-items-center flex-wrap">
                                                <div class="mx-2" data-field="tags">
                                                    @foreach($tags as $tag)
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input cursor-pointer" type="checkbox" name="tags[]" id="tag-{{ $tag->id }}" value="{{ $tag->id }}">
                                                        <label class="form-check-label cursor-pointer" for="tag-{{ $tag->id }}">{{ $tag->name }}</label>
                                                    </div>
                                                    @endforeach
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
    <script type="module" src="{{ asset('js/components/admin/users/index.js') }}"></script>
    @endslot

</x-kaikon::app-layout>
