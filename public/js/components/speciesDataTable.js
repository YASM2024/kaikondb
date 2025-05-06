export const speciesItem = {
  props: ['index', 'code', 'speciesJa', 'species'],
  template: `
    <div class="item row" @click="handleClick">
      <div class="col-1">{{ index }}</div>
      <div class="col col-md-5 ps-4">{{ speciesJa }}</div>
      <div class="col d-none d-md-block">{{ species }}</div>
    </div>
  `,
};

export const speciesDataTable = {
  props: ['items'],
  components: { 'species-item': speciesItem }, // 同じく正しく設定
  template: `
    SPECIES DATA TABLE
    <div class="zebra mb-5 mx-0 mx-sm-3">
      <div class="row" style="background-color: #e0e0e0; padding: 0.4em 0; font-weight: bold;">
        <div class="col-1">#</div>
        <div class="col col-sm-8 col-md-5 ps-4">和名</div>
        <div class="col d-none d-md-block">Species</div>
      </div>
      <species-item 
        v-for="(item, index) in items" 
        :index="index + 1">
      </species-item>
    </div>
  `
};
