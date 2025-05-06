<x-kaikon::app-layout>
  @slot('header')
    雑誌情報マスタ
  @endslot
    <style>
    /* コンテナのカスタマイズ */
    @media (min-width: 768px) {.container { max-width: 736px;}}

    .table-container {
        height: 100vh;
        overflow: auto;
        position: relative;
        scrollbar-width: none; /* Firefox用 */
    }
    .table-container::-webkit-scrollbar {
        display: none; /* Chrome, Edge, Safari用 */
    }
    .header-container {
        position: sticky;
        top: 0;
        background-color: #f8f9fa;
        padding: 10px;
        z-index: 101;
    }
    thead {
        position: sticky;
        top: 40px; /* `.header-container` の高さに応じて調整 */
        background-color: white;
        z-index: 100;
    }

    </style>

    <div class="container mt-4 py-2">
        <div class="row mx-2 mx-md-0">
            <div class="header-container">
                <h4 class="my-3 px-0 mx-2">雑誌情報マスタ</h4>
                <!-- <h5 class="my-3 px-0">CSVアップロード</h5>
                <p>取込みデータは、journal_code,journal_name_ja,journal_name_en,URL,category,publisher,provided_byを含む７列のCSVデータとして下さい。
                    文字コードはUTF-8、改行コードはLFとして下さい。
                </p>
                <form action="" method="post" enctype="multipart/form-data" class="input-group mb-3">
                    <input type="file" class="form-control" id="formFile" name="csvFile">
                    <button type="submit" class="btn btn-primary">送　信</button>
                </form> -->

                <div class="h5 my-3 px-0">現在の設定内容</div>
                <span class="p-0 mb-3">
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">追加</button>
                    <button type="button" id="editBtn" class="btn btn-sm btn-primary" disabled>編集</button>
                    <button type="button" id="deleteBtn" class="btn btn-sm btn-danger" disabled>削除</button>
                    <a class="btn btn-secondary btn-sm" href="./download">ダウンロード</a>
                </span>
            </div>
            <table class="table table-striped table-hover text-nowrap">
                <thead>
                    <tr>
                        <th>-</th>
                        <th>雑誌コード</th>
                        <th>雑誌名</th>
                        <th>雑誌名（英語）</th>
                        <th>URL</th>
                        <th>カテゴリ</th>
                        <th>出版元</th>
                        <th>提供</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($journals as $journal)
                    <tr>
                        <td><input name="id" class="form-check-input" type="radio" value="{{$journal->id}}"></input></td>
                        <td journal_id='{{$journal->id}}'>{{$journal->journal_code}}</td>
                        <td journal_id='{{$journal->id}}'>{{$journal->journal_name_ja}}</td>
                        <td journal_id='{{$journal->id}}'>{{$journal->journal_name_en}}</td>
                        <td journal_id='{{$journal->id}}'>{{$journal->url}}</td>
                        <td journal_id='{{$journal->id}}'>{{$journal->category}}</td>
                        <td journal_id='{{$journal->id}}'>{{$journal->publisher}}</td>
                        <td journal_id='{{$journal->id}}'>{{$journal->provided_by}}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <!-- Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">市町村情報</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col">journal_code</div>
                        <div class="col-8"><input id="journal_code" name="journal_code" class="form-control mb-2"></input></div>
                    </div>
                    <div class="row">
                        <div class="col">journal_name_ja</div>
                        <div class="col-8"><input  id="journal_name_ja" name="journal_name_ja" class="form-control mb-2"></input></div>
                    </div>
                    <div class="row">
                        <div class="col">journal_name_en</div>
                        <div class="col-8"><input  id="journal_name_en" name="journal_name_en" class="form-control mb-2"></input></div>
                    </div>
                    <div class="row">
                        <div class="col">url</div>
                        <div class="col-8"><input  id="url" name="url" class="form-control mb-2"></input></div>
                    </div>
                    <div class="row">
                        <div class="col">category</div>
                        <div class="col-8"><input  id="category" name="category" class="form-control mb-2"></input></div>
                    </div>
                    <div class="row">
                        <div class="col">publisher</div>
                        <div class="col-8"><input  id="publisher" name="publisher" class="form-control mb-2"></input></div>
                    </div>
                    <div class="row">
                        <div class="col">provided_by</div>
                        <div class="col-8"><input  id="provided_by" name="provided_by" class="form-control mb-2"></input></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" id="saveBtn" class="btn btn-primary btn-sm">保存</button>
                </div>
            </div>
        </div>
    </div>
    
    <div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addModalLabel">市町村情報追加</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col">journal_code</div>
                        <div class="col-8"><input id="journal_code_add" class="form-control mb-2"></input></div>
                    </div>
                    <div class="row">
                        <div class="col">journal_name_ja</div>
                        <div class="col-8"><input  id="journal_name_ja_add" class="form-control mb-2"></input></div>
                    </div>
                    <div class="row">
                        <div class="col">journal_name_en</div>
                        <div class="col-8"><input  id="journal_name_en_add" class="form-control mb-2"></input></div>
                    </div>
                    <div class="row">
                        <div class="col">url</div>
                        <div class="col-8"><input  id="url_add" class="form-control mb-2"></input></div>
                    </div>
                    <div class="row">
                        <div class="col">category</div>
                        <div class="col-8"><input  id="category_add" class="form-control mb-2"></input></div>
                    </div>
                    <div class="row">
                        <div class="col">publisher</div>
                        <div class="col-8"><input  id="publisher_add" class="form-control mb-2"></input></div>
                    </div>
                    <div class="row">
                        <div class="col">provided_by</div>
                        <div class="col-8"><input  id="provided_by_add" class="form-control mb-2"></input></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" id="addSaveBtn" class="btn btn-primary btn-sm">保存</button>
                </div>
            </div>
        </div>
    </div>

    @slot('scripts')
    <script>

    const journalIdEles = document.querySelectorAll('input[name="id"]');
    // const addBtn = document.getElementById('addBtn');
    const editBtn = document.getElementById('editBtn');
    const deleteBtn = document.getElementById('deleteBtn');

    const addModal = document.getElementById('addModal');
    addModal.addEventListener('shown.bs.modal', openAddModal);

    function openAddModal() {
        const saveUrl = "{{ url('master/journal/create') }}";
        const saveBtn = document.getElementById('addSaveBtn');

        saveBtn.addEventListener('click', function() {

            let body = new FormData();
            const journalCodeEle = document.getElementById('journal_code_add');
            const journalNameJaEle = document.getElementById('journal_name_ja_add');
            const journalNameEnEle = document.getElementById('journal_name_en_add');
            const urlEle = document.getElementById('url_add');
            const categoryEle = document.getElementById('category_add');
            const publisherEle = document.getElementById('publisher_add');
            const providedByEle = document.getElementById('provided_by_add');
            body.append('journal_code', (journalCodeEle ? journalCodeEle.value : null));
            body.append('journal_name_ja', (journalNameJaEle ? journalNameJaEle.value : null));
            body.append('journal_name_en', (journalNameEnEle ? journalNameEnEle.value : null));
            body.append('url', (urlEle ? urlEle.value : null));
            body.append('category', (categoryEle ? categoryEle.value : null));
            body.append('publisher', (publisherEle ? publisherEle.value : null));
            body.append('provided_by', (providedByEle ? providedByEle.value : null));

            fetch(saveUrl, {
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

        });
        
    }
    journalIdEles.forEach(radio => {
        radio.addEventListener('click', async () => {

            await activateBtns();
            deleteBtn.removeEventListener('click', askDelete);
            deleteBtn.addEventListener('click', askDelete);
            editBtn.removeEventListener("click", openEditModal);
            editBtn.addEventListener("click", openEditModal);

            event.stopPropagation(); // td へのイベント伝播を防ぐ
        });
    });

    document.querySelectorAll("td").forEach(td => {
        td.addEventListener("click", async () => {
            const row = td.closest("tr");
            const radio = row.querySelector("input[type='radio']");
            if (radio) {
                radio.checked = true;
                await activateBtns();
                deleteBtn.removeEventListener("click", askDelete);
                deleteBtn.addEventListener("click", askDelete);
                editBtn.removeEventListener("click", openEditModal);
                editBtn.addEventListener("click", openEditModal);
            }
        });
    });


    async function activateBtns(){
        const selectedId = document.querySelector('input[name="id"]:checked')?.value;
        editBtn.setAttribute('disabled', true);
        deleteBtn.setAttribute('disabled', true);
        editBtn.removeAttribute('disabled');
        // 非同期処理を正しく実行
        const deletable = await isDeletable(selectedId);
        if (deletable) {
            deleteBtn.removeAttribute('disabled');
        }
    }

    async function isDeletable(id) {
        const screeningUrl = `{{url('master/journal/delete-screening')}}/${id}`;
        try {
            const response = await fetch(screeningUrl);
            const data = await response.json();
            return data.deletable;
        } catch (error) {
            console.error("Error fetching data:", error);
            return false;
        }
    }

    function askDelete(){
        const selectedId = document.querySelector('input[name="id"]:checked')?.value;
        const elements = document.querySelectorAll(`td[journal_id='${selectedId}']`);
        const contentList = Array.from(elements).map(ele => ele.innerHTML).join(" : ");
        const deleteUrl = `{{ url('master/journal/delete') }}/${selectedId}`;
        const result = confirm(`本当に削除してよろしいですか？\n削除すると元には戻せません。\n${contentList}`);
        if (!result) {
            return;
        } else {
            fetch(deleteUrl, {
                method: "POST", // *GET, POST, PUT, DELETE, etc.
                mode: "cors",
                cache: "no-cache",
                credentials: "same-origin",
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                },
                redirect: "follow",
                referrerPolicy: "no-referrer"
            })
            .then(response => response.json())
            .then((data) => {
                if (data.res === 0) {
                    alert("削除しました。");
                    location.reload();
                } else if (data.res === 1) {
                    throw new Error("削除に失敗しました。");
                } else {
                    throw new Error("不明なレスポンス");
                }
            })
            .catch(error => console.error('Error:', error));
        }

    }

    function openEditModal() {
        const selectedId = document.querySelector('input[name="id"]:checked')?.value;
        const editModalEle = document.getElementById('editModal');
        const editModal = new bootstrap.Modal(editModalEle);
        const editUrl = `{{ url('master/journal/show') }}/${selectedId}`; // ID を URL に追加
        const saveUrl = `{{ url('master/journal/edit') }}/${selectedId}`;
        fetch(editUrl)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                document.getElementById('journal_code').value = data.journal_code || "";
                document.getElementById('journal_name_ja').value = data.journal_name_ja || "";
                document.getElementById('journal_name_en').value = data.journal_name_en || "";
                document.getElementById('url').value = data.url || "";
                document.getElementById('category').value = data.category || "";
                document.getElementById('publisher').value = data.publisher || "";
                document.getElementById('provided_by').value = data.provided_by || "";
                editModal.show();
                const saveBtn = document.getElementById('saveBtn');
                saveBtn.addEventListener('click', function() {

                    let body = new FormData();
                    const journalCodeEle = document.getElementById('journal_code');
                    const journalNameJaEle = document.getElementById('journal_name_ja');
                    const journalNameEnEle = document.getElementById('journal_name_en');
                    const urlEle = document.getElementById('url');
                    const categoryEle = document.getElementById('category');
                    const publisherEle = document.getElementById('publisher');
                    const providedByEle = document.getElementById('provided_by');
                    body.append('journal_code', (journalCodeEle ? journalCodeEle.value : null));
                    body.append('journal_name_ja', (journalNameJaEle ? journalNameJaEle.value : null));
                    body.append('journal_name_en', (journalNameEnEle ? journalNameEnEle.value : null));
                    body.append('url', (urlEle ? urlEle.value : null));
                    body.append('category', (categoryEle ? categoryEle.value : null));
                    body.append('publisher', (publisherEle ? publisherEle.value : null));
                    body.append('provided_by', (providedByEle ? providedByEle.value : null));

                    fetch(saveUrl, {
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

                });
            })
            .catch(error => console.error("エラー:", error));
    }

    </script>
    @endslot

</x-kaikon::app-layout>