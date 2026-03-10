// public/js/components/createCsvImportApp.js
import { appConfig, domSelectors, messages } from '../constants.js';

const baseUrl = appConfig.baseUrl;

export function createCsvImportApp({
  fileFieldName,
  importPath,
  defaultLabel = '設定内容取込み',
}) {
  return Vue.createApp({
    data() {
      return {
        baseUrl,
        messages,
        defaultLabel,
        uploading: false,
        message: '',
      };
    },

    methods: {
      async uploadFile(event) {
        const file = event.target.files[0];
        if (!file) return;

        const formData = new FormData();
        formData.append(fileFieldName, file);

        this.uploading = true;
        this.message = '';

        try {
          const response = await fetch(`${this.baseUrl}${importPath}`, {
            method: 'POST',
            headers: {
              'X-CSRF-TOKEN': document
                .querySelector(domSelectors.csrfMeta)
                .getAttribute('content'),
            },
            body: formData,
          });

          if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
          }

          this.message = this.messages.uploadSuccess;
          event.target.value = '';
        } catch (error) {
          this.message = this.messages.uploadFailure;
          console.error(error);
        } finally {
          this.uploading = false;
        }
      },
    },

    template: `
      <div>
        <label class="d-block py-3" style="cursor: pointer;">
          <template v-if="!uploading">
            <svg class="bi ms-1 me-2" width="2.4em" height="2.4em" aria-hidden="true">
              <use xlink:href="#upload"></use>
            </svg>
          </template>

          <template v-else>
            <span class="spinner-border spinner-border-sm ms-1 me-2" role="status" aria-hidden="true"></span>
          </template>

          <span style="vertical-align: super;">
            {{ uploading ? messages.uploadNow : defaultLabel }}
          </span>

          <input
            type="file"
            style="display: none"
            :disabled="uploading"
            @change="uploadFile"
          >
        </label>

        <div v-if="message" class="small mt-1 ms-1">
          {{ message }}
        </div>
      </div>
    `,
  });
}