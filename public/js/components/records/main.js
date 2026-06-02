// js/components/records/main.js
import { handleSpeciesPhotoAdmin, resetSpeciesPhotoAdmin } from './species-photo-link.js';

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

const UNKNOWN_MUNICIPALITY_CODE = '199900';
/** モーダル分布情報での表示名（DB の「詳細不明」等はここに正規化） */
const UNKNOWN_MUNICIPALITY_DISPLAY = '地点不明';
const UNKNOWN_MUNICIPALITY_ALIASES = new Set(['詳細不明', '地名不明', UNKNOWN_MUNICIPALITY_DISPLAY]);

function splitPlaces(raw) {
  return (raw || '').split(/[;；]/).map(s => s.trim()).filter(Boolean);
}

function normalizePlaceName(name) {
  if (!name) return '';
  return UNKNOWN_MUNICIPALITY_ALIASES.has(name) ? UNKNOWN_MUNICIPALITY_DISPLAY : name;
}

function isUnknownPlaceName(name) {
  return name === UNKNOWN_MUNICIPALITY_DISPLAY;
}

/** names / codes を index で対応づけた { name, code } の配列 */
function placePairsFromRecord(namesRaw, codesRaw) {
  const names = splitPlaces(namesRaw);
  const codes = splitPlaces(codesRaw);
  const len = Math.max(names.length, codes.length);
  const pairs = [];

  for (let i = 0; i < len; i++) {
    let name = normalizePlaceName(names[i] || '');
    const code = codes[i];
    if (!name && code === UNKNOWN_MUNICIPALITY_CODE) {
      name = UNKNOWN_MUNICIPALITY_DISPLAY;
    }
    if (name || code) {
      pairs.push({ name, code });
    }
  }

  codes.forEach(code => {
    if (code === UNKNOWN_MUNICIPALITY_CODE && !pairs.some(p => isUnknownPlaceName(p.name))) {
      pairs.push({ name: UNKNOWN_MUNICIPALITY_DISPLAY, code });
    }
  });

  return pairs;
}

function placeNamesFromRecord(namesRaw, codesRaw) {
  return placePairsFromRecord(namesRaw, codesRaw)
    .map(p => p.name)
    .filter(Boolean);
}

const SPECIES_PHOTO_SLOT_COUNT = 3;
/** 縦：横＝3:4（高さ3・幅4の横長 → aspect-ratio 幅/高さ＝4/3） */
const SPECIES_PHOTO_ASPECT_WIDTH = 4;
const SPECIES_PHOTO_ASPECT_HEIGHT = 3;

/** /photos と同様に public/storage 配下の写真 URL を組み立てる */
function speciesPhotoStorageUrl(filename) {
  if (!filename) {
    return typeof window.waitImg === 'string' ? window.waitImg : '';
  }
  const base = (typeof window.homeUrl === 'string' ? window.homeUrl : '').replace(/\/$/, '');
  return `${base}/storage/photos/${encodeURIComponent(filename)}`;
}

function escapeHtml(text) {
  const el = document.createElement('div');
  el.textContent = text ?? '';
  return el.innerHTML;
}

function speciesPhotoCaption(photo) {
  const place = (photo.place ?? '').trim();
  const showName = (photo.show_name ?? '').trim();
  if (!place && !showName) {
    return '';
  }
  const atPlace = place ? `＠${place}` : '';
  const byLine = showName ? `Photoed By ${showName}` : '';
  return [atPlace, byLine].filter(Boolean).join('　');
}

function speciesPhotoSlotHtml(photo, speciesJa) {
  if (photo?.url) {
    const src = speciesPhotoStorageUrl(photo.url);
    const caption = speciesPhotoCaption(photo);
    const captionHtml = caption
      ? `<span class="species-photo-caption">${escapeHtml(caption)}</span>`
      : '';
    return `
      <div class="col-12 col-md-4">
        <div class="position-relative species-photo-wrap w-100">
          <div
            class="species-photo-frame"
            style="aspect-ratio: ${SPECIES_PHOTO_ASPECT_WIDTH} / ${SPECIES_PHOTO_ASPECT_HEIGHT};"
          >
            <img
              src="${escapeHtml(src)}"
              alt="${escapeHtml(speciesJa)}"
              class="species-photo-img"
              loading="lazy"
            >
          </div>
          ${captionHtml}
        </div>
      </div>`;
  }

  return `
    <div class="col-12 col-md-4">
      <div class="species-photo-wrap w-100">
        <div
          class="species-photo-frame species-photo-placeholder"
          style="aspect-ratio: ${SPECIES_PHOTO_ASPECT_WIDTH} / ${SPECIES_PHOTO_ASPECT_HEIGHT};"
          role="img"
          aria-label="写真なし"
        >
          <span class="species-photo-placeholder-text">写真なし</span>
        </div>
      </div>
    </div>`;
}

