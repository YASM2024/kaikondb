//import { createApp } from 'https://unpkg.com/vue@3/dist/vue.esm-browser.js';
import { PaginationComponent } from '../../fetch_data_test/pagination.js'; // パスを適宜変更してください
import { config } from './config.js';
baseUrl = config.baseUrl;

const app = Vue.createApp({
    data() {
        return {
            showCategory: null,
            list: null,
            metadata: {
                info: {},
                items: {},
                pagination: {
                    current: 1,
                    last: 1,
                    per: 10,
                    from: 0,
                    to: 0,
                    total: 0
                }
            },
            baseUrl: `${baseUrl}/species/search?`,
            category: '',
            code: '',
            keywords: '',
            page: '1',
            url: '',
            conditions: [],
            pages: [],
            processing: false,
            isFirstScreen: true,
        };
    },
    components: {
        'pagination-area': PaginationComponent,
        'order-area': {
            props: ['json_data', 'from'],
            emits: ['item-clicked'],
            template: `
                <div class="zebra my-4 mx-0 mx-sm-3">
                    <div class="item row fw-bold">
                        <div class="col-1">#</div><div class="col-8 col-md-4 ps-4">目</div><div class="col d-none d-md-block">Order</div><div class="col-3 col-md-2 text-end">種数</div>
                    </div>
                    <div class="item row" v-on:click="$emit('item-clicked', 'order', ele.order_id)"
                     v-for="(ele, index) in json_data" :key="ele.order_id" >
                        <div class="col-1">{{ index + from }}</div><div class="col-8 col-md-4 ps-4">{{ ele.order_ja }}</div><div class="col d-none d-md-block">{{ ele.order }}</div><div class="col-3 col-md-2 text-end">{{ ele.count.toLocaleString() }}種</div>
                    </div>
                </div>` 
        },
        'family-area': {
            props: ['json_data', 'from'],
            emits: ['item-clicked'],
            template: `
                <div class="zebra my-4 mx-0 mx-sm-3">
                    <div class="item row fw-bold">
                        <div class="col-1">#</div><div class="col-8 col-md-4 ps-4">科</div><div class="col d-none d-md-block">Family</div><div class="col-3 col-md-2 text-end">種数</div>
                    </div>
                    <div class="item row" v-for="(ele, index) in json_data" :key="ele.code" v-on:click="handleClick(ele.code)">
                        <div class="col-1">{{ index + from }}</div><div class="col-8 col-md-4 ps-4">{{ ele.family_ja }}</div><div class="col d-none d-md-block">{{ ele.family }}</div><div class="col-3 col-md-2 text-end">{{ ele.count.toLocaleString() }}種</div>
                    </div>
                </div>`,
            methods: {
                handleClick(code) {
                    this.$emit('item-clicked', 'family', code, '1');
                }
            }
        },
        'species-area': {
            props: ['json_data', 'from'],
            emits: ['item-clicked'],
            template: `
                <div class="zebra my-4 mx-0 mx-sm-3">
                    <div class="item row fw-bold">
                        <div class="col-1">#</div><div class="col-8 col-md-4 ps-4">種名</div><div class="col d-none d-md-block">Species</div>
                    </div>
                    <div class="item row" v-for="(ele, index) in json_data" :key="ele.random_key">
                        <div class="col-1">{{ index + from }}</div><div class="col-8 col-md-4 ps-4">{{ ele.species_ja }}</div><div class="col d-none d-md-block">{{ ele.species }}</div>
                    </div>
                </div>`
        }
    },
    methods: {
        fetchData() {
            if (this.processing) return;
            this.processing = true;
            let queryParams = new URLSearchParams();
            if (this.category) queryParams.append('category', this.category);
            if (this.code) queryParams.append('code', this.code);
            if (this.keywords) queryParams.append('keyword', this.keywords);
            if (this.page) queryParams.append('page', this.page);

            this.url = `${this.baseUrl}${queryParams.toString()}`;

            fetch(this.url)
            .then(response => response.json())
            .then(json => {
                this.list = json.data;
                this.metadata = {
                    'info': {
                        'order': json.order,
                        'family': json.family,
                        'keywords': json.keyword,
                        'species_count': json.species_count.toLocaleString()
                    },
                    'items': {
                        'from': json.from,
                        'to': json.to,
                        'total': json.total
                    },
                    'pagination': {
                        'current': json.current_page,
                        'last': json.last_page,
                        'per': json.per_page,
                        'from': json.from,
                        'to': json.to,
                        'total': json.total
                    }
                };
        
                this.showCategory = (json.family && json.family.length !== 0) ? 'family'
                    : (json.order && json.order.length !== 0) ? 'order'
                    : (json.keyword !== '' && json.keyword !== null) ? 'family'
                    : null;
            })
            .catch(error => {
                console.error('Error:', error); // エラーハンドリング
            })
            .finally(()=>{
                this.processing = false;
            });
            console.log(this.conditions);
        },
        updateAndFetch(category = null, code = null, page = null) {
            if(this.processing) return;
            if (category && typeof category == 'string') { this.category = String(category); }
            if (code && (typeof code == 'number' || typeof code == 'string')) { this.code = String(code); }
            if (page && (typeof page == 'number' || typeof page == 'string')) { this.page = String(page); }
            this.conditions.push({
                'keywords': this.keywords || '',
                'category': this.category || '',
                'code': this.code || '',
                'page': this.page || '1',
                'scrollPosition': window.scrollY
            });
            this.fetchData();
            window.scrollTo(0, 0);
            this.isFirstScreen = false;
        },
        goBack() {
            if(this.isFirstScreen | this.processing) return;
            this.conditions.pop();
            const lastCondition = this.conditions[this.conditions.length - 1];
            if (lastCondition) {
                this.keywords = lastCondition.keywords || '';
                this.category = lastCondition.category || '';
                this.code = lastCondition.code || '';
                this.page = lastCondition.page || '1';
                this.fetchData();
                window.scrollTo(0, lastCondition.scrollPosition);
            }
            if(this.conditions.length < 2){
                this.isFirstScreen = true;
            }
        }
    },
    mounted() {
        this.conditions.push({
            'keywords': this.keywords,
            'category': this.category,
            'code': this.code,
            'page': this.page,
            'scrollPosition': window.scrollY
        });
        this.fetchData();
    },
    template: `
        <div>
            <input type="hidden" v-model="category">
            <input type="hidden" v-model="code">
            <input type="hidden" v-model="page">
            <div class="mb-3 me-4 input-group">
                <input type="text" v-model="keywords" class="form-control" placeholder="キーワード"></input>
                <input type="button" v-on:click="updateAndFetch" class="btn btn-secondary" value="検索">
            </div>
            <div v-if="metadata">
                <template v-if="metadata?.info?.family?.family_ja">
                    分類群： 
                    <span v-on:click="updateAndFetch('class', '1', null)" style="cursor: pointer;">
                        <span class="text-decoration-underline">六脚綱 Hexapoda</span>
                    </span>
                    <span>
                         ≫ <span v-on:click="updateAndFetch('order', metadata?.info?.order?.id, null)" class="text-decoration-underline" style="cursor: pointer;">{{ metadata?.info?.order?.order_ja ?? '' }} {{ metadata?.info?.order?.order ?? '' }}</span>
                    </span>
                    <span>
                         ≫ {{ metadata?.info?.family?.family_ja ?? '' }} {{ metadata?.info?.family?.family ?? '' }}（{{metadata.info.species_count}}種）
                    </span>
                </template>
                <template v-else-if="metadata?.info?.order?.order_ja">
                    分類群： 
                    <span v-on:click="updateAndFetch('class', '1', null)" style="cursor: pointer;">
                        <span class="text-decoration-underline">六脚綱 Hexapoda</span>
                    </span>
                    <span>
                         ≫ {{ metadata?.info?.order?.order_ja ?? '' }} {{ metadata?.info?.order?.order ?? '' }}（{{metadata.info.species_count}}種）
                    </span>
                </template>
                <template v-else>分類群： 六脚綱 Hexapoda（{{metadata.info.species_count}}種）</template>
                <template v-if="metadata?.info?.keywords"><br>キーワード： {{ metadata?.info?.keywords ?? '' }}
                    <span>（{{metadata.info.species_count}}種） </span>
                </template>
            </div>
            <div v-if="metadata">
                <pagination-area :data="metadata.pagination" @page-changed="updateAndFetch(null, null, $event)"></pagination-area>
            </div>
            <species-area v-if="showCategory=='family'" :json_data="list" :from="metadata.pagination.from" v-on:item-clicked="updateAndFetch('order', ele.order_id, '1')"></species-area>
            <family-area v-else-if="showCategory=='order'" :json_data="list" :from="metadata.pagination.from" v-on:item-clicked="updateAndFetch"></family-area>
            <order-area v-else="showCategory==null" :json_data="list" :from="metadata.pagination.from" v-on:item-clicked="updateAndFetch"></order-area>
            <div v-if="metadata">
                <pagination-area :data="metadata.pagination" @page-changed="updateAndFetch(null, null, $event)"></pagination-area>
            </div>
        </div>
    `
});

app.mount('#app');
