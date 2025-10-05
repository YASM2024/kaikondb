import config from './config.js'
const baseUrl = config.baseUrl

const app = Vue.createApp({
  data() {
    return {
      baseUrl: baseUrl,
      message: '',
      uploading: false
    }
  },
  methods: {
    async uploadFile(event) {
      const file = event.target.files[0]
      if (!file) return

      const formData = new FormData()
      formData.append('family_file', file)

      this.uploading = true
      this.message = ''

      try {
        const response = await fetch(`${this.baseUrl}/master/family/import`, {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
          },
          body: formData
        })

        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`)
        }

        this.message = 'アップロード成功！'
      } catch (error) {
        this.message = 'アップロード失敗…'
        console.error(error)
      } finally {
        this.uploading = false
      }
    }
  },
  template: `
    <div>
      <label class="d-block py-3" style="cursor: pointer;">
        <a>
          <svg class="bi ms-1 me-2" width="2.4em" height="2.4em">
            <use xlink:href="#upload"></use>
          </svg>
          <span style="vertical-align: super;">設定内容取込み</span>
        </a>
        <input type="file" style="display: none" @change="uploadFile" />
      </label>
      <p v-if="uploading">アップロード中...</p>
      <p v-if="message">{{ message }}</p>
    </div>
  `
})

app.mount('#app')
