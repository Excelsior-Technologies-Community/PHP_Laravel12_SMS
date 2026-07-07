<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modern SMS Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        body {
            background: linear-gradient(to right, #0f172a, #1e293b);
        }
        .glass {
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .stat-card { transition: all 0.3s; }
        .stat-card:hover { transform: translateY(-4px); }
        .template-select { cursor: pointer; transition: all 0.3s; }
        .template-select:hover { border-color: #8b5cf6; }
        .template-select.active { border-color: #8b5cf6; background: rgba(139, 92, 246, 0.1); }
        .status-badge { font-size: 11px; padding: 4px 12px; border-radius: 20px; font-weight: 500; }
        .pagination { display: flex; justify-content: center; gap: 8px; margin-top: 20px; }
        .pagination a, .pagination span { padding: 8px 14px; background: rgba(255,255,255,0.08); border-radius: 12px; text-decoration: none; color: #94a3b8; border: 1px solid rgba(255,255,255,0.1); }
        .pagination .active { background: #8b5cf6; color: white; }
        .pagination a:hover { background: rgba(139,92,246,0.2); }
    </style>
</head>
<body class="min-h-screen text-white">
    <div class="max-w-7xl mx-auto px-5 py-10">

        <!-- Top Navbar -->
        <div class="glass rounded-3xl p-6 flex flex-col md:flex-row justify-between items-center mb-8 shadow-2xl">
            <div>
                <h1 class="text-4xl font-extrabold tracking-wide">📩 SMS Control Center</h1>
                <p class="text-slate-300 mt-2">Send and manage SMS with Multi-Gateway Support</p>
            </div>
            <div class="mt-5 md:mt-0 flex gap-4 flex-wrap">
                <div class="bg-indigo-500 px-6 py-4 rounded-2xl shadow-lg text-center stat-card">
                    <p class="text-sm text-indigo-100">Total</p>
                    <h2 class="text-3xl font-bold">{{ $stats['total'] ?? 0 }}</h2>
                </div>
                <div class="bg-green-500 px-6 py-4 rounded-2xl shadow-lg text-center stat-card">
                    <p class="text-sm text-green-100">Delivered</p>
                    <h2 class="text-3xl font-bold">{{ $stats['delivered'] ?? 0 }}</h2>
                </div>
                <div class="bg-yellow-500 px-6 py-4 rounded-2xl shadow-lg text-center stat-card">
                    <p class="text-sm text-yellow-100">Pending</p>
                    <h2 class="text-3xl font-bold">{{ $stats['pending'] ?? 0 }}</h2>
                </div>
                <div class="bg-red-500 px-6 py-4 rounded-2xl shadow-lg text-center stat-card">
                    <p class="text-sm text-red-100">Failed</p>
                    <h2 class="text-3xl font-bold">{{ $stats['failed'] ?? 0 }}</h2>
                </div>
            </div>
        </div>

        <!-- Alerts -->
        @if(session('success'))
            <div class="bg-green-500/20 border border-green-400 text-green-200 px-5 py-4 rounded-2xl mb-6">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="bg-red-500/20 border border-red-400 text-red-200 px-5 py-4 rounded-2xl mb-6">{{ session('error') }}</div>
        @endif

        <!-- Main Grid -->
        <div class="grid lg:grid-cols-3 gap-8">

            <!-- Send SMS Card -->
            <div class="lg:col-span-1">
                <div class="glass rounded-3xl p-8 shadow-2xl sticky top-5">
                    <h2 class="text-3xl font-bold mb-6">🚀 Send SMS</h2>

                    <form method="POST" action="{{ url('/sms/send') }}" class="space-y-5">
                        @csrf

                        <!-- Template Selection -->
                        <div>
                            <label class="block text-sm mb-2 text-slate-300">Choose Template (Optional)</label>
                            <select name="template_id" id="templateSelect" class="w-full bg-slate-800 border border-slate-600 rounded-2xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 outline-none" onchange="loadTemplate(this.value)">
                                <option value="">-- Custom Message --</option>
                                @foreach($templates ?? [] as $template)
                                    <option value="{{ $template->id }}">{{ $template->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Placeholders -->
                        <div id="placeholderContainer" class="hidden space-y-3">
                            <label class="block text-sm mb-2 text-slate-300">Template Variables</label>
                            <div id="placeholderInputs"></div>
                        </div>

                        <!-- Number -->
                        <div>
                            <label class="block text-sm mb-2 text-slate-300">Phone Number</label>
                            <input type="text" name="number" placeholder="+919876543210" value="{{ old('number') }}"
                                class="w-full bg-slate-800 border border-slate-600 rounded-2xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 outline-none">
                            @error('number')<p class="text-red-400 text-sm mt-2">{{ $message }}</p>@enderror
                        </div>

                        <!-- Message -->
                        <div>
                            <label class="block text-sm mb-2 text-slate-300">Your Message</label>
                            <textarea name="message" id="messageInput" rows="6" maxlength="160" placeholder="Type your SMS..."
                                class="w-full bg-slate-800 border border-slate-600 rounded-2xl px-4 py-3 focus:ring-2 focus:ring-pink-500 outline-none">{{ old('message') }}</textarea>
                            <div class="text-right text-sm text-slate-400 mt-1"><span id="charCount">0</span>/160</div>
                            @error('message')<p class="text-red-400 text-sm mt-2">{{ $message }}</p>@enderror
                        </div>

                        <button type="submit" class="w-full bg-gradient-to-r from-indigo-500 to-pink-500 hover:scale-105 transition-all duration-300 py-3 rounded-2xl font-bold shadow-xl">
                            📨 Send Now
                        </button>
                    </form>

                    <!-- Quick Actions -->
                    <div class="mt-6 grid grid-cols-2 gap-3">
                        <a href="{{ url('/sms/templates') }}" class="text-center bg-slate-800 hover:bg-slate-700 px-4 py-3 rounded-2xl transition text-sm">📝 Templates</a>
                        <a href="{{ url('/sms/bulk') }}" class="text-center bg-slate-800 hover:bg-slate-700 px-4 py-3 rounded-2xl transition text-sm">📤 Bulk Upload</a>
                    </div>
                </div>
            </div>

            <!-- History Section -->
            <div class="lg:col-span-2">
                <div class="glass rounded-3xl p-8 shadow-2xl">
                    <div class="flex flex-col md:flex-row justify-between md:items-center gap-4 mb-8">
                        <div>
                            <h2 class="text-3xl font-bold">📜 SMS History</h2>
                            <p class="text-slate-400 mt-1">Recent sent messages</p>
                        </div>
                        <div class="flex gap-3">
                            <form method="GET" action="{{ url('/sms') }}" class="flex gap-2">
                                <input type="text" name="search" placeholder="Search..." value="{{ $search ?? '' }}"
                                    class="bg-slate-800 border border-slate-600 rounded-2xl px-5 py-3 outline-none focus:ring-2 focus:ring-indigo-500">
                                <button type="submit" class="bg-indigo-500 hover:bg-indigo-600 px-4 py-3 rounded-2xl">🔍</button>
                            </form>
                            <a href="{{ url('/sms/export') }}" class="bg-green-500 hover:bg-green-600 px-4 py-3 rounded-2xl transition">📥</a>
                        </div>
                    </div>

                    <!-- Status Filter -->
                    <div class="flex gap-2 mb-6 flex-wrap">
                        <a href="{{ url('/sms') }}" class="px-4 py-2 rounded-xl {{ empty($status) ? 'bg-indigo-500' : 'bg-slate-800' }} hover:bg-indigo-600 transition text-sm">All</a>
                        <a href="{{ url('/sms?status=pending') }}" class="px-4 py-2 rounded-xl {{ ($status ?? '') == 'pending' ? 'bg-yellow-500' : 'bg-slate-800' }} hover:bg-yellow-600 transition text-sm">⏳ Pending</a>
                        <a href="{{ url('/sms?status=sent') }}" class="px-4 py-2 rounded-xl {{ ($status ?? '') == 'sent' ? 'bg-blue-500' : 'bg-slate-800' }} hover:bg-blue-600 transition text-sm">📤 Sent</a>
                        <a href="{{ url('/sms?status=delivered') }}" class="px-4 py-2 rounded-xl {{ ($status ?? '') == 'delivered' ? 'bg-green-500' : 'bg-slate-800' }} hover:bg-green-600 transition text-sm">✅ Delivered</a>
                        <a href="{{ url('/sms?status=failed') }}" class="px-4 py-2 rounded-xl {{ ($status ?? '') == 'failed' ? 'bg-red-500' : 'bg-slate-800' }} hover:bg-red-600 transition text-sm">❌ Failed</a>
                    </div>

                    <!-- Messages -->
                    <div class="space-y-5">
                        @forelse($messages as $msg)
                            <div class="bg-slate-800/70 border border-slate-700 rounded-3xl p-5 hover:scale-[1.01] transition duration-300">
                                <div class="flex flex-col md:flex-row justify-between gap-5">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-3 mb-3">
                                            <div class="bg-indigo-500 h-12 w-12 rounded-full flex items-center justify-center text-xl">📱</div>
                                            <div>
                                                <h3 class="font-bold text-lg">{{ $msg->number }}</h3>
                                                <p class="text-slate-400 text-sm">{{ $msg->created_at->diffForHumans() }}</p>
                                            </div>
                                        </div>
                                        <div class="bg-slate-900 rounded-2xl p-4 text-slate-200 leading-relaxed">{{ $msg->message }}</div>
                                        @if($msg->gateway)
                                            <p class="text-xs text-slate-500 mt-2">Gateway: {{ $msg->gateway }}</p>
                                        @endif
                                        @if($msg->error_message)
                                            <p class="text-xs text-red-400 mt-1">Error: {{ $msg->error_message }}</p>
                                        @endif
                                    </div>
                                    <div class="flex flex-col justify-between items-end">
                                        <span class="status-badge {{ $msg->status_badge }}">
                                            {{ $msg->status_icon }} {{ ucfirst($msg->status) }}
                                        </span>
                                        <div class="flex gap-2 mt-4">
                                            @if($msg->status == 'failed' && $msg->retry_count < 3)
                                                <a href="{{ url('/sms/retry/' . $msg->id) }}" class="bg-yellow-500 hover:bg-yellow-600 px-4 py-2 rounded-xl text-sm font-semibold transition" onclick="return confirm('Retry this SMS?')">🔄 Retry</a>
                                            @endif
                                            <form method="POST" action="{{ url('/sms/' . $msg->id) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button onclick="return confirm('Delete this SMS?')" class="bg-red-500 hover:bg-red-600 px-4 py-2 rounded-xl text-sm font-semibold transition">🗑 Delete</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-20">
                                <div class="text-7xl mb-4">📭</div>
                                <h2 class="text-2xl font-bold text-slate-300">No SMS History Found</h2>
                                <p class="text-slate-500 mt-2">Start sending SMS to see history here.</p>
                            </div>
                        @endforelse
                    </div>

                    <!-- Pagination -->
                    <div class="mt-10">
                        {{ $messages->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Character counter
        const messageInput = document.getElementById('messageInput');
        const charCount = document.getElementById('charCount');
        
        if (messageInput && charCount) {
            messageInput.addEventListener('input', function() {
                charCount.textContent = this.value.length;
            });
            
            // Initial count
            charCount.textContent = messageInput.value.length;
        }

        // Template data - using JSON from PHP
        const templates = @json($templates ?? []);

        function loadTemplate(templateId) {
            const container = document.getElementById('placeholderContainer');
            const inputs = document.getElementById('placeholderInputs');
            const messageField = document.getElementById('messageInput');
            const charCounter = document.getElementById('charCount');

            if (!templateId) {
                container.classList.add('hidden');
                return;
            }

            const template = templates.find(function(t) { 
                return t.id == templateId; 
            });
            
            if (!template) return;

            const placeholders = template.placeholders || {};
            const keys = Object.keys(placeholders);

            if (keys.length === 0) {
                container.classList.add('hidden');
                messageField.value = template.content;
                if (charCounter) charCounter.textContent = template.content.length;
                return;
            }

            container.classList.remove('hidden');
            inputs.innerHTML = '';

            keys.forEach(function(key) {
                const div = document.createElement('div');
                div.innerHTML = `
                    <label class="block text-xs text-slate-400 mb-1">${placeholders[key]}</label>
                    <input type="text" placeholder="Enter ${placeholders[key].toLowerCase()}" 
                           class="w-full bg-slate-800 border border-slate-600 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none"
                           oninput="updatePreview()" data-key="${key}">
                `;
                inputs.appendChild(div.firstElementChild);
                // Fix: Append both label and input properly
                const wrapper = document.createElement('div');
                wrapper.innerHTML = `
                    <label class="block text-xs text-slate-400 mb-1">${placeholders[key]}</label>
                    <input type="text" placeholder="Enter ${placeholders[key].toLowerCase()}" 
                           class="w-full bg-slate-800 border border-slate-600 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none"
                           oninput="updatePreview()" data-key="${key}">
                `;
                inputs.appendChild(wrapper.firstElementChild);
                // Actually let's use simpler approach
                inputs.innerHTML = '';
                keys.forEach(function(k) {
                    inputs.innerHTML += `
                        <div>
                            <label class="block text-xs text-slate-400 mb-1">${placeholders[k]}</label>
                            <input type="text" placeholder="Enter ${placeholders[k].toLowerCase()}" 
                                   class="w-full bg-slate-800 border border-slate-600 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none"
                                   oninput="updatePreview()" data-key="${k}">
                        </div>
                    `;
                });
            });

            // Set initial message with placeholders
            messageField.value = template.content;
            if (charCounter) charCounter.textContent = template.content.length;
        }

        function updatePreview() {
            const templateId = document.getElementById('templateSelect').value;
            if (!templateId) return;

            const template = templates.find(function(t) { 
                return t.id == templateId; 
            });
            
            if (!template) return;

            let content = template.content;
            const inputs = document.querySelectorAll('#placeholderInputs input');

            inputs.forEach(function(input) {
                const key = input.dataset.key;
                const value = input.value || '{' + key + '}';
                content = content.replace(new RegExp('{{' + key + '}}', 'g'), value);
            });

            const messageField = document.getElementById('messageInput');
            const charCounter = document.getElementById('charCount');
            
            if (messageField) {
                messageField.value = content;
                if (charCounter) charCounter.textContent = content.length;
            }
        }
    </script>
</body>
</html>