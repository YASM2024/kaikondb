export const familiesItem = {
  props: ['index', 'code', 'familyJa', 'family', 'count'],
  template: `
    <div class="item row" @click="handleClick">
      <div class="col-1">{{ index }}</div>
      <div class="col col-md-5 ps-4">{{ familyJa }}</div>
      <div class="col d-none d-md-block">{{ family }}</div>
      <div class="col-3 col-md-2 pe-4 text-end">{{ count }}</div>
    </div>
  `,
};

export const familiesDataTable = {
  props: ['items'],
  components: { familiesItem },
  template: `
    FAMILIES DATA TABLE
    <div class="zebra mb-5 mx-0 mx-sm-3">
      <div class="row" style="background-color: #e0e0e0; padding: 0.4em 0; font-weight: bold;">
        <div class="col-1">#</div>
        <div class="col col-sm-8 col-md-5 ps-4">科</div>
        <div class="col d-none d-md-block">Family</div>
        <div class="col-3 col-md-2 text-end">種数</div>
      </div>
      <families-item 
        v-for="(item, index) in items" 
        :index="index + 1" 
        :family-ja="item.path" 
        :family="item.family" 
        :count="item.count">
      </families-item>
    </div>
  `
};
