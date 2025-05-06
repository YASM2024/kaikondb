<x-kaikon::app-layout>
    @slot('header')
    {{ __('messages.Inventory') }}
    @endslot
  <style>
      <style>
      /* プロジェクト名の高さをナビゲーションと同じにする */
      #component-serch-species header h3 {  line-height: 3rem;}
      /* コンテナのカスタマイズ */
      @media (min-width: 768px) {
        #component-serch-species .container { max-width: 736px;  }
      }
      /* アイコン（オンマウスで色づく） */
      #component-serch-species .icon-articles, 
      #component-serch-species .icon-species { box-sizing: border-box; border: 1px solid #ffffff; fill:#222222;}
      #component-serch-species .icon-articles:hover, 
      #component-serch-species .icon-species:hover { box-sizing: border-box; border: 1px solid #333399; color:#333399; fill:#333399;}
      #component-serch-species .convey-icon-color{ fill: inherit;}
      #component-serch-species .st0{ fill:inherit; }
      #component-serch-species .st0:hover{ fill:#333399; }
      #component-serch-species .item {
        color: #444444;
        text-decoration: none;
        letter-spacing: -0.2px;
        padding: 0.4em 0;
      }
      #component-serch-species div.zebra > .item:nth-child(2n){
        background-color: white;
      }
      #component-serch-species div.zebra > .item:nth-child(2n+1) {
        background-color: #e0e0e0;
      }
      #component-serch-species .item:hover {
        cursor: pointer;
        text-decoration: none;
        font-weight: bold;
        background-color: #d0e2be !important;
        letter-spacing: -0.2px;
      }
  </style>
<!-- アイコンの設定 -->
<svg xmlns="http://www.w3.org/2000/svg" class="d-none">
  <symbol id="edit" viewBox="0 0 512 512">
    <g>
      <path class="st0" d="M500.11,71.074l-59.202-59.181c-15.856-15.848-41.53-15.862-57.386-0.014l-38.378,38.378L57.252,338.185
        c-7.775,7.775-13.721,17.158-17.44,27.498L1.799,471.479c-3.957,11.032-1.206,23.36,7.092,31.656
        c8.288,8.288,20.627,11.046,31.662,7.075l105.774-38.024c10.339-3.714,19.729-9.674,27.501-17.435l277.883-277.893l0.006,0.014
        l10.028-10.048l38.364-38.37l0.014-0.021C515.91,112.598,515.965,86.943,500.11,71.074z M77.321,358.254L363.814,71.732
        l33.208,33.208L107.66,394.296l-33.035-33.028C75.491,360.236,76.364,359.209,77.321,358.254z M136.734,445.472L69.33,469.699
        l-27.019-27.02l24.223-67.392c0.17-0.492,0.412-0.957,0.602-1.442l71.028,71.024C137.68,445.064,137.218,445.299,136.734,445.472z
        M153.757,434.682c-0.956,0.95-1.982,1.83-3.014,2.696l-33.045-33.042l289.359-289.362l33.194,33.201L153.757,434.682z
        M480.034,108.384l-28.322,28.336l-1.42,1.421l-76.443-76.442l29.743-29.75c4.768-4.733,12.474-4.74,17.248,0.014l59.195,59.174
        c4.761,4.754,4.767,12.46-0.021,17.269L480.034,108.384z"></path>
    </g>
  </symbol>
