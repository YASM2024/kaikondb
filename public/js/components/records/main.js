// js/components/records/main.js
const formatter = new Intl.NumberFormat('ja-JP');

// DOM 要素をまとめる
export const DOM = {
  modal: document.getElementById('ModalItemDetail'),
  keyword: document.getElementById('keyword'),
  searchBtn: document.getElementById('searchBtn'),
};

// Search オブジェクト
export const Search = {
    init() {
      // order のクリックイベントをまとめて設定
      document.querySelectorAll('.item.row.searchable').forEach(item => {
        item.addEventListener('click', () => {
          const orderId = item.dataset.orderId;
          Search.generateCategoryQuery('order', orderId);
          Search.searchPage();
        });
      });
    },
    generateKeywordQuery(){
      let strKeyword = DOM.keyword.value;
      if(strKeyword === undefined || strKeyword === null || strKeyword === '' ){
        return false;
      }else{
        document.getElementById('httpquery').value = 'keyword=' + strKeyword;
      }
    },
    generateCategoryQuery(key, value){
      document.getElementById('httpquery').value = 'category=' + key + '&code=' + value;
    },
    searchPage(page){
      setTimeout(() => {
        let urlHttpQuery = document.getElementById('httpquery').value;
        if(urlHttpQuery === undefined || urlHttpQuery === null || urlHttpQuery === '' ){
          return false;
        }
        if(!isNaN(page)){ urlHttpQuery += '&page=' + page }
        let url1 = './species/search?&' + urlHttpQuery;
        const f1 = fetch(url1)
        .then(function (response1) {
          return response1.json();
        })
        .then(function (jsonx) {
            let pagination = new Pagination({
              current_page: jsonx.current_page,
              last_page: jsonx.last_page,
              per_page: jsonx.per_page,
              total: jsonx.total
            });

            pagination.onPageChange = (page) => {
                Search.searchPage(page);
            };
            pagination.renderLinks();
            pagination.renderMessage();

            //item_row = '<p><a href="" class="text-decoration-none" style="cursor: pointer;">分類から探す</a>';
            document.getElementById("by_category").href=""
            document.getElementById("by_category").setAttribute('style',"cursor: pointer;");
            let item_row = '';
            if( jsonx.order != '' & jsonx.family == ''){ 
              item_row += '<div class="fw-bolder link-primary"><a class="text-decoration-none" href="./species">戻る</a></div><div class="fw-bolder">目 Order：'+ jsonx.order.order_ja + jsonx.order.order + '</div>'; 
            }
            else if( jsonx.family != '' ){ 
              item_row += '<div class="fw-bolder link-primary"><a class="text-decoration-none" href="./species">戻る</a></div>'
              item_row += '<div id="back_to_order" class="fw-bolder link-primary"><span style="cursor: pointer;" data-order-id="' + jsonx.order.id + '">目 Order：';
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
                    item_row += `
                    <div class="item row searchable" 
                        data-key="family" 
                        data-value="${jsonx.data[i].code}">
                        <div class="col-1">${jsonx.per_page*(jsonx.current_page-1) + i + 1}</div>
                        <div class="col col-md-5 ps-4">${jsonx.data[i].family_ja}</div>
                        <div class="col d-none d-md-block">${jsonx.data[i].family}</div>
                        <div class="col-3 col-md-2 pe-4 text-end">${formatter.format(jsonx.data[i].count)}</div>
                    </div>`;
                }
                else if( jsonx.family != ''|| jsonx.keyword != ''|| jsonx.keyword === null ){
                  item_row += '<div class="item row" data-bs-toggle="modal" data-bs-target="#ModalItemDetail" data-bs-whatever="' + jsonx.data[i].random_key + '">';
                  item_row += '<div class="col-1">' + (jsonx.per_page*(jsonx.current_page-1) + i + 1) + '</div>';
                  item_row += '<div class="col col-md-5 ps-4">' + jsonx.data[i].species_ja + '</div>';
                  item_row += '<div class="col d-none d-md-block">' + jsonx.data[i].species + '</div>';
                  item_row += '</div>';
                }
            }
            item_row += '</div>';
            /*item_row += jsonx.page_button;*/ 
            document.getElementById('app').innerHTML = item_row;

            // 生成された要素にイベントを付与
            document.querySelectorAll('#app .item.searchable').forEach(el => {
                el.addEventListener('click', () => {
                    const key = el.dataset.key;
                    const value = el.dataset.value;
                    Search.generateCategoryQuery(key, value);
                    Search.searchPage();
                });
            });
            
            const backToOrderBtn = document.getElementById('back_to_order');
            backToOrderBtn?.addEventListener('click', (e) => {
                const orderId = e.target.dataset.orderId;
                Search.generateCategoryQuery('order', orderId);
                Search.searchPage();
            });

        })
      }, 50);
    }
  };

