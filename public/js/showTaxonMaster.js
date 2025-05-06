import config from './config.js'
const baseUrl = config.baseUrl;

const app = Vue.createApp({
  data() {
    return {
      baseUrl: baseUrl,
      order_id: "none",
      family_id: "none",
      families: [],
      data: null,
      editingRowId: null,
      insertingRowId: null,
      formData: {
        id: '',
        code: '',
        species_ja: '',
        species: '',
        order_id: '',
        family_id: '',
      },
      showConfirmation: false,
    }
  },
  methods: {
    fetchFamilies() {
      fetch(`${this.baseUrl}/master/family/show?order_id=${this.order_id}`)
        .then(res => res.json())
        .then(data => {
          this.families = data;
        })
        .catch(() => {
          alert('エラーが発生しました。');
        });
    },
    showTable() {
      if (!this.family_id) return;
      fetch(`${this.baseUrl}/master/species/show?family_id=${this.family_id}`)
        .then(res => res.json())
        .then(data => {
          this.data = data;
        })
        .catch(() => {
          alert('エラーが発生しました。');
        });
    },
    openEdit(rowId) {
      this.closeInsert(); // 挿入モードを閉じる
      this.editingRowId = rowId; // 編集モードにする行のIDを設定
    },
    openInsert(rowId = null) {
      this.closeInsert(); // 既に開かれた挿入モードを閉じる
      this.editingRowId = null; // 編集モードを解除
      this.insertingRowId = 'new'; // 新規挿入用の仮のIDを設定
      const newRow = {
        id: 'new',
        code: '',
        species_ja: '',
        species: '',
        order_id: this.order_id,
        family_id: this.family_id,
      };
    
      // ヘッダのボタンが押された場合、最上行に挿入
      if (rowId === null) {
        this.data.unshift(newRow);
      } else {
        // 特定の行の前に挿入
        const rowIndex = this.data.findIndex(row => row.id === rowId);
        if (rowIndex !== -1) {
          this.data.splice(rowIndex, 0, newRow);
        }
      }
    },    
    closeEditAndInsert() {
      this.closeEdit();
      this.closeInsert();
    },
    closeEdit() {
      this.editingRowId = null; // 編集モードを解除
    },
    closeInsert() {
      this.data = this.data.filter(row => row.id !== 'new'); // 新規行を削除
      this.insertingRowId = null; // 新規挿入モードを解除
    },
    submitData(row) {
      const res = confirm("更新してもよろしいですか？元に戻すことはできません。");
      if (!res) return;
      const body = new FormData();
      if(row.id !== 'new'){
        Object.keys(row).forEach(key => body.append(key, row[key]));
      }else{
        Object.keys(row).forEach(key => { 
          if (key !== 'id') { // id を除外 
            body.append(key, row[key]); 
          } 
        });
      }

      fetch(`${this.baseUrl}/master/species/edit`, {
        method: 'POST',
        mode: 'cors',
        cache: 'no-cache',
        credentials: 'same-origin',
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        redirect: 'follow',
        referrerPolicy: 'no-referrer',
        body
      })
      .then(res => res.json())
      .then(data => {
        if (data.result === 'success') {
          alert('修正を完了しました。画面を再読み込みします。');
          this.showTable();
        } else if (data.result === 'error') {
          alert('！エラーを感知しました。画面を再読み込みします。！');
        } else {
          alert('修正に失敗しました。');
        }
      })
      .catch(() => {
        alert('エラーが発生しました。');
      })
      .finally(() => {
        this.showConfirmation = false; // 確認ダイアログを閉じる
        this.closeEditAndInsert();
      });
    },
  },
  template: `
  <div>
    <div class="row mb-2 px-3">
      <select class="form-control" v-model="order_id" v-on:change="fetchFamilies">
        <option value="none" disabled>目を選択してください</option>
        <option value="7">蜻蛉目（トンボ目）</option>
        <option value="10">直翅目（バッタ目）</option>
        <option value="15">蟷螂目（カマキリ目）</option>
        <option value="20">半翅目</option>
        <option value="23">鞘翅目</option>
        <option value="27">双翅目</option>
        <option value="28">鱗翅目</option>
        <option value="30">膜翅目</option>
      </select>
    </div>
    <div class="row mb-2 px-3">
      <select class="form-control" v-if="order_id != 'none'" v-model="family_id" v-on:change="showTable">
        <option value="none" disabled>科を選択してください</option>
        <option v-for="family in families" :key="family.id" :value="family.id">
          {{ family.family_ja }} {{ family.family }}
        </option>
      </select>
    </div>
    <table class="table table-striped table-hover">
      <thead>
        <tr class="rowItem">
          <th>
            <div class="row">
              <div class="col-8 col-sm-4">code</div>
              <div class="col-12 col-sm-8">
                <div>species_ja</div>
                <div>species</div>
              </div>
            </div>
          </th>
          <th>
            <div class="px-0 text-muted" name="addBtn">
              <svg @click="openInsert(null)" class="bi" width="1.2em" height="1.2em">
                <use xlink:href="${baseUrl}/svg/admin_symbols.svg#plus"></use>
              </svg>
            </div>
          </th>
        </tr>
      </thead>
      <tbody>
        <tr class="rowItem" v-for="row in data" :key="row.id">
          <td :class="[ 
            {'bg-danger bg-opacity-25': insertingRowId === row.id}, 
            {'bg-success bg-opacity-25': editingRowId === row.id} 
          ]">
            <div class="row">
              <div class="col-8 col-sm-4">
                <template v-if="editingRowId === row.id || insertingRowId === row.id">
                  <form ref="editForm">
                    <input class="form-control mb-1" type="text" v-model="row.code" name="code" placeholder="code">
                    <input class="d-none" v-model="row.id" type="text" name="id">
                    <input class="d-none" v-model="row.species_ja" type="text" name="species_ja">
                    <input class="d-none" v-model="row.species" type="text" name="species">
                    <input class="d-none" v-model="row.order_id" type="text" name="order_id">
                    <input class="d-none" v-model="row.family_id" type="text" name="family_id">
                  </form>
                </template>
                <template v-else>
                  {{ row.code }}
                </template>
              </div>
              <div class="col-12 col-sm-8">
                <div>
                  <template v-if="editingRowId === row.id || insertingRowId === row.id">
                    <input class="form-control mb-1" type="text" v-model="row.species_ja" placeholder="species_ja">
                  </template>
                  <template v-else>
                    {{ row.species_ja }}
                  </template>
                </div>
                <div>
                  <template v-if="editingRowId === row.id || insertingRowId === row.id">
                    <input class="form-control" type="text" v-model="row.species" placeholder="species">
                  </template>
                  <template v-else>
                    {{ row.species }}
                  </template>
                </div>
              </div>
            </div>
          </td>
          <td :class="[ 
            {'bg-danger bg-opacity-25': insertingRowId === row.id}, 
            {'bg-success bg-opacity-25': editingRowId === row.id} 
          ]">
            <span v-if="editingRowId === row.id || insertingRowId === row.id">
              <svg @click="submitData(row)" class="bi" width="1.2em" height="1.2em">
                <use xlink:href="${baseUrl}/svg/admin_symbols.svg#enter"></use>
              </svg>
              <svg @click="closeEditAndInsert" class="bi" width="1.2em" height="1.2em">
                <use xlink:href="${baseUrl}/svg/admin_symbols.svg#back"></use>
              </svg>
            </span>
            <span v-else>
              <svg @click="openEdit(row.id)" class="bi" width="1.2em" height="1.2em">
                <use xlink:href="${baseUrl}/svg/admin_symbols.svg#edit"></use>
              </svg>
              <svg @click="openInsert(row.id)" class="bi" width="1.2em" height="1.2em">
                <use xlink:href="${baseUrl}/svg/admin_symbols.svg#plus"></use>
              </svg>
            </span>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
  `
});

app.mount('#app');
