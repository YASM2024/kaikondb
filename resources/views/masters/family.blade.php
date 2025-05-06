<x-kaikon::app-layout>
  @slot('header')
    分類マスタ
  @endslot
    <style>
    /* コンテナのカスタマイズ */
    @media (min-width: 768px) {.container { max-width: 736px;}}
    tbody > tr :hover{ cursor: pointer;}
    .rowItem > tr > span{ color: red;}
    </style>

    <!-- アイコンの設定 -->
    <svg xmlns="http://www.w3.org/2000/svg" class="d-none">

    <symbol id="reload" viewBox="0 0 512 512"><style>.reloadicon{fill:#4B4B4B;}</style>
        <g>
            <path class="reloadicon" d="M403.925,108.102c-27.595-27.595-62.899-47.558-102.459-56.29L304.182,0L201.946,53.867l-27.306,14.454
                l-5.066,2.654l8.076,4.331l38.16,20.542l81.029,43.602l2.277-42.859c28.265,7.546,53.438,22.53,73.623,42.638
                c29.94,29.939,48.358,71.119,48.358,116.776c0,23.407-4.843,45.58-13.575,65.687l40.37,17.532
                c11.076-25.463,17.242-53.637,17.242-83.219C465.212,198.306,441.727,145.904,403.925,108.102z"  stroke="#4B4B4B" stroke-width="6"></path>
            <path class="reloadicon" d="M296.256,416.151l-81.101-43.612l-2.272,42.869c-28.26-7.555-53.51-22.53-73.618-42.636
                c-29.945-29.95-48.364-71.12-48.364-116.767c0-23.427,4.844-45.522,13.576-65.697l-40.37-17.531
                c-11.076,25.53-17.242,53.723-17.242,83.228c0,57.679,23.407,110.157,61.21,147.893c27.595,27.594,62.899,47.548,102.453,56.202
                l-2.716,51.9l102.169-53.878l27.455-14.454l4.988-2.643l-7.999-4.332L296.256,416.151z"  stroke="#4B4B4B" stroke-width="6"></path>
        </g>
    </symbol>
    
    <symbol id="edit" viewBox="0 0 24 24">
        <g>
            <path class="editIcon" d="M 19.171875 2 C 18.448125 2 17.724375 2.275625 17.171875 2.828125 L 16 4 L 20 8 L 21.171875 6.828125 
            C 22.275875 5.724125 22.275875 3.933125 21.171875 2.828125 C 20.619375 2.275625 19.895625 2 19.171875 2 z M 14.5 5.5 L 3 17 
            L 3 21 L 7 21 L 18.5 9.5 L 14.5 5.5 z"></path>
        </g>
    </symbol>

    <symbol id="plus" viewBox="0 0 512 512">
        <g>
            <path class="plusIcon" d="M359.244,224.004h-59.988c-6.217,0-11.258-5.043-11.258-11.258v-59.992c0-6.215-5.039-11.254-11.256-11.254
                h-41.486c-6.217,0-11.258,5.039-11.258,11.254v59.992c0,6.215-5.039,11.258-11.256,11.258h-59.988
                c-6.219,0-11.258,5.039-11.258,11.258v41.484c0,6.215,5.039,11.258,11.258,11.258h59.988c6.217,0,11.256,5.039,11.256,11.258
                v59.984c0,6.219,5.041,11.258,11.258,11.258h41.486c6.217,0,11.256-5.039,11.256-11.258v-59.984
                c0-6.219,5.041-11.258,11.258-11.258h59.988c6.217,0,11.258-5.043,11.258-11.258v-41.484
                C370.502,229.043,365.461,224.004,359.244,224.004z"></path>
            <path class="plusIcon" d="M256,0C114.613,0,0,114.617,0,256c0,141.387,114.613,256,256,256c141.383,0,256-114.613,256-256
                C512,114.617,397.383,0,256,0z M256,448c-105.871,0-192-86.129-192-192c0-105.867,86.129-192,192-192c105.867,0,192,86.133,192,192
                C448,361.871,361.867,448,256,448z"></path>
        </g>
    </symbol>

    <symbol id="back" viewBox="0 0 512 512">
        <g>
            <path class="backIcon" d="M452.421,155.539c-36.6-36.713-87.877-59.612-143.839-59.579h-87.179V9.203L5.513,228.805h215.889h87.179
                c19.702,0.033,36.924,7.8,49.898,20.659c12.876,12.99,20.644,30.212,20.676,49.914c-0.032,19.703-7.8,36.924-20.676,49.898
                c-12.974,12.876-30.196,20.642-49.898,20.676H0v132.844h308.582c55.962,0.033,107.239-22.866,143.839-59.579
                c36.715-36.6,59.612-87.877,59.579-143.84C512.033,243.416,489.136,192.14,452.421,155.539z"></path>
        </g>
    </symbol>

    <symbol id="enter" viewBox="0 0 26 26">
        <g>
            <path class="enterIcon" d="M 22.566406 4.730469 L 20.773438 3.511719 C 20.277344 3.175781 19.597656 3.304688 19.265625 3.796875 
                L 10.476563 16.757813 L 6.4375 12.71875 C 6.015625 12.296875 5.328125 12.296875 4.90625 12.71875 L 3.371094 14.253906 C 
                2.949219 14.675781 2.949219 15.363281 3.371094 15.789063 L 9.582031 22 C 9.929688 22.347656 10.476563 22.613281 10.96875 
                22.613281 C 11.460938 22.613281 11.957031 22.304688 12.277344 21.839844 L 22.855469 6.234375 C 23.191406 5.742188 23.0625 
                5.066406 22.566406 4.730469 Z"/>
        </g>
    </symbol>

    </svg>


    <div class="container mt-4 py-2">
        <div class="row mx-2 mx-md-0">
            <h4 class="my-3 px-0 mx-2">科（Family）マスタ</h4>
            <h5 class="my-3 px-0">現在の設定内容</h5>
        </div>
        <select name="order_select" id="order_select" class="w-75 mb-4 form-control">
            <option value="">目を選択してください</option>
            <option value="7">蜻蛉目（トンボ目）</option>
            <option value="10">直翅目（バッタ目）</option>
            <option value="15">蟷螂目（カマキリ目）</option>
            <option value="20">半翅目</option>
            <option value="23">鞘翅目</option>
            <option value="27">双翅目</option>
            <option value="28">鱗翅目</option>
            <option value="30">膜翅目</option>
        </select>
        <div id="table"></div>
    </div>

    @slot('scripts')
    <script>
    const this_url = "/database/master/family/edit";
    const tableArea = document.getElementById('table')
    const order_select = document.getElementById('order_select')
    let show_url = `../../master/family/show?order_id={order_id}`;
    let targetRowEle
    const templateTr = `
    <td class="bg-{color} bg-opacity-25">
        <div class="row">
            <input class="col-8" name="code" type="text" placeholder="コード" style="font-weight: bold; background: none; border: none; outline: none; width:5em;" value="{codeShow}"></input>
        </div>
    </td>
    <td class="bg-{color} bg-opacity-25">
        <div class="row">
            <input class="col-8 col-sm-6" name="family_ja" type="text" placeholder="科" style="font-weight: bold; background: none; border:none;" value="{familyJaShow}"></input>
            <input class="col-8 col-sm-6" name="family" type="text" placeholder="Family" style="font-weight: bold; background: none; border:none;" value="{familyShow}"></input>
            <input class="d-none" name="id" type="text" value="{idHidden}"></input>
            <input class="d-none" name="order_id" type="text" value="{orderIdHidden}"></input>
        </div>
    </td>
    <td class="bg-{color} bg-opacity-25">
        <div class="row px-1">
            <div class="col px-0 text-muted" name="enterBtn">
                <svg class="bi" width="1.2em" height="1.2em"><use xlink:href="#enter"></use></svg>
            </div>
            <div class="col px-0 text-muted" name="backBtn">
                <svg class="bi" width="1em" height="1em"><use xlink:href="#back"></use></svg>
            </div>
        </div>
    </td>
    `;
    let backBtn = ""
    let submitBtn = ""

    order_select.addEventListener("change", showTable, false );

    function showTable(){
        const index = order_select.selectedIndex;
        let order_id = order_select[index].value;
        let tableEle = `
        <div class="d-flex justify-content-end mb-2 me-4">
            <div class="p-0 btn btn-link">
                <svg class="bi" width="1.2em" height="1.2em"><use xlink:href="#reload"></use></svg>
            </div>
        </div>
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>code</th>
                    <th class="row">
                        <div class="col-8 col-sm-6">family_ja</div>
                        <div class="col-8 col-sm-5">family</div>
                    </th>
                    <th>
                        <div class="f-bold text-muted" name="addBtn">
                            <svg class="bi ms-1 me-2 text-right" width="1.2em" height="1.2em"><use xlink:href="#plus"></use></svg>
                        </div>
                    </th>
                </tr>
            </thead>
            <tbody>
                {tbody}
            </tbody>
        </table>
        `;
        if( order_id == undefined || order_id == '' || order_id == null ){

            tableArea.innerHTML = '';
        
        }else{
            let tbody = '';

            fetch(show_url.replace('{order_id}',order_id))
            .then(function (response) {
                return response.json();
            })
            .then(function (json) { 
                for (let i = 0; i < json.length; i++) {
                    tbody += `<tr class="rowItem">
                    <td>
                        <div class="row">
                            <div class="col-8">${json[i].code}</div>
                        </div>
                    </td>
                    <td>
                        <div class="row">
                            <div class="col-8 col-sm-6">${json[i].family_ja}</div>
                            <div class="col-8 col-sm-5">${json[i].family}</div>
                            <div class="d-none">${json[i].id}</div>
                            <div class="d-none">${json[i].order_id}</div>
                        </div>
                    </td>
                    <td>
                        <div class="row px-1">
                            <div class="col px-0 text-muted" name="editBtn">
                                <svg class="bi" width="1.2em" height="1.2em"><use xlink:href="#edit"></use></svg>
                            </div>
                            <div class="col px-0 text-muted" name="addBtn">
                                <svg class="bi" width="1.2em" height="1.2em"><use xlink:href="#plus"></use></svg>
                            </div>
                        </div>
                    </td>
                    </tr>\n`;
                }
            })
            .then(function (){
                tableArea.innerHTML = tableEle.replace('{tbody}', tbody);
                const itemAdd = document.querySelectorAll('.rowItem [name="addBtn"]');
                for(let i = 0; i < itemAdd.length; i++){
                    itemAdd[i].addEventListener('click',function(){
                        
                        back();
                        
                        let addTableRow = document.createElement('tr');
                        addTableRow.setAttribute('id','addNow');
                        addTableRow.innerHTML = templateTr.replaceAll('{color}','danger').replace('{idHidden}','').replace('{codeShow}','').replace('{familyJaShow}','').replace('{familyShow}','').replace('{orderIdHidden}',order_id);
                        
                        itemAdd[i].parentElement.parentElement.parentElement.insertAdjacentElement('afterend', addTableRow);

                        targetRowEle = addTableRow;
                        let submitBtn = addTableRow.querySelector('#table [name="enterBtn"]')
                        submitBtn.addEventListener('click', submitData, false );

                        backBtn = document.querySelector('#table [name="backBtn"]');
                        backBtn.addEventListener('click', back, false );
                    }, false );
                }

                const itemEdit = document.querySelectorAll('.rowItem [name="editBtn"]');
                for(let i = 0; i < itemEdit.length; i++){
                    itemEdit[i].addEventListener('click',function(){
                        
                        back();
                        
                        let itemEditRow = itemEdit[i].parentElement.parentElement.parentElement
                        itemEditRow.classList.add('d-none');
                        itemEditRow.setAttribute('id','editNow');

                        let code = itemEditRow.children[0].children[0].innerText.replaceAll(' ','');
                        let family_ja = itemEditRow.children[1].children[0].children[0].innerText.replaceAll(' ','');
                        let family = itemEditRow.children[1].children[0].children[1].innerText.replaceAll(' ','');
                        let id = itemEditRow.children[1].children[0].children[2].innerText.replaceAll(' ','');
                        let addTableRow = document.createElement('tr');
                        addTableRow.setAttribute('id','editRowEle');
                        addTableRow.innerHTML = templateTr.replaceAll('{color}','success').replace('{idHidden}',id).replace('{codeShow}',code).replace('{familyJaShow}',family_ja).replace('{familyShow}',family).replace('{orderIdHidden}',order_id);
                        itemEditRow.insertAdjacentElement('afterend', addTableRow);

                        targetRowEle = document.querySelector('#table #editRowEle')
                        let submitBtn = targetRowEle.querySelector('#table [name="enterBtn"]')
                        submitBtn.addEventListener('click', submitData, false );

                        backBtn = document.querySelector('#table [name="backBtn"]');
                        backBtn.addEventListener('click', back, false );

                    }, false );
                }
            })
        }
    }

    function back(){
        let backBtn = document.querySelector('#table [name="backBtn"]');
        let itemEditRow = document.querySelector('#editNow');
        let itemaddRow = document.querySelector('#addNow');
        if(backBtn !== null){
            backBtn.parentElement.parentElement.parentElement.remove();
        }
        if(itemEditRow !== null){
            itemEditRow.classList.remove('d-none');
            itemEditRow.removeAttribute('id','editNow');
        }
        if(itemaddRow !== null){
            itemaddRow.remove();
        }
    }

    function submitData(){
        
        let res = confirm("更新してもよろしいですか？元に戻すことはできません。");

        if( res === true ) {

            let body = new FormData()
            body.append('id', targetRowEle.querySelector('input[name="id"]').value)
            body.append('family_ja', targetRowEle.querySelector('input[name="family_ja"]').value)
            body.append('family', targetRowEle.querySelector('input[name="family"]').value)
            body.append('order_id', targetRowEle.querySelector('input[name="order_id"]').value)
            body.append('code', targetRowEle.querySelector('input[name="code"]').value)

            fetch(this_url, {
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
            .then((res) => {
                return res.json();
            })
            .then((data) => {
                if( data.result ==='success' ){
                    alert("修正を完了しました。画面を再読み込みします。");
                }else if( data.result ==='error' ){
                    alert("！エラーを感知しました。画面を再読み込みします。！");
                }else{
                    alert("修正に失敗しました。");
                }
            })
            .then(() => {
                showTable(); 
            })
            .catch(()=>{
                alert("エラーが発生しました。");
            })
        }

    }

    </script>
    @endslot
</x-kaikon::app-layout>