import { config } from './config.js';
import { ordersDataTable } from './components/ordersDataTable.js';
import { familiesDataTable } from './components/familiesDataTable.js';
import { speciesDataTable } from './components/speciesDataTable.js';
const baseUrl = config.baseUrl;
const { createApp, ref } = Vue;

const app1 = createApp({
    data() {
        return {
            baseUrl: baseUrl,
            keyword: '',
            httpQuery: ''
        };
    },
    methods: {
        generateKeywordQuery() {
            // フォームからキーワードを取得してクエリを生成
            //this.httpQuery = `keyword=${encodeURIComponent(this.keyword)}`;
            this.httpQuery = `category=family&code=070010`;
        },
        searchPage(page = null) {
            this.generateKeywordQuery(); // キーワードクエリを生成
            setTimeout(() => {
                let urlHttpQuery = this.httpQuery;
                if (!urlHttpQuery) return false;
                if (!isNaN(page)) {
                    urlHttpQuery += '&page=' + page;
                }
                const url = `${this.baseUrl}/species/search?&${urlHttpQuery}`;
                // const url = './species/search?&' + urlHttpQuery;
                fetch(url)
                    .then(response => response.json())
                    .then(json => {
                        this.initializeApp2(json);
                    })
                    //.catch(error => {
                    //    console.error('Error:', error); // エラーハンドリング
                    //});
            }, 50);
        },
        initializeApp2(data) {
            createApp({
                data() {
                    return {
                        items: data.data || [], 
                        order: data.order || {},
                        family: data.family || {}
                    };
                },
                components: {
                    'orders-data-table': ordersDataTable,
                    'families-data-table': familiesDataTable,
                    'species-data-table': speciesDataTable
                }
            }).mount('#app2');
        }
    }
});

app1.mount('#app');