// js/components/masters/showTaxonMaster.js
import {
  appConfig, apiPaths, sentinelValues, resultStatus, domSelectors, messages
} from '../../constants.js';

const baseUrl = appConfig.baseUrl;

const app = Vue.createApp({
  data() {
    return {
      baseUrl,
      order_id: sentinelValues.none,
      orders: [],
      family_id: sentinelValues.none,
      families: [],
      code_header: null,
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
    };
  },

  methods: {
    fetchOrders() {
      fetch(`${this.baseUrl}${apiPaths.masterOrderShow}`)
        .then(res => res.json())
        .then(data => {
          data.sort((a, b) => a.code.localeCompare(b.code));
          this.orders = data;

          if (data.length > 0) {
            this.order_id = data[0].id;
          }
        })
        .catch(() => {
          alert(messages.commonError);
        });
    },

    fetchFamilies() {
      fetch(`${this.baseUrl}${apiPaths.masterFamilyShow}?order_id=${this.order_id}`)
        .then(res => res.json())
        .then(data => {
          this.families = data;
        })
        .catch(() => {
          alert(messages.commonError);
        });
    },

    showTable() {
      if (!this.family_id) return;

      fetch(`${this.baseUrl}${apiPaths.masterSpeciesShow}?family_id=${this.family_id}`)
        .then(res => res.json())
        .then(data => {
          this.data = data;
        })
        .catch(() => {
          alert(messages.commonError);
        });
    },

    openEdit(rowId) {
      this.closeInsert();
      this.editingRowId = rowId;
    },

    openInsert(rowId = null) {
      this.closeInsert();
      this.editingRowId = null;
      this.insertingRowId = sentinelValues.newRowId;

      const newRow = {
        id: sentinelValues.newRowId,
        code: this.code_header,
        species_ja: '',
        species: '',
        order_id: this.order_id,
        family_id: this.family_id,
      };

      if (rowId === null) {
        this.data.unshift(newRow);
      } else {
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
      this.editingRowId = null;
    },

    closeInsert() {
      if (!this.data) return;

      this.data = this.data.filter(row => row.id !== sentinelValues.newRowId);
      this.insertingRowId = null;
    },

    submitData(row) {
      const res = confirm(messages.submitConfirm);
      if (!res) return;

      const body = new FormData();

      if (row.id !== sentinelValues.newRowId) {
        Object.keys(row).forEach(key => body.append(key, row[key]));
      } else {
        Object.keys(row).forEach(key => {
          if (key !== 'id') {
            body.append(key, row[key]);
          }
        });
      }

      fetch(`${this.baseUrl}${apiPaths.masterSpeciesEdit}`, {
        method: 'POST',
        mode: 'cors',
        cache: 'no-cache',
        credentials: 'same-origin',
        headers: {
          'X-CSRF-TOKEN': document.querySelector(domSelectors.csrfMeta).getAttribute('content'),
        },
        redirect: 'follow',
        referrerPolicy: 'no-referrer',
        body,
      })
        .then(res => res.json())
        .then(data => {
          if (data.result === resultStatus.success) {
            alert(messages.submitSuccess);
            this.showTable();
          } else if (data.result === resultStatus.error) {
            alert(messages.submitDetectedError);
          } else {
            alert(messages.submitFailure);
          }
        })
        .catch(() => {
          alert(messages.commonError);
        })
        .finally(() => {
          this.showConfirmation = false;
          this.closeEditAndInsert();
        });
    },
  },

  watch: {
    order_id() {
      this.family_id = sentinelValues.none;
      this.families = [];
      this.data = null;
      this.code_header = null;
      this.editingRowId = null;
      this.insertingRowId = null;
      this.fetchFamilies();
    },

    family_id(newId) {
      const selectedFamily = this.families.find(family => family.id === newId);

      if (selectedFamily) {
        this.code_header = selectedFamily.code;
        this.showTable();
      } else {
        this.code_header = null;
        this.data = null;
      }
    },
  },

  mounted() {
    this.fetchOrders();
  },

  template: `
    <div>
      <div class="mb-3">
        <label class="form-label">目を選択してください</label>
        <select v-model="order_id" class="form-select">
          <option
            v-for="order in orders"
            :key="order.id"
            :value="order.id"
          >
            {{ order.order_ja }} {{ order.order }}
          </option>
        </select>
      </div>

      <div class="mb-3">
        <label class="form-label">科を選択してください</label>
        <select v-model="family_id" class="form-select">
          <option
            v-for="family in families"
            :key="family.id"
            :value="family.id"
          >
            {{ family.family_ja }} {{ family.family }}
          </option>
        </select>
      </div>

      <table v-if="data" class="table table-bordered">
        <thead>
          <tr>
            <th>
              code
              <button
                type="button"
                class="btn btn-sm btn-outline-primary ms-2"
                @click="openInsert()"
              >
                ＋
              </button>
            </th>
            <th>species_ja</th>
            <th>species</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="row in data" :key="row.id">
            <template v-if="editingRowId === row.id || insertingRowId === row.id">
              <td><input v-model="row.code" class="form-control"></td>
              <td><input v-model="row.species_ja" class="form-control"></td>
              <td><input v-model="row.species" class="form-control"></td>
              <td class="text-nowrap">
                <button
                  type="button"
                  class="btn btn-sm btn-primary me-2"
                  @click="submitData(row)"
                >
                  保存
                </button>
                <button
                  type="button"
                  class="btn btn-sm btn-secondary"
                  @click="closeEditAndInsert()"
                >
                  取消
                </button>
              </td>
            </template>

            <template v-else>
              <td>{{ row.code }}</td>
              <td>{{ row.species_ja }}</td>
              <td>{{ row.species }}</td>
              <td class="text-nowrap">
                <button
                  type="button"
                  class="btn btn-sm btn-outline-primary me-2"
                  @click="openEdit(row.id)"
                >
                  編集
                </button>
                <button
                  type="button"
                  class="btn btn-sm btn-outline-success"
                  @click="openInsert(row.id)"
                >
                  上に追加
                </button>
              </td>
            </template>
          </tr>
        </tbody>
      </table>
    </div>
  `,
});

app.mount('#app');