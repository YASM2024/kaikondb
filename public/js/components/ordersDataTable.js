
export const speciesItem = {
  props: ['index', 'code', 'species_ja', 'species'],
  template: `
    <div class="item row" @click="handleClick">
      <div class="col-1">{{ index }}</div>
      <div class="col col-md-5 ps-4">{{ species_ja }}</div>
      <div class="col d-none d-md-block">{{ species }}</div>
    </div>
  `,
  methods: {
    handleClick() {
      console.log('Item clicked:', this.code);
    }
  }
};

export const ordersDataTable = {
  props: ['items'],
  components: { 'species-item': speciesItem },
  template: `
    <div class="zebra mb-5 mx-0 mx-sm-3">
      <div class="row" style="background-color: #e0e0e0; padding: 0.4em 0; font-weight: bold;">
        <div class="col-1">#</div>
        <div class="col col-sm-8 col-md-5 ps-4">和名</div>
        <div class="col d-none d-md-block">Species</div>
      </div>
      <species-item 
        v-for="(item, index) in filteredItems" 
        :key="item.code" 
        :index="index + 1" 
        :code="item.code" 
        :species_ja="item.species_ja" 
        :species="item.species">
      </species-item>
    </div>
  `,
  computed: {
    filteredItems() {
      return this.items.filter(item => item !== null);
    }
  }
};
