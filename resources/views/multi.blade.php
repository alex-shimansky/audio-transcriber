<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Upload Audio File</title>
    @vite(['resources/css/app.css', 'resources/js/upload.js'])
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-6">
<div class="w-full max-w-xl bg-white p-6 rounded-xl shadow-lg">
    <h1 class="text-2xl font-bold mb-4">Upload Audio File</h1>

    <p class="text-gray-600 mb-4">
        This service allows you to upload an audio file and get automatic transcription using Amazon Transcribe.
        You can optionally provide your email to receive the transcription when it's ready.
    </p>

    <p class="text-gray-600 mb-4">
        Supported file formats: <strong>MP3, MP4, WAV, FLAC, OGG, AMR, WebM</strong>.<br>
        The audio can be in one of the following languages:
        <strong>English, Ukrainian, Russian</strong>.
    </p>

    <div class="mb-4">
        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
        <input type="email" id="email" class="mt-1 block w-full border border-gray-300 rounded p-2">
    </div>

    <div id="drag-drop-area" class="mb-4 min-h-[300px]"></div>

    <div class="mb-4">
        <progress id="upload-progress" value="0" max="100" class="w-full hidden"></progress>
    </div>

    <div id="status-container" class="mt-6 hidden">
        <p id="status-text" class="text-gray-700">Checking status...</p>
        <pre id="transcription-text" class="mt-2 text-sm bg-gray-100 p-2 rounded hidden"></pre>
    </div>
</div>
</body>
</html>
