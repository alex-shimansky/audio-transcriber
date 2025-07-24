import Uppy from '@uppy/core'
import Dashboard from '@uppy/dashboard'
import AwsS3Multipart from '@uppy/aws-s3-multipart'
import '@uppy/core/dist/style.min.css'
import '@uppy/dashboard/dist/style.min.css'
import axios from 'axios'

const uppy = new Uppy({
    restrictions: {
        maxNumberOfFiles: 1,
        allowedFileTypes: ['audio/*'],
    },
    autoProceed: true,
})

uppy.use(Dashboard, {
    inline: true,
    target: '#drag-drop-area',
    showProgressDetails: true,
    proudlyDisplayPoweredByUppy: false,
})

uppy.use(AwsS3Multipart, {
    limit: 4,
    shouldUseMultipart: () => true,

    async getUploadParameters() {
        return {}
    },

    async createMultipartUpload(file) {
        const email = document.getElementById('email')?.value || null

        const res = await axios.post('/api/s3/multipart/initiate', {
            filename: file.name,
            contentType: file.type,
            email,
        })

        file.meta.statusUrl = res.data.status_url

        return {
            uploadId: res.data.uploadId,
            key: res.data.key,
            bucket: res.data.bucket,
            metadata: {
                transcriptionId: res.data.transcription_id,
                statusUrl: res.data.status_url,
            },
        }
    },

    async prepareUploadParts(file, { uploadId, key, bucket, partNumbers }) {
        const presignedParts = await Promise.all(
            partNumbers.map(async (partNumber) => {
                const res = await axios.post('/api/s3/multipart/sign-part', {
                    uploadId,
                    key,
                    partNumber,
                })

                return {
                    partNumber,
                    url: res.data.url,
                    headers: {},
                }
            })
        )

        return presignedParts
    },

    async signPart(file, { uploadId, key, partNumber }) {
        // Этот метод вызывается внутри prepareUploadParts, но лучше добавить для явности
        const res = await axios.post('/api/s3/multipart/sign-part', {
            uploadId,
            key,
            partNumber,
        })
        return {
            url: res.data.url,
            headers: {},
        }
    },

    async completeMultipartUpload(file, { uploadId, key, parts }) {
        const res = await axios.post('/api/s3/multipart/complete', {
            uploadId,
            key,
            parts,
        })

        return {
            location: res.data.location,
        }
    },

    async abortMultipartUpload(file, { uploadId, key }) {
        try {
            await axios.post('/api/s3/multipart/abort', {
                uploadId,
                key,
            })
        } catch (err) {
            // Игнорируем ошибку
        }
    },
})

uppy.on('upload-success', (file, response) => {
    console.log('✅ upload-success called')
    console.log('file.meta:', file.meta)
    const meta = file.meta
    const statusUrl = meta.statusUrl || meta.metadata?.statusUrl

    if (statusUrl) {
        const statusContainer = document.getElementById('status-container')
        const statusText = document.getElementById('status-text')
        const textEl = document.getElementById('transcription-text')

        statusContainer?.classList.remove('hidden')
        textEl?.classList.add('hidden')
        statusText.innerText = '⏳ Transcription in progress...'

        // Добавим спиннер
        const spinner = document.createElement('div')
        spinner.className = 'animate-spin h-5 w-5 border-2 border-t-transparent border-blue-500 rounded-full inline-block ml-2'
        spinner.id = 'spinner'
        statusText.appendChild(spinner)

        window.scrollTo({ top: document.body.scrollHeight, behavior: 'smooth' })

        const intervalId = setInterval(async () => {
            try {
                const res = await axios.get(statusUrl)
                console.log('✅ Transcription completed?:', res.data)
                if (res.data.status === 2) {
                    clearInterval(intervalId)

                    statusText.innerText = '✅ Transcription completed:'
                    document.getElementById('spinner')?.remove()

                    textEl.innerText = res.data.text || '(no transcription text received)'
                    textEl.classList.remove('hidden')

                    window.scrollTo({ top: document.body.scrollHeight, behavior: 'smooth' })
                } else if (res.data.status === 'failed') {
                    clearInterval(intervalId)
                    document.getElementById('spinner')?.remove()
                    statusText.innerText = '❌ Transcription failed.'
                }
            } catch (e) {
                clearInterval(intervalId)
                document.getElementById('spinner')?.remove()
                statusText.innerText = '⚠️ Error checking status.'
                console.error(e)
            }
        }, 3000)
    }
})
