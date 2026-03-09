import { appConfig, apiPaths, domSelectors, messages } from './constants.js';

const baseUrl = appConfig.baseUrl;

const app = Vue.createApp({
  data() {
    return {
      baseUrl,
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
    <div>
      <label class="form-label">設定内容取込み</label>
      <input
        type="file"
        class="form-control"
        @change="uploadFile"
        :disabled="uploading"
      >
      <div v-if="uploading">{{ messages.uploadNow }}</div>
      <div v-if="message">{{ message }}</div>
    </div>
  `,
});

app.mount('#app');