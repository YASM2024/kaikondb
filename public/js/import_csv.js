import { appConfig, apiPaths, domSelectors, messages } from './constants.js';

const baseUrl = appConfig.baseUrl;

const app = Vue.createApp({
  data() {
    return {
      baseUrl,
      messages,
      message: '',
      uploading: false,
    };
  },

  methods: {
    async uploadFile(event) {
      const file = event.target.files[0];
      if (!file) return;

      const formData = new FormData();
      formData.append('family_file', file);

      this.uploading = true;
      this.message = '';

      try {
        const response = await fetch(`${this.baseUrl}${apiPaths.masterFamilyImport}`, {
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

        this.message = messages.uploadSuccess;
      } catch (error) {
        this.message = messages.uploadFailure;
        console.error(error);
      } finally {
        this.uploading = false;
      }
    },
  },

  template: `
    <label class="d-block py-3" style="cursor: pointer;">
      <svg v-if="!uploading" class="bi ms-1 me-2" width="2.4em" height="2.4em">
        <use xlink:href="#upload"></use>
      </svg>

      <span v-else class="spinner-border spinner-border-sm ms-1 me-2" role="status" aria-hidden="true"></span>

      <span style="vertical-align: super;">
        {{ uploading ? messages.uploadNow : '設定内容取込み' }}
      </span>

      <input
        type="file"
        name="family_file"
        style="display:none"
        @change="uploadFile"
        :disabled="uploading"
      >
    </label>

    <div v-if="message" class="mt-2">{{ message }}</div>
  `,
});

app.mount('#app');