<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SMS Templates</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { background: linear-gradient(to right, #0f172a, #1e293b); }
        .glass { background: rgba(255,255,255,0.08); backdrop-filter: blur(12px); border: 1px solid rgba(255,255,255,0.1); }
        .template-card { transition: all 0.3s; }
        .template-card:hover { transform: translateY(-4px); box-shadow: 0 8px 25px rgba(0,0,0,0.3); }
        .badge { font-size: 10px; padding: 2px 10px; border-radius: 20px; font-weight: 500; }
    </style>
</head>
<body class="min-h-screen text-white">
    <div class="max-w-6xl mx-auto px-5 py-10">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold">📝 SMS Templates</h1>
            <div class="flex gap-3">
                <a href="{{ url('/sms') }}" class="bg-indigo-500 hover:bg-indigo-600 px-4 py-2 rounded-xl transition">← Back</a>
                <button onclick="document.getElementById('createModal').classList.remove('hidden')" class="bg-green-500 hover:bg-green-600 px-4 py-2 rounded-xl transition">+ New Template</button>
            </div>
        </div>

        @if(session('success'))
            <div class="bg-green-500/20 border border-green-400 text-green-200 px-5 py-4 rounded-2xl mb-6">{{ session('success') }}</div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @forelse($templates as $template)
                <div class="glass rounded-3xl p-6 template-card">
                    <div class="flex justify-between items-start">
                        <div>
                            <h3 class="text-xl font-bold">{{ $template->name }}</h3>
                            <div class="flex gap-2 mt-2">
                                <span class="badge bg-purple-500/20 text-purple-300 border border-purple-400">{{ ucfirst($template->category) }}</span>
                                <span class="badge {{ $template->is_active ? 'bg-green-500/20 text-green-300 border border-green-400' : 'bg-gray-500/20 text-gray-300 border border-gray-400' }}">
                                    {{ $template->is_active ? 'Active' : 'Inactive' }}
                                </span>
                                <span class="badge bg-orange-500/20 text-orange-300 border border-orange-400">{{ count($template->placeholders ?? []) }} vars</span>
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <a href="{{ url('/sms/template/toggle/' . $template->id) }}" class="text-sm hover:opacity-80" onclick="return confirm('Toggle status?')">
                                {{ $template->is_active ? '🔇' : '🔊' }}
                            </a>
                            <form method="POST" action="{{ url('/sms/template/' . $template->id) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-sm text-red-400 hover:text-red-300" onclick="return confirm('Delete this template?')">🗑️</button>
                            </form>
                        </div>
                    </div>
                    <div class="mt-3 bg-slate-900 rounded-xl p-3 text-sm text-slate-300">{{ $template->content }}</div>
                    @if($template->placeholders)
                        <div class="mt-2 text-xs text-slate-400">Variables: {{ implode(', ', array_keys($template->placeholders)) }}</div>
                    @endif
                </div>
            @empty
                <div class="col-span-2 text-center py-20 glass rounded-3xl">
                    <div class="text-6xl mb-4">📭</div>
                    <h2 class="text-2xl font-bold">No Templates Found</h2>
                    <button onclick="document.getElementById('createModal').classList.remove('hidden')" class="mt-4 bg-indigo-500 hover:bg-indigo-600 px-6 py-3 rounded-xl transition">Create First Template</button>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Create Modal -->
    <div id="createModal" class="hidden fixed inset-0 bg-black/70 flex items-center justify-center p-4 z-50">
        <div class="bg-slate-800 rounded-3xl max-w-2xl w-full p-8 max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold">Create Template</h2>
                <button onclick="document.getElementById('createModal').classList.add('hidden')" class="text-2xl hover:text-gray-400">&times;</button>
            </div>
            <form method="POST" action="{{ url('/sms/templates') }}">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm mb-1">Template Name</label>
                        <input type="text" name="name" required class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm mb-1">Category</label>
                        <select name="category" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 outline-none">
                            <option value="general">General</option>
                            <option value="otp">OTP</option>
                            <option value="notification">Notification</option>
                            <option value="promotional">Promotional</option>
                            <option value="alert">Alert</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm mb-1">Content <span class="text-slate-400 text-xs">(Use {variable} for placeholders)</span></label>
                        <textarea name="content" rows="4" required class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 outline-none font-mono"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm mb-1">Placeholders <span class="text-slate-400 text-xs">(JSON format)</span></label>
                        <textarea name="placeholders" rows="3" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 outline-none font-mono" placeholder='{"name":"Full Name","otp":"OTP Code"}'></textarea>
                    </div>
                    <div class="flex items-center gap-3">
                        <input type="checkbox" name="is_active" id="is_active" checked class="w-5 h-5">
                        <label for="is_active">Active</label>
                    </div>
                    <button type="submit" class="w-full bg-gradient-to-r from-indigo-500 to-pink-500 hover:scale-105 transition py-3 rounded-xl font-bold">Create Template</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('createModal').addEventListener('click', function(e) {
            if (e.target === this) this.classList.add('hidden');
        });
    </script>
</body>
</html>