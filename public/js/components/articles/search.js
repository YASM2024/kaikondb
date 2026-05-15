const home_url = window.home_url;
const authenticated = window.authenticated;
const searchResult = window.searchResult;

export const SearchModule = {
    generateQuery(){
        let optionNames = ['author', 'journal_code', 'keyword', 'order_id', 'year'];
        let formData = Object;
        let searchFlg = false;
        optionNames.forEach(optionName => {
            let strOptionInput = document.forms['search'].elements[optionName].value;
            formData[optionName] = strOptionInput ?? '';
            if (strOptionInput) searchFlg = true;
        });
        httpquery.value = searchFlg ? new URLSearchParams(formData).toString() : '';
    },
    refreshPage(){
      app.innerHTML = '';
      number_of_show.innerText = '';
      next_page_loader.innerHTML = '';
    },
    renderSearchHeader(jsonx, search_option, page) {
        if (page !== '' && page !== 1) return;

        app.insertAdjacentHTML('beforeend', `<h4 class="my-3 px-3 px-md-0">${searchResult}</h4>`);

        if (jsonx.too_many) {
          app.insertAdjacentHTML('beforeend', 'ヒット件数が100件を超えました。検索条件をご確認ください。<br>');
        } else if (jsonx.total === 0) {
          app.insertAdjacentHTML('beforeend', '該当はありませんでした。<br>');
        } else {
          app.insertAdjacentHTML('beforeend', `${jsonx.total}件がヒットしました。<br>`);
        }

        app.insertAdjacentHTML('beforeend', `検索条件：${search_option.join(' ')}<br><hr>`);
    },
    renderSearchResults(data) {
        if (!data || data.length === 0) return;
        data.forEach((item, index) => {
          let html = `
            <a href="" class="article_title text-decoration-none" 
              data-bs-toggle="modal" 
              data-bs-target="#ModalItemDetail" 
              data-bs-whatever="${item.random_id}">
              ${item.title}
            </a>
          `;

          if (item.document === 1) {
            html += '<span class="badge rounded-pill bg-danger mx-1">PDF</span>';
          }
          if (item.inventory === 1) {
            html += '<span class="badge rounded-pill bg-secondary mx-1">Inventory</span>';
          }

          html += `
            <div id="popover-content-${index + 1}" class="d-none">
              <a class="btn btn-outline-secondary" href="./literatures/${item.random_id}/edit">編集</a>
              <a class="btn btn-outline-danger" href="./literatures/${item.random_id}/delete">削除</a>
            </div>
            <div>${item.summary}</div><hr>
          `;

          app.insertAdjacentHTML('beforeend', html);
        });
    },
    async renderPagination(json) {
        const nextPageLoaderInstance = new NextPageLoader({
          current_page: json.current_page,
          last_page: json.last_page,
          per_page: json.per_page,
          total: json.total
        });

        const created = nextPageLoaderInstance.printBtn();
        if (created) {
            const nextPageBtn = document.getElementById('next_page_btn');
            if (nextPageBtn) {
                nextPageBtn.addEventListener('click', () => {
                    const currentPage = parseInt(nextPageBtn.getAttribute('data-current-page'));
                    SearchModule.searchPage(currentPage + 1);
                });
            }
        }
        nextPageLoaderInstance.printMsg();
    },
    searchPage(page = '') {
        setTimeout(async () => {
          number_of_show.innerText = "";

          const urlHttpQuery = httpquery.value;
          if (!urlHttpQuery) return false;

          const url = `${home_url}/literatures/search?&${urlHttpQuery}&page=${page}`;

          try {
            const response = await fetch(url);
            const jsonx = await response.json();

            const search_option = Object.entries(jsonx.search_option)
              .filter(([_, value]) => value !== null && value !== '')
              .map(([_, value]) => value);

            this.renderSearchHeader(jsonx, search_option, page);
            this.renderSearchResults(jsonx.data);
            this.renderPagination(jsonx);

            return true;
          } catch (error) {
            console.error("検索失敗:", error);
            return false;
          }
        }, 50);
    }
};