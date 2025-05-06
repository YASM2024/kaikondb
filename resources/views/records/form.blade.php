<x-kaikon::app-layout>
  <style>
  /* コンテナのカスタマイズ */
  @media (min-width: 768px) {  .container {max-width: 736px;}}
  /* アイコン（オンマウスで色づく） */
  .custom-border{
      margin-top: -1px;
      margin-left: -1px;
      border: 1px solid black;
  }
  .mb-2{ margin-bottom:0 !important;}
  .orderBtn, .familyBtn, .speciesBtn{ font-size:0.9em ; cursor: pointer;}
  .familyBtn, .speciesBtn { font-size: 0.76em ;}
  </style>

  <div class="container mt-4 py-2">
      <div class="row mx-2 mx-md-0">
          <div>
              <h4 class="my-3 px-0 mx-2">分類・分布情報</h4>
              <div class="row">
                <label for="species" class="col-sm-3 custom-border col-form-label text-danger">学名・和名</label>
                @if( $action_type ==='create' )
                <div class="col-sm-9 custom-border col-form-label pb-4">
                    <div class="input-group mb-3">
                      <input id="keyword" type="test" class="form-control" style="z-index: 1;" placeholder="キーワード" tabindex="1">
                      <button class="btn btn-outline-secondary" style="z-index: 1;" type="button" onclick="showSpeciesBtnByKeyword();" tabindex="2">検索</button>
                    </div>
                    <div class="mb-3">
                        <span id="select_order_id">目Orderの選択</span> <span class="badge rounded-pill bg-white text-dark border" style="cursor: pointer;" onclick="openOrderSelecter();">...</span> &gt; 
                        <span id="select_family_id">科Familyの選択</span> <span class="badge rounded-pill bg-white text-dark border" style="cursor: pointer;" onclick="openFamilySelecterByOrderId()">...</span> &gt; 
                        <span id="select_species_id">種Speciesの選択</span> <span class="badge rounded-pill bg-white text-dark border" style="cursor: pointer;" onclick="openSpeciesSelecterByFamilyId()">...</span>
                        <div><a class="mt-2 btn btn-sm btn-danger" href="../master/species/edit" target="_blank">種追加</a></div>
                    </div>
                    <div class="container text-center px-0 mb-2">
                        <div class="row g-2 taxonBtnGroup" id="orderList"></div>
                    </div>
                    
                    <div class="container text-center px-0 mb-2">
                        <div class="row g-2 taxonBtnGroup" id="familyList"></div>
                    </div>
                    
                    <div class="container text-center px-0 mb-2">
                        <div class="row g-2 taxonBtnGroup" id="speciesList"></div>
                    </div>

                </div>
                @elseif( $action_type ==='edit' )
                <div class="col-sm-9 custom-border col-form-label">
                  {{$species_all}}
                </div>
                @endif
              </div>
              
              <div class="row">
                  <label for="memo" class="col-sm-3 custom-border col-form-label text-danger">分布情報登録</label>
                  <div id="municipalities_input" class="col-sm-9 custom-border col-form-label">
                      @foreach( $municipalities as $municipality )
                      <span class="d-inline-block m-1">
                          <input type="checkbox" class="btn-check" id="btn-check-{{ $municipality->municipality_code }}" autocomplete="off" name="municipality_ids_array[]" value="{{ $municipality->municipality_code }}" form="registerRecord" 
                          @if(isset($recorded_municipalities))
                          @if(in_array($municipality->municipality_code, $recorded_municipalities))
                          checked
                          @endif
                          @endif
                          >
                          <label for="btn-check-{{ $municipality->municipality_code }}" class="btn btn-outline-primary btn-sm" style="letter-spacing: 0; width: 112px;">{{ $municipality->municipality_ja }}</label>
                      </span>
                      @endforeach
                  </div>
              </div>

              <div class="row">
                  <label for="memo" class="col-sm-3 custom-border col-form-label text-danger">関連文献</label>
                  <div class="col-sm-9 custom-border col-form-label">
                      @if(isset($article_id))
                      {{ $summary; }}
                      <input type="text" name="article_id" class="d-none" value="{{ $article_id }}" form="registerRecord">
                      @else
                      <input type="text" name="article_id" class="form-control" value="" form="registerRecord">
                      @endif
                  </div>
                </div>
              
              <div class="row">
                  <label for="rdb" class="col-sm-3 custom-border col-form-label">ＲＤＢ区分</label>
                  <div class="col-sm-9 custom-border col-form-label">
                      <input type="text" name="rdb" class="form-control" value="" form="registerRecord">
                  </div>
              </div>
              
              <div class="row">
                  <label for="link" class="col-sm-3 custom-border col-form-label">備考</label>
                  <div class="col-sm-9 custom-border col-form-label">
                      <input type="text" name="memo" class="form-control" value="" form="registerRecord">
                  </div>
              </div>
              
              <input type="text" class="d-none" id="input_order_id" value="">
              <input type="text" class="d-none" id="input_family_id" value="">
              <input type="text" name="species_id" class="d-none" id="input_species_id" value="{{@$species_id}}" form="registerRecord">
              <div class="center-button">
                  <form id="registerRecord" action="" method="POST">
                      @csrf
                      <button id="submit" class="btn btn-primary" onclick="trySubmitForm()">確認</button>
                  </form>
              </div>
              
          </div>
      </div>
  </div>
  @slot('scripts')
  <script>
      const keywordEle = document.getElementById('keyword')
      function openOrderSelecter(){
        refleshFamilyInfo();
        refleshSpeciesInfo();
        showOrderBtn();
      }
      function selectOrder(order_id, order_name_japanese, order_name_english){
        confirmOrder(order_id, order_name_japanese, order_name_english);
        openFamilySelecterByOrderId(order_id);
      }
      function selectFamily(family_id, family_name_japanese, family_name_english){
        confirmFamily(family_id, family_name_japanese, family_name_english);
        openSpeciesSelecterByFamilyId(family_id);
      }
      function selectSpecies(species_id, species_ja, species, exists_in_records){
        confirmSpecies(species_id, species_ja, species, exists_in_records);
        document.getElementById('speciesList').innerText = '';
      }
      
      function openFamilySelecterByOrderId(order_id = ''){
        refleshOrderInfo();
        refleshSpeciesInfo();
        if(order_id === ''){
          showFamilyBtnByOrderId(document.getElementById('input_order_id').value);
        }else{
          showFamilyBtnByOrderId(order_id);
        }
      }
      function openSpeciesSelecterByFamilyId(family_id = ''){
        refleshOrderInfo();
        document.getElementById('familyList').innerText = '';
        if(family_id === ''){
          showSpeciesBtnByFamilyId(document.getElementById('input_family_id').value);
        }else{
          showSpeciesBtnByFamilyId(family_id);
        }
      }

      function refleshOrderInfo(){
        document.getElementById('orderList').innerText = '';
      }
      function refleshFamilyInfo(){
        document.getElementById('select_family_id').innerText = '科Familyの選択';
        document.getElementById('familyList').innerText = '';
        document.getElementById('input_family_id').value = '';
      }
      function refleshSpeciesInfo(){
        document.getElementById('select_species_id').innerText = '種Speciesの選択';
        document.getElementById('speciesList').innerText = '';
        document.getElementById('input_species_id').value = '';
      }
      function showOrderBtn(){
          let orderList = '';
          fetch('../master/order/show_enabled')
          .then(function (response) {return response.json();})
          .then(function (jsonx) { 
              for (let i = 0; i < jsonx.length; i++) {
                  orderList = orderList + '<div class="col-6 col-md-4"><div class="p-1 rounded-2 text-bg-secondary orderBtn"';
                  orderList = orderList + ' onclick="selectOrder(' + "'" + jsonx[i].id + "'," + "'" + jsonx[i].order_ja + "'," + "'" + jsonx[i].order + "'" + ')"; openFamilySelecterByOrderId(' + "'" + jsonx[i].id + "'" + ');';
                  orderList = orderList + '>';
                  orderList = orderList + jsonx[i].order_ja + '<br>' + jsonx[i].order + '</div></div>' + "\n";
              }
          })
          .then(function (){document.getElementById('orderList').innerHTML = orderList;})
      }
      function confirmOrder(order_id, order_name_japanese, order_name_english){
        document.getElementById('select_order_id').innerText = order_name_japanese + '　' + order_name_english;
        document.getElementById('input_order_id').value = order_id;
      } 

      function showFamilyBtnByOrderId(order_id = ''){
          let familyList = '';
          if(order_id === ''){order_id = document.getElementById('input_order_id');}
          let url = '../master/family/show?order_id=' + order_id;
          fetch(url)
          .then(function (response) {
              return response.json();
          })
          .then(function (jsonx) { 
              for (let i = 0; i < jsonx.length; i++) {
                  familyList = familyList + '<div class="col-6 col-md-4"><div class="p-1 rounded-2 text-bg-secondary familyBtn"';
                  familyList = familyList + ' onclick="selectFamily(' + "'" + jsonx[i].id + "'," + "'" + jsonx[i].family_ja + "'," + "'" + jsonx[i].family + "'" + '); "';
                  familyList = familyList + '>';
                  familyList = familyList + jsonx[i].family_ja + '<br>' + jsonx[i].family + '</div></div>' + "\n";
              }
          })
          .then(function (){
              document.getElementById('familyList').innerHTML = familyList;
          })
      }
      function confirmFamily(family_id, family_name_japanese, family_name_english){
        document.getElementById('select_family_id').innerText = family_name_japanese + '　' + family_name_english;
        document.getElementById('input_family_id').value = family_id;
      }
      
      function showSpeciesBtnByFamilyId(family_id = ''){
            let speciesList = '';
            if(family_id === ''){family_id = document.getElementById('input_family_id');}
            let url = '../master/species/show?family_id=' + family_id;
            fetch(url)
            .then(function (response) {
                return response.json();
            })
            .then(function (jsonx) { 
                for (let i = 0; i < jsonx.length; i++) {
                    speciesList = speciesList + '<div class="col-6 col-md-4"><div class="p-1 rounded-2 text-bg-secondary speciesBtn"';
                    speciesList = speciesList + ' onclick="selectSpecies(' + "'" + jsonx[i].id + "'," + "'" + jsonx[i].species_ja + "'," + "'" + jsonx[i].species + "'," + jsonx[i].exists_in_records + ')"';
                    speciesList = speciesList + '>';
                    speciesList = speciesList + jsonx[i].species_ja + '<br>' + jsonx[i].species + '</div></div>' + "\n";
                }
            })
            .then(function (){
                document.getElementById('speciesList').innerHTML = speciesList;
            })
      }
      
      function showSpeciesBtnByKeyword(){
            let keywordVal = keywordEle.value;
            if(keywordVal === undefined || keywordVal === null || keywordVal === ''){ return false;}
            let speciesList = '';
            let url = '../master/species/show?keyword=' + keywordVal;
            fetch(url)
            .then(function (response) {
                return response.json();
            })
            .then(function (jsonx) { 
                for (let i = 0; i < jsonx.length; i++) {
                    speciesList = speciesList + '<div class="col-6 col-md-4"><div class="p-1 rounded-2 text-bg-secondary speciesBtn"';
                    speciesList = speciesList + ' onclick="selectSpecies(' + "'" + jsonx[i].id + "'," + "'" + jsonx[i].species_ja + "'," + "'" + jsonx[i].species + "'," + jsonx[i].exists_in_records + ')"';
                    speciesList = speciesList + '>';
                    speciesList = speciesList + jsonx[i].species_ja + '<br>' + jsonx[i].species + '</div></div>' + "\n";
                }
            })
            .then(function (){
                document.getElementById('speciesList').innerHTML = speciesList;
            })
      }
      function confirmSpecies(species_id, species_ja, species, exists_in_records ){
        document.getElementById('select_species_id').innerText = species_ja + '　' + species;
        document.getElementById('input_species_id').value = species_id;
        if(!exists_in_records){
          document.getElementById('select_species_id').classList.add("text-danger");
        }else{
          document.getElementById('select_species_id').classList.remove("text-danger");
        }
      }
      function trySubmitForm(){
        if(input_species_id === undefined || input_species_id === null || input_species_id === ''){ return false;}
        if(municipality_ids_array !== undefined || municipality_ids_array.length >= 1 ){ return false;}
        registerRecord.submit()
      }

      keywordEle.addEventListener("keydown", function(e) {
        if (e.key === "Enter") {
          showSpeciesBtnByKeyword();
        }  
        return false;
      });
      
  </script>
  @endslot
</x-kaikon::app-layout>