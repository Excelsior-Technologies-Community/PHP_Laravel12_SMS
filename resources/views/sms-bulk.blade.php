<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bulk SMS Upload</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { background: linear-gradient(to right, #0f172a, #1e293b); }
        .glass { background: rgba(255,255,255,0.08); backdrop-filter: blur(12px); border: 1px solid rgba(255,255,255,0.1); }
        .dropzone { border: 3px dashed rgba(255,255,255,0.2); transition: all 0.3s; cursor: pointer; }
        .dropzone:hover, .dropzone.dragover { border-color: #8b5cf6; background: rgba(139,92,246,0.1); }
        .file-info { display: none; }
        .file-info.show { display: block; }
    </style>
</head>
<body class="min-h-screen text-white">
    <div class="max-w-4xl mx-auto px-5 py-10">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold">📤 Bulk SMS Upload</h1>
            <a href="{{ url('/sms') }}" class="bg-indigo-500 hover:bg-indigo-600 px-4 py-2 rounded-xl transition">← Back</a>
        </div>

        @if(session('success'))
            <div class="bg-green-500/20 border border-green-400 text-green-200 px-5 py-4 rounded-2xl mb-6">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="bg-red-500/20 border border-red-400 text-red-200 px-5 py-4 rounded-2xl mb-6">{{ session('error') }}</div>
        @endif

        <div class="glass rounded-3xl p-8">
            <div class="mb-6">
                <h2 class="text-2xl font-bold mb-2">Upload CSV File</h2>
                <p class="text-slate-400">CSV format: <code class="bg-slate-800 px-3 py-1 rounded">number,message</code></p>
                <p class="text-slate-400 text-sm mt-1">Or use: <code class="bg-slate-800 px-3 py-1 rounded">phone,sms</code> or <code class="bg-slate-800 px-3 py-1 rounded">mobile,text</code></p>
                <p class="text-slate-400 text-sm mt-1">Max file size: <span class="text-yellow-400">2MB</span></p>
            </div>

            <form method="POST" action="{{ url('/sms/bulk') }}" enctype="multipart/form-data" class="space-y-6">
                @csrf

                <div>
                    <label class="block text-sm mb-2">Select Template (Optional)</label>
                    <select name="template_id" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 outline-none">
                        <option value="">-- No Template --</option>
                        @foreach($templates ?? [] as $template)
                            <option value="{{ $template->id }}">{{ $template->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Dropzone -->
                <div class="dropzone rounded-3xl p-12 text-center" id="dropzone">
                    <div class="text-6xl mb-4">📁</div>
                    <h3 class="text-xl font-bold mb-2">Drop your CSV file here</h3>
                    <p class="text-slate-400 text-sm mb-4">or click to browse</p>
                    <input type="file" name="file" id="fileInput" accept=".csv,.txt" required 
                        class="block w-full text-sm text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:bg-indigo-500 file:text-white hover:file:bg-indigo-600 file:cursor-pointer">
                    <div id="fileInfo" class="file-info mt-4 p-4 bg-slate-800 rounded-xl">
                        <p class="text-sm">📎 Selected: <span id="fileName" class="text-indigo-400"></span></p>
                        <p class="text-sm text-slate-400">Size: <span id="fileSize"></span></p>
                    </div>
                </div>

                <div class="flex gap-4">
                    <button type="submit" class="flex-1 bg-gradient-to-r from-indigo-500 to-pink-500 hover:scale-105 transition py-3 rounded-xl font-bold text-lg">
                        🚀 Upload & Process
                    </button>
                    <a href="{{ url('/sms/templates') }}" class="bg-slate-700 hover:bg-slate-600 px-6 py-3 rounded-xl transition text-center">
                        📝 Templates
                    </a>
                </div>
            </form>

            <div class="mt-6 p-4 bg-slate-900/50 rounded-xl">
                <h4 class="font-bold mb-2">📌 Sample CSV Format</h4>
                <pre class="text-xs text-slate-300 bg-slate-800 p-3 rounded overflow-x-auto">number,message
+919876543210,Welcome to our service!
+919876543211,Your OTP is 123456
+919876543212,Thank you for registering</pre>
            </div>

            <div class="mt-4 p-4 bg-yellow-500/10 border border-yellow-500/30 rounded-xl">
                <h4 class="font-bold text-yellow-400">⚠️ Note</h4>
                <ul class="text-sm text-slate-300 list-disc list-inside mt-2">
                    <li>File must be in CSV format</li>
                    <li>First row must contain headers (number, message)</li>
                    <li>Phone numbers will be automatically formatted with +91 prefix</li>
                    <li>Messages will be processed in background via queue</li>
                    <li>Maximum 1000 messages per upload</li>
                </ul>
            </div>
        </div>
    </div>

    <script>
        const dropzone = document.getElementById('dropzone');
        const fileInput = document.getElementById('fileInput');
        const fileInfo = document.getElementById('fileInfo');
        const fileName = document.getElementById('fileName');
        const fileSize = document.getElementById('fileSize');

        // Drag and drop
        dropzone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropzone.classList.add('dragover');
        });

        dropzone.addEventListener('dragleave', () => {
            dropzone.classList.remove('dragover');
        });

        dropzone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropzone.classList.remove('dragover');
            fileInput.files = e.dataTransfer.files;
            updateFileInfo();
        });

        // File selection
        fileInput.addEventListener('change', updateFileInfo);

        function updateFileInfo() {
            if (fileInput.files.length > 0) {
                const file = fileInput.files[0];
                fileName.textContent = file.name;
                fileSize.textContent = (file.size / 1024).toFixed(2) + ' KB';
                fileInfo.classList.add('show');
            } else {
                fileInfo.classList.remove('show');
            }
        }

        // Click dropzone to trigger file input
        dropzone.addEventListener('click', () => {
            fileInput.click();
        });
    </script>
</body>
</html>