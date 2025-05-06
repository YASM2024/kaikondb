<x-kaikon::app-layout>
  @slot('header')
    市町村マスタ
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
        <div class="row mx-2 mx-md-0 table-container">
            <div class="header-container">
                <h4 class="my-3 px-0 mx-2">市町村マスタ</h4>
                <!-- <h5 class="my-3 px-0">CSVアップロード</h5>
                <p>取込みデータは、municipality_code、municipality_ja、municipality_enを含む３列のCSVデータとして下さい。
                    文字コードはUTF-8、改行コードはLFとして下さい。
                </p>
                <form action="" method="post" enctype="multipart/form-data" class="input-group">
                    <input type="file" class="form-control" id="formFile" name="csvFile">
                    <button type="submit" class="btn btn-primary">送　信</button>
                </form> -->
                <div class="h5 my-3 px-0">現在の設定内容</div>
                <span class="p-0 pb-3 mt-3">
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">追加</button>
                    <button type="button" id="editBtn" class="btn btn-sm btn-primary" disabled>編集</button>
                    <button type="button" id="deleteBtn" class="btn btn-sm btn-danger" disabled>削除</button>
                    <a class="btn btn-secondary btn-sm" href="./download">ダウンロード</a>
                </span>
            </div>
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>-</th>
                        <th>市町村コード</th>
                        <th>市町村名</th>
                        <th>市町村名（英語）</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($municipalities as $municipality)
                    <tr>
                        <td><input name="id" class="form-check-input" type="radio" value="{{$municipality->id}}"></input></td>
                        <td municipality_id='{{$municipality->id}}'>{{$municipality->municipality_code}}</td>
                        <td municipality_id='{{$municipality->id}}'>{{$municipality->municipality_ja}}</td>
                        <td municipality_id='{{$municipality->id}}'>{{$municipality->municipality_en}}</td>
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
                        <div class="col">市町村コード</div><div class="col-8"><input id="municipality_code" name="municipality_code" class="form-control mb-2"></input></div>
                    </div>
                    <div class="row">
                        <div class="col">市町村名</div><div class="col-8"><input  id="municipality_ja" name="municipality_ja" class="form-control mb-2"></input></div>
                    </div>
                    <div class="row">
                        <div class="col">（英語）</div><div class="col-8"><input  id="municipality_en" name="municipality_en" class="form-control mb-2"></input></div>
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
                        <div class="col">市町村コード</div><div class="col-8"><input id="municipality_code_add" class="form-control mb-2"></input></div>
                    </div>
                    <div class="row">
                        <div class="col">市町村名</div><div class="col-8"><input  id="municipality_ja_add" class="form-control mb-2"></input></div>
                    </div>
                    <div class="row">
                        <div class="col">（英語）</div><div class="col-8"><input  id="municipality_en_add" class="form-control mb-2"></input></div>
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

    const municipalityIdEles = document.querySelectorAll('input[name="id"]');
    // const addBtn = document.getElementById('addBtn');
    const editBtn = document.getElementById('editBtn');
    const deleteBtn = document.getElementById('deleteBtn');

    const addModal = document.getElementById('addModal');
    addModal.addEventListener('shown.bs.modal', openAddModal);

    function openAddModal() {
        const saveUrl = "{{ url('master/municipality/create') }}";
        const saveBtn = document.getElementById('addSaveBtn');

        saveBtn.addEventListener('click', function() {

            let body = new FormData();
            const municipalityCodeEle = document.getElementById('municipality_code_add');
            const municipalityJaEle = document.getElementById('municipality_ja_add');
            const municipalityEnEle = document.getElementById('municipality_en_add');
            body.append('municipality_code', (municipalityCodeEle ? municipalityCodeEle.value : null));
            body.append('municipality_ja', (municipalityJaEle ? municipalityJaEle.value : null));
            body.append('municipality_en', (municipalityEnEle ? municipalityEnEle.value : null));

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
    municipalityIdEles.forEach(radio => {
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
        const screeningUrl = `{{url('master/municipality/delete-screening')}}/${id}`;
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
        const elements = document.querySelectorAll(`td[municipality_id='${selectedId}']`);
        const contentList = Array.from(elements).map(ele => ele.innerHTML).join(" : ");
        const deleteUrl = `{{ url('master/municipality/delete') }}/${selectedId}`;
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
        const editUrl = `{{ url('master/municipality/show') }}/${selectedId}`; // ID を URL に追加
        const saveUrl = `{{ url('master/municipality/edit') }}/${selectedId}`;
        fetch(editUrl)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                document.getElementById('municipality_code').value = data.municipality_code || "";
                document.getElementById('municipality_ja').value = data.municipality_ja || "";
                document.getElementById('municipality_en').value = data.municipality_en || "";
                editModal.show();
                const saveBtn = document.getElementById('saveBtn');
                saveBtn.addEventListener('click', function() {

                    let body = new FormData();
                    const municipalityCodeEle = document.getElementById('municipality_code');
                    const municipalityJaEle = document.getElementById('municipality_ja');
                    const municipalityEnEle = document.getElementById('municipality_en');
                    body.append('municipality_code', (municipalityCodeEle ? municipalityCodeEle.value : null));
                    body.append('municipality_ja', (municipalityJaEle ? municipalityJaEle.value : null));
                    body.append('municipality_en', (municipalityEnEle ? municipalityEnEle.value : null));

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