function renderSpeciesPhotoSlots(photos, speciesJa) {
  const container = document.getElementById('species_photos');
  if (!container) {
    return;
  }

  const list = Array.isArray(photos) ? photos.slice(0, SPECIES_PHOTO_SLOT_COUNT) : [];
  const slots = Array.from({ length: SPECIES_PHOTO_SLOT_COUNT }, (_, i) =>
    speciesPhotoSlotHtml(list[i], speciesJa)
  );

  container.innerHTML = slots.join('');
}

// イベント登録
DOM.modal?.addEventListener('show.bs.modal', event => {
    const button1 = event.relatedTarget;
    if (!button1) {
      return;
    }

    renderSpeciesPhotoSlots([], '');
    resetSpeciesPhotoAdmin();

    const speciesKey = button1.getAttribute('data-bs-whatever');
    if (!speciesKey) {
      return;
    }

    const url2 = `./species/${encodeURIComponent(speciesKey)}/show`;
    fetch(url2)
      .then((response) => {
        if (!response.ok) {
          throw new Error(`HTTP ${response.status}`);
        }
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

          if (!handleSpeciesPhotoAdmin(data)) {
            renderSpeciesPhotoSlots(data.photos, data.species.species_ja);
          }

          // 1-1. 全体の collections の names と codes を収集
          const allCollectionNames = new Set();
          const allCollectionCodes = new Set();

          data.literatures.forEach(literature => {
            const namesRaw = literature.records?.collections?.names || '';
            const codesRaw = literature.records?.collections?.codes || '';

            placeNamesFromRecord(namesRaw, codesRaw).forEach(n => allCollectionNames.add(n));
            splitPlaces(codesRaw).forEach(c => allCollectionCodes.add(c));
          });

          // 1-2. 各 literature の observations から collections に含まれるものを除去
          data.literatures = data.literatures.map(literature => {
            
            const obsNamesRaw = literature.records?.observations?.names || '';
            const obsCodesRaw = literature.records?.observations?.codes || '';

            const filteredObsPairs = placePairsFromRecord(obsNamesRaw, obsCodesRaw).filter(({ name, code }) => {
              if (name && allCollectionNames.has(name)) return false;
              if (code && allCollectionCodes.has(code)) return false;
              return true;
            });

            literature.records.observations.names = filteredObsPairs
              .map(p => p.name)
              .filter(Boolean)
              .join(';');
            literature.records.observations.codes = filteredObsPairs
              .map(p => p.code)
              .filter(Boolean)
              .join(';');

            return literature;
          })
          // 1-3. collections と observations の両方が空なら除外
          .filter(literature => {
            const obsEmpty = placePairsFromRecord(
              literature.records?.observations?.names,
              literature.records?.observations?.codes
            ).length === 0;
            const colEmpty = placePairsFromRecord(
              literature.records?.collections?.names,
              literature.records?.collections?.codes
            ).length === 0;
            return !(obsEmpty && colEmpty);
          });


          // 2. 分布情報の生成
          const placeToIndices = new Map(); // 地名 → [literature番号]

          data.literatures.forEach((literature, index) => {
            const literatureIndex = index + 1; // 1-based

            const allNames = [
              ...placeNamesFromRecord(
                literature.records?.collections?.names,
                literature.records?.collections?.codes
              ),
              ...placeNamesFromRecord(
                literature.records?.observations?.names,
                literature.records?.observations?.codes
              ),
            ];

            allNames.forEach(name => {
              if (!placeToIndices.has(name)) {
                placeToIndices.set(name, []);
              }
              if (!placeToIndices.get(name).includes(literatureIndex)) {
                placeToIndices.get(name).push(literatureIndex);
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
          const collectionNamesRaw = data.literatures.flatMap(literature =>
            placeNamesFromRecord(
              literature.records?.collections?.names,
              literature.records?.collections?.codes
            )
          );

          const observationNamesRaw = data.literatures.flatMap(literature =>
            placeNamesFromRecord(
              literature.records?.observations?.names,
              literature.records?.observations?.codes
            )
          );

          const formatPlaceLabel = (name, formattedPlaceMap) =>
            formattedPlaceMap.get(name) ?? name;

          const collectionSet = new Set(collectionNamesRaw);
          const filteredObservations = observationNamesRaw.filter(n => n && !collectionSet.has(n));

          // 重複を除いて表示用に整形
          const collectionText = Array.from(new Set(collectionNamesRaw))
            .map(name => formatPlaceLabel(name, formattedPlaceMap))
            .filter(Boolean)
            .join(';');

          const observationsText = filteredObservations.length > 0
            ? `（参考：${Array.from(new Set(filteredObservations))
                .map(name => formatPlaceLabel(name, formattedPlaceMap))
                .filter(Boolean)
                .join(';')}）`
            : '';

          const distributionMemo = document.getElementById('distribution_memo');
          if (distributionMemo) {
            distributionMemo.style.display = observationsText ? 'block' : 'none';
          }

          const combinedText = [collectionText, observationsText].filter(t => t).join(' ').trim();
          document.getElementById('distribution_info').innerHTML = combinedText || 'データがありません';



          // 3. 関連文献の生成
          // literature.id が無いケースがあるため、collapse の id は必ずユニークに生成する
          const literatures_info = document.getElementById('literatures_info');
          const literaturesText = data.literatures.reduce((str, literature, idx) => {
            const rawKey = (literature?.id ?? literature?.code ?? idx);
            const safeKey = String(rawKey).replace(/[^a-zA-Z0-9_-]/g, '_');
            const collapseId = `literature_${safeKey}_${idx}`;

            const shortSummary = literature?.short_summary ?? '';
            const fullSummary = literature?.full_summary ?? '';
            const editHref = `./records/${literature.code}_${data.species.species_id}/edit`;

            return str
              + '<li>'
              +   '<span class="ms-4">'
              +     `<a class="text-decoration-none text-dark" data-bs-toggle="collapse" href="#${collapseId}" role="button" aria-expanded="false" aria-controls="${collapseId}">${shortSummary}</a>`
              +   '</span>'
              +   `<span class="collapse" id="${collapseId}"> ${fullSummary}</span>`
              +   `<a class="text-dark" href="${editHref}">${edit_icon}</a>`
              + '</li>';
          }, '');

          literatures_info.innerHTML = literaturesText.trim() || '関連する文献はありません';


          // 4. 備考の設定
          const memo = document.getElementById('memo');
          memo.innerText = data.memo !== undefined ? data.memo : '';



          // 5. 地図の描画
          const obsSet = new Set();
          const colSet = new Set();

          data.literatures.forEach(literature => {
            const obsCodes = literature.records?.observations?.codes || '';
            const colCodes = literature.records?.collections?.codes || '';

            obsCodes.split(/[;；]/).map(c => c.trim()).filter(c => c).forEach(c => obsSet.add(c));
            colCodes.split(/[;；]/).map(c => c.trim()).filter(c => c).forEach(c => colSet.add(c));
          });

          const mapdata = {
            observations: Array.from(obsSet).sort(),
            collections: Array.from(colSet).sort()
          };

          async function renderMap(map) {
            const svg = await drawMapFromJson(mapdata, window.kaikonPrefectureMap);
            map.innerHTML = svg;
          }

          const map = document.getElementById('map');
          renderMap(map);

          // 6. 登録者の設定
          const userNamesSet = new Set();
          data.literatures.forEach(literature => {
            if (literature.user_name) {
              userNamesSet.add(literature.user_name);
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
      .catch(() => {
        renderSpeciesPhotoSlots([], '');
        resetSpeciesPhotoAdmin();
      });
});

DOM.modal?.addEventListener('hidden.bs.modal', () => {
  resetSpeciesPhotoAdmin();
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