// 関数
export function formatPlaceNames(namesArray) {
  return namesArray.join(', ');
}

// イベント登録
DOM.modal?.addEventListener('show.bs.modal', event => {
    // モーダルを起動するボタン
    const button1 = event.relatedTarget;
      let url2 = './species/'+ button1.getAttribute('data-bs-whatever')+'/show'; 
      fetch(url2)
      .then(function (response) {
      return response.json();
      })
      .then(function (data) {
          let edit_icon = ''
          if( window.authenticated ) {
              edit_icon = '<i class="bi bi-pencil-square text-primary"></i>';
          }
          
          const species_id = data.species.species_id;
          species_ja.innerHTML = data.species.species_ja;
          species_en.innerText = data.species.species;


          // 1-1. 全体の collections の names と codes を収集
          const allCollectionNames = new Set();
          const allCollectionCodes = new Set();

          data.articles.forEach(article => {
            const names = article.records?.collections?.names || '';
            const codes = article.records?.collections?.codes || '';

            names.split(/[;；]/).map(n => n.trim()).filter(n => n).forEach(n => allCollectionNames.add(n));
            codes.split(/[;；]/).map(c => c.trim()).filter(c => c).forEach(c => allCollectionCodes.add(c));
          });

          // 1-2. 各 article の observations から collections に含まれるものを除去
          data.articles = data.articles.map(article => {
            
            const obsNamesRaw = article.records?.observations?.names || '';
            const obsCodesRaw = article.records?.observations?.codes || '';

            const obsNames = obsNamesRaw.split(/[;；]/).map(n => n.trim()).filter(n => n && !allCollectionNames.has(n));
            const obsCodes = obsCodesRaw.split(/[;；]/).map(c => c.trim()).filter(c => c && !allCollectionCodes.has(c));

            article.records.observations.names = obsNames.join(';');
            article.records.observations.codes = obsCodes.join(';');

            return article;
          })
          // 1-3. collections と observations の両方が空なら除外
          .filter(article => {
            const obsEmpty = !article.records?.observations?.names && !article.records?.observations?.codes;
            const colEmpty = !article.records?.collections?.names && !article.records?.collections?.codes;
            return !(obsEmpty && colEmpty);
          });


          // 2. 分布情報の生成
          const placeToIndices = new Map(); // 地名 → [article番号]

          data.articles.forEach((article, index) => {
            const articleIndex = index + 1; // 1-based

            const collectionNames = article.records?.collections?.names || '';
            const observationNames = article.records?.observations?.names || '';

            const allNames = [...collectionNames.split(/[;；]/), ...observationNames.split(/[;；]/)]
              .map(n => n.trim())
              .filter(n => n && n !== '詳細不明');

            allNames.forEach(name => {
              if (!placeToIndices.has(name)) {
                placeToIndices.set(name, []);
              }
              if (!placeToIndices.get(name).includes(articleIndex)) {
                placeToIndices.get(name).push(articleIndex);
              }
            });
          });

          // 表示用地名を生成（sup付き）
          const formattedPlaceMap = new Map();
          for (const [place, indices] of placeToIndices.entries()) {
            const indexText = indices.map(i => `${i})`).join('');
            formattedPlaceMap.set(place, `${place}<sup>${indexText}</sup>`);
          }

          // collections と observations を分けて表示
          const collectionNamesRaw = data.articles
            .map(article => article.records?.collections?.names)
            .filter(name => name)
            .flatMap(name => name.split(/[;；]/).map(n => n.trim()).filter(n => n));

          const observationNamesRaw = data.articles
            .map(article => article.records?.observations?.names)
            .filter(name => name)
            .flatMap(name => name.split(/[;；]/).map(n => n.trim()).filter(n => n));

          const collectionSet = new Set(collectionNamesRaw.filter(n => n && n !== '詳細不明'));
          const filteredObservations = observationNamesRaw.filter(n => n && !collectionSet.has(n));

          // 重複を除いて表示用に整形
          const collectionText = Array.from(new Set(collectionNamesRaw))
            .map(name => formattedPlaceMap.get(name))
            .join(';');

          const observationsText = filteredObservations.length > 0
            ? `（参考：${Array.from(new Set(filteredObservations))
                .map(name => formattedPlaceMap.get(name))
                .join(';')}）`
            : '';

          const distributionMemo = document.getElementById('distribution_memo');
          distributionMemo.style.display = observationsText ? 'block' : 'none';

          const combinedText = [collectionText, observationsText].filter(t => t).join(' ').trim();
          document.getElementById('distribution_info').innerHTML = combinedText || 'データがありません';



          // 3. 関連文献の生成
          // article.id が無いケースがあるため、collapse の id は必ずユニークに生成する
          const articles_info = document.getElementById('articles_info');
          const articlesText = data.articles.reduce((str, article, idx) => {
            const rawKey = (article?.id ?? article?.code ?? idx);
            const safeKey = String(rawKey).replace(/[^a-zA-Z0-9_-]/g, '_');
            const collapseId = `article_${safeKey}_${idx}`;

            const shortSummary = article?.short_summary ?? '';
            const fullSummary = article?.full_summary ?? '';
            const editHref = `./records/${article.code}_${data.species.species_id}/edit`;

            return str
              + '<li>'
              +   '<span class="ms-4">'
              +     `<a class="text-decoration-none text-dark" data-bs-toggle="collapse" href="#${collapseId}" role="button" aria-expanded="false" aria-controls="${collapseId}">${shortSummary}</a>`
              +   '</span>'
              +   `<span class="collapse" id="${collapseId}"> ${fullSummary}</span>`
              +   `<a class="text-dark" href="${editHref}">${edit_icon}</a>`
              + '</li>';
          }, '');

          articles_info.innerHTML = articlesText.trim() || '関連する記事はありません';


          // 4. 備考の設定
          const memo = document.getElementById('memo');
          memo.innerText = data.memo !== undefined ? data.memo : '';



          // 5. 地図の描画
          const obsSet = new Set();
          const colSet = new Set();

          data.articles.forEach(article => {
            const obsCodes = article.records?.observations?.codes || '';
            const colCodes = article.records?.collections?.codes || '';

            obsCodes.split(/[;；]/).map(c => c.trim()).filter(c => c).forEach(c => obsSet.add(c));
            colCodes.split(/[;；]/).map(c => c.trim()).filter(c => c).forEach(c => colSet.add(c));
          });

          const mapdata = {
            observations: Array.from(obsSet).sort(),
            collections: Array.from(colSet).sort()
          };

          async function renderMap(map) {
            const svg = await drawMapFromJson(mapdata, '19_yamanashi');
            map.innerHTML = svg;
          }

          const map = document.getElementById('map');
          renderMap(map);

          // 6. 登録者の設定
          const userNamesSet = new Set();
          data.articles.forEach(article => {
            if (article.user_name) {
              userNamesSet.add(article.user_name);
            }
          });
          const userNamesArray = Array.from(userNamesSet);
          const usernames = userNamesArray.join('; ');
          const usernamesElement = document.getElementById('usernames');
          if (usernamesElement) {
            usernamesElement.innerText = usernames || '';
          }

          return true;
      })
});
DOM.keyword?.addEventListener("keydown", e => {
      if (e.key === "Enter") {
        Search.generateKeywordQuery();
        Search.searchPage();
      }  
      return false;
});
DOM.searchBtn?.addEventListener('click', () => {
    Search.generateKeywordQuery();
    Search.searchPage();
});

// ページロード時に初期化
document.addEventListener('DOMContentLoaded', () => {
  // モーダルが開いている時は何もしない
  if (DOM.modal?.classList.contains('show')) return;
  Search.init();
});