</svg>

  <!-- /アイコンの設定 -->
  <div id="component-serch-species" class="container mt-4 py-2">
    <h4 class="ssj my-3 px-3 px-md-0">{{ __('messages.Inventory') }}</h4>
          <p>文献にて正式に記録された昆虫を分類に基づき整理し、検索できるようにしています。</p>
          <a href="{{ url('/summary') }}"><p>目・科の種数一覧</p></a>
          <div class="mb-3">
            <span id="by_keyword" class="h5 d-block">キーワードから探す</span>
            <div class="mb-3 me-4 input-group">
              <input id="keyword" type="text" class="form-control" placeholder="キーワード"></input>
              <button class="btn btn-secondary" onclick="generateKeywordQuery(); searchPage();">検索</button>
            </div>
            <div class="h5"><a id="by_category" class="text-dark text-decoration-none">分類から探す</a></div>
            <span class="mb-3">下表から選択してください。</span>
          </div>
          <div id="app" class="px-3" style="text-align: start;">          
            <div class="zebra mb-5 mx-0 mx-sm-3">
              <div class="row" style="background-color: #e0e0e0; padding: 0.4em 0; font-weight: bold;">
                  <div class="col-1">#</div>
                  <div class="col-8 col-md-4 ps-4">目</div>
                  <div class="col d-none d-md-block">Order</div>
                  <div class="col-3 col-md-2 text-end">種数</div>
              </div>
              @foreach( $orders as $key => $order )
              <div class="item row" onclick="generateCategoryQuery('order','{{ $order->order_id }}'); searchPage();">
                  <div class="col-1">{{ $key + 1 }}</div>
                  <div class="col-8 col-md-4 ps-4">{{ $order->order_ja }}</div>
                  <div class="col d-none d-md-block">{{ $order->order }}</div>
                  <div class="col-3 col-md-2 pe-4 text-end">{{ number_format($order->count) }}</div>
              </div>
              @endforeach
              <div class="row" style="background-color: #e0e0e0; padding: 0.4em 0; font-weight: bold;">
                  <div class="col-9 ps-5">合計</div>
                  <div class="col-3 pe-4 text-end">{{ number_format($species_count) }}</div>
              </div>
          </div><!--zebra 終わり-->
        </div><!--app 終わり-->
        <div id ="pagination"></div>
        <form class="d-none"><input class="d-none" name="httpquery" id="httpquery"></form>
  </div>




  @slot('kaikon::modal')
      <!---ModalDetail--->
      <div class="modal fade" id="ModalItemDetail" tabindex="-1" aria-labelledby="ModalArticle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">

                <div class="modal-header">
                    <h6 class="modal-title">
                        <div colspan="2">
                            <p id="species_ja" class="d-inline" style="font-size: larger;margin-bottom: 0.3em;">和名</p>
                            <p id="species_en" class="m-0" style="font-size: inherit;font-style: italic;">学名</p>
                        </div>
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="閉じる"></button>
                </div>
                
                <div class="modal-body">
                  <div class="row mb-3">
                    <div class="col-md-8 col-lg-6">
                      <div class="table">
                        <div>
                            <div><div class="bg-secondary text-light border-bottom">分布情報</div><div id="distribution_info" class="break-word"></div></div>
                            <div><div class="bg-secondary text-light border-bottom">関連文献</div><div class="break-word"><ul id="articles_info"></ul></div></div>
                            <div><div class="bg-secondary text-light border-bottom">ＲＤＢ区分</div><div id="rdb" class="break-word"></div></div>
                            <div><div class="bg-secondary text-light border-bottom">備考</div><div id="memo" class="break-word"></div></div>
                        </div>
                      </div>
                    </div>
                    <div class="col-md-4 col-lg-6">
                      <style>
                      #map{margin: 0 8%; height: 300px;} svg.outline{border: 1px solid #e0e0e0;}
                      @media screen and (min-width: 768px) {
                        #map{margin: 72px -16%; height: 186px;} svg.outline{border: none;}
                      }
                      @media screen and (min-width: 992px) {
                        #map{margin: 0 10%; height: 300px;} svg.outline{border: 1px solid #e0e0e0;}
                      }
                      </style>
                      <div id="map"></div>
                    </div>
                    <div class="px-4 py-2"><small>※地図は、本種の記録地域を市町村の区画に基づき可視化したものであり、本種の分布を正確に示しているとは限りません。</small></div>
                  </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">閉じる</button>
                </div>
            </div>
        </div>
      </div><!---ModalDetail終わり--->
  @endslot

  @slot('kaikon::scripts')
  <script src ="{{url('/')}}/js/drowMap.js"></script>
  <script src ="{{url('/')}}/js/pagination.js"></script>
  <script>
  const formatter = new Intl.NumberFormat('ja-JP');
  const keywordEle = document.getElementById('keyword')
  function generateKeywordQuery(){
    let strKeyword = keywordEle.value;
    if(strKeyword === undefined || strKeyword === null || strKeyword === '' ){
      return false;
    }else{
      document.getElementById('httpquery').value = 'keyword=' + strKeyword;
    }
  }
  function generateCategoryQuery(key, value){
    document.getElementById('httpquery').value = 'category=' + key + '&code=' + value;
  }
  function searchPage(page){
      setTimeout(() => {
        let urlHttpQuery = document.getElementById('httpquery').value;
        if(urlHttpQuery === undefined || urlHttpQuery === null || urlHttpQuery === '' ){
          return false;
        }
        if(!isNaN(page)){ urlHttpQuery += '&page=' + page }
        let url1 = './species/search?&' + urlHttpQuery;
        f1 = fetch(url1)
        .then(function (response1) {
          return response1.json();
        })
        .then(function (jsonx) {
            item_row = ""
            var paginationData = []
            paginationData['current_page'] = jsonx.current_page;
            paginationData['last_page'] = jsonx.last_page;
            paginationData['per_page'] = jsonx.per_page;
            paginationData['total'] = jsonx.total;
            const pagination = new Pagination(paginationData);
            pagination.printLink();

            //item_row = '<p><a href="" class="text-decoration-none" style="cursor: pointer;">分類から探す</a>';
            document.getElementById("by_category").href=""
            document.getElementById("by_category").setAttribute('style',"cursor: pointer;");
            item_row = '';
            if( jsonx.order != '' & jsonx.family == ''){ 
              item_row += '<div class="fw-bolder link-primary"><a class="text-decoration-none" href="./species">戻る</a></div><div class="fw-bolder">目 Order：'+ jsonx.order.order_ja + jsonx.order.order + '</div>'; 
            }
            else if( jsonx.family != '' ){ 
              item_row += '<div class="fw-bolder link-primary"><a class="text-decoration-none" href="./species">戻る</a></div>'
              item_row += '<div class="fw-bolder link-primary"><span style="cursor: pointer;" onclick="javascript: generateCategoryQuery(' + "'order','" + jsonx.order.id + "'" + '); searchPage();">目 Order：';
              item_row += jsonx.order.order_ja + jsonx.order.order + '</div><div class="fw-bolder">科 Family：' + jsonx.family.family_ja + jsonx.family.family + '</span></div>'; 
            }
            else if( jsonx.keyword != ''|| jsonx.keyword === null ){ 
              item_row += '<div class="fw-bolder link-primary"><a class="text-decoration-none" href="./species">戻る</a></div>'
            }
            item_row += '<br>'
            item_row += '<div class="zebra mb-5 mx-0 mx-sm-3">';
            item_row += '<div class="row" style="background-color: #e0e0e0; padding: 0.4em 0; font-weight: bold;">';
            item_row += '<div class="col-1">#</div>';  
            if(jsonx.order != '' & jsonx.family == ''){ 
              item_row += '<div class="col col-sm-8 col-md-5 ps-4">科</div>'; 
              item_row += '<div class="col d-none d-md-block">Family</div>'; 
              item_row += '<div class="col-3 col-md-2 text-end">種数</div>'; 
            }
            else if( jsonx.family != '' ){
              item_row += '<div class="col col-md-5 ps-4">種</div>';  
              item_row += '<div class="col d-none d-md-block">Species</div>';  
            }
            item_row += '</div>';
            for (let i = 0; i < jsonx.data.length; i++) {
                if(jsonx.order != '' & jsonx.family == ''){ 
                  item_row += '<div class="item row" onclick = "javascript: generateCategoryQuery('+"'family', '"+ jsonx.data[i].code + "'" + '); searchPage();">';
                  item_row += '<div class="col-1">' + (jsonx.per_page*(jsonx.current_page-1) + i + 1) + '</div>';
                  item_row += '<div class="col col-md-5 ps-4">'+ jsonx.data[i].family_ja + '</div>';
                  item_row += '<div class="col d-none d-md-block">'+ jsonx.data[i].family + '</div>';
                  item_row += '<div class="col-3 col-md-2 pe-4 text-end">'+ formatter.format(jsonx.data[i].count) + '</div>';
                  item_row += '</div>';
                }
                else if( jsonx.family != ''|| jsonx.keyword != ''|| jsonx.keyword === null ){
                  item_row += '<a href="" class="item row" data-bs-toggle="modal" data-bs-target="#ModalItemDetail" data-bs-whatever="' + jsonx.data[i].random_key + '">';
                  item_row += '<div class="col-1">' + (jsonx.per_page*(jsonx.current_page-1) + i + 1) + '</div>';
                  item_row += '<div class="col col-md-5 ps-4">' + jsonx.data[i].species_ja + '</div>';
                  item_row += '<div class="col d-none d-md-block">' + jsonx.data[i].species + '</div>';
                  item_row += '</a>';
                }
            }
            item_row += '</div>';
            /*item_row += jsonx.page_button;*/ 
            document.getElementById('app').innerHTML = item_row;
        })
      }, 50);
    }

    setTimeout(() => {
      const Modal1 = document.getElementById('ModalItemDetail')
      Modal1.addEventListener('show.bs.modal', event => {
        // モーダルを起動するボタン
        const button1 = event.relatedTarget;
          let url2 = './species/'+ button1.getAttribute('data-bs-whatever')+'/show'; 
          fetch(url2)
          .then(function (response) {
          return response.json();
          })
          .then(function (data) {

              @if (Auth::check())
              const edit_icon = '<svg class="bi ms-1 me-2" width="1.2em" height="1.2em"><use xlink:href="#edit"></use></svg>'
              @else
              const edit_icon = ''
              @endif
              
              species_ja.innerHTML = data.species.species_ja;
              species_en.innerText = data.species.species;
              articles_info.innerHTML = data.articles.reduce((str, article) => {
                  return str + '<li><span><a class="text-decoration-none text-dark" data-bs-toggle="collapse" href="#'+ article.rid +'" role="button" aria-expanded="false" aria-controls="collapseExample">' + article.short_summary + '</a></span><span class="collapse" id="'+ article.rid +'"> '+ article.full_summary + '</span><a class="text-dark" href="./records/' + article.rc_id +'/edit">'+ edit_icon + '</a></li>'
              },'')
              distribution_info.innerHTML = data.municipalities.names;
              rdb.innerHTML = data.rdb != undefined ? data.rdb : '';
              memo.innerHTML = data.memo != undefined ? data.memo : '';
              map.innerHTML = drowMap(data.municipalities.codes);
              return true;
          })
      })
    }, 100);

  keywordEle.addEventListener("keydown", function(e) {
      if (e.key === "Enter") {
        generateKeywordQuery();
        searchPage();
      }  
      return false;
  });
  
</script>
  @endslot
</x-kaikon::app-layout>