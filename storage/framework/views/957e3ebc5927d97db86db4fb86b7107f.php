<!-- resources/views/upload.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Video </title>
    <!-- Tailwind CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script>
        async function uploadVideo(event) {
            event.preventDefault();
            
            const form = document.getElementById('upload-form');
            const formData = new FormData(form);
            
            try {
                const response = await fetch('http://127.0.0.1:8000/api/transcode', {
                    method: 'POST',
                    body: formData
                });

                const contentType = response.headers.get("content-type");
                let result;

                if (contentType && contentType.indexOf("application/json") !== -1) {
                    result = await response.json();
                } else {
                    throw new Error("Unexpected response format");
                }

                if (response.ok) {
                    document.getElementById('response').innerHTML = `
                        <div class="p-4 mt-4 bg-green-100 rounded-lg">
                            <h3 class="text-lg font-medium text-green-800">Video transcoded successfully</h3>
                            <p>ID: ${result.data.id}</p>
                            <p>Original URL: <a href="${result.data.original_url}" class="text-blue-600" target="_blank">${result.data.original_url}</a></p>
                            <p>Compressed URL: <a href="${result.data.compressed_url}" class="text-blue-600" target="_blank">${result.data.compressed_url}</a></p>
                            <p>Transcoded URL: <a href="${result.data.transcoded_url}" class="text-blue-600" target="_blank">${result.data.transcoded_url}</a></p>
                        </div>
                    `;
                } else {
                    document.getElementById('response').innerHTML = `
                        <div class="p-4 mt-4 bg-red-100 rounded-lg">
                            <h3 class="text-lg font-medium text-red-800">Error: ${result.message}</h3>
                        </div>
                    `;
                }
            } catch (error) {
                document.getElementById('response').innerHTML = `
                    <div class="p-4 mt-4 bg-red-100 rounded-lg">
                        <h3 class="text-lg font-medium text-red-800">Error: ${error.message}</h3>
                    </div>
                `;
            }
        }
    </script>
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">
    <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-md">
        <h1 class="text-2xl font-bold mb-6 text-center">Upload a Video to Transcode</h1>

        </h1>
        <form id="upload-form" onsubmit="uploadVideo(event)" class="space-y-6">
            <?php echo csrf_field(); ?>
            <div class="form-group">
                <label for="video" class="block text-sm font-medium text-gray-700">Select Video:</label>
                <input type="file" name="video" id="video" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
            </div>
            <button type="submit" class="w-full bg-indigo-600 text-white py-2 px-4 rounded-md shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">Upload</button>
        </form>
        <div id="response"></div>
    </div>
</body>
</html>
<?php /**PATH /home/foysal/Development/video-transcoder/resources/views/upload.blade.php ENDPATH**/ ?>