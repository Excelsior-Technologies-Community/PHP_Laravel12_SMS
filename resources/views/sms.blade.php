<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modern SMS Dashboard</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        body {
            background: linear-gradient(to right, #0f172a, #1e293b);
        }

        .glass {
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
    </style>
</head>

<body class="min-h-screen text-white">

    <div class="max-w-7xl mx-auto px-5 py-10">

        <!-- Top Navbar -->
        <div class="glass rounded-3xl p-6 flex flex-col md:flex-row justify-between items-center mb-8 shadow-2xl">

            <div>
                <h1 class="text-4xl font-extrabold tracking-wide">
                    📩 SMS Control Center
                </h1>

                <p class="text-slate-300 mt-2">
                    Send and manage SMS with Twilio API
                </p>
            </div>

            <div class="mt-5 md:mt-0 flex gap-4">

                <div class="bg-indigo-500 px-6 py-4 rounded-2xl shadow-lg text-center">
                    <p class="text-sm text-indigo-100">
                        Total Messages
                    </p>

                    <h2 class="text-3xl font-bold">
                        {{ $messages->total() }}
                    </h2>
                </div>

                <div class="bg-pink-500 px-6 py-4 rounded-2xl shadow-lg text-center">
                    <p class="text-sm text-pink-100">
                        SMS Status
                    </p>

                    <h2 class="text-xl font-bold">
                        Active
                    </h2>
                </div>

            </div>

        </div>

        <!-- Alerts -->
        @if(session('success'))
        <div class="bg-green-500/20 border border-green-400 text-green-200 px-5 py-4 rounded-2xl mb-6">
            {{ session('success') }}
        </div>
        @endif

        @if(session('error'))
        <div class="bg-red-500/20 border border-red-400 text-red-200 px-5 py-4 rounded-2xl mb-6">
            {{ session('error') }}
        </div>
        @endif

        <!-- Main Grid -->
        <div class="grid lg:grid-cols-3 gap-8">

            <!-- Send SMS Card -->
            <div class="lg:col-span-1">

                <div class="glass rounded-3xl p-8 shadow-2xl sticky top-5">

                    <h2 class="text-3xl font-bold mb-6">
                        🚀 Send SMS
                    </h2>

                    <form method="POST" action="/sms/send" class="space-y-5">
                        @csrf

                        <!-- Number -->
                        <div>
                            <label class="block text-sm mb-2 text-slate-300">
                                Phone Number
                            </label>

                            <input
                                type="text"
                                name="number"
                                placeholder="+919876543210"
                                class="w-full bg-slate-800 border border-slate-600 rounded-2xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 outline-none">

                            @error('number')
                            <p class="text-red-400 text-sm mt-2">
                                {{ $message }}
                            </p>
                            @enderror
                        </div>

                        <!-- Message -->
                        <div>
                            <label class="block text-sm mb-2 text-slate-300">
                                Your Message
                            </label>

                            <textarea
                                name="message"
                                rows="6"
                                maxlength="160"
                                placeholder="Type your SMS..."
                                class="w-full bg-slate-800 border border-slate-600 rounded-2xl px-4 py-3 focus:ring-2 focus:ring-pink-500 outline-none"></textarea>

                            @error('message')
                            <p class="text-red-400 text-sm mt-2">
                                {{ $message }}
                            </p>
                            @enderror
                        </div>

                        <!-- Button -->
                        <button
                            type="submit"
                            class="w-full bg-gradient-to-r from-indigo-500 to-pink-500 hover:scale-105 transition-all duration-300 py-3 rounded-2xl font-bold shadow-xl">
                            📨 Send Now
                        </button>

                    </form>

                </div>

            </div>

            <!-- History Section -->
            <div class="lg:col-span-2">

                <div class="glass rounded-3xl p-8 shadow-2xl">

                    <!-- Top -->
                    <div class="flex flex-col md:flex-row justify-between md:items-center gap-4 mb-8">

                        <div>
                            <h2 class="text-3xl font-bold">
                                📜 SMS History
                            </h2>

                            <p class="text-slate-400 mt-1">
                                Recent sent messages
                            </p>
                        </div>

                        <!-- Search -->
                        <form method="GET" action="/sms">
                            <input
                                type="text"
                                name="search"
                                placeholder="Search number or message..."
                                class="bg-slate-800 border border-slate-600 rounded-2xl px-5 py-3 outline-none focus:ring-2 focus:ring-indigo-500">
                        </form>

                    </div>

                    <!-- Messages -->
                    <div class="space-y-5">

                        @forelse($messages as $msg)

                        <div class="bg-slate-800/70 border border-slate-700 rounded-3xl p-5 hover:scale-[1.01] transition duration-300">

                            <div class="flex flex-col md:flex-row justify-between gap-5">

                                <!-- Left -->
                                <div class="flex-1">

                                    <div class="flex items-center gap-3 mb-3">

                                        <div class="bg-indigo-500 h-12 w-12 rounded-full flex items-center justify-center text-xl">
                                            📱
                                        </div>

                                        <div>
                                            <h3 class="font-bold text-lg">
                                                {{ $msg->number }}
                                            </h3>

                                            <p class="text-slate-400 text-sm">
                                                {{ $msg->created_at->diffForHumans() }}
                                            </p>
                                        </div>

                                    </div>

                                    <div class="bg-slate-900 rounded-2xl p-4 text-slate-200 leading-relaxed">
                                        {{ $msg->message }}
                                    </div>

                                </div>

                                <!-- Right -->
                                <div class="flex flex-col justify-between items-end">

                                    <!-- Status -->
                                    <span class="px-4 py-2 rounded-full text-sm font-semibold
                                        {{ $msg->status == 'Sent'
                                            ? 'bg-green-500/20 text-green-300 border border-green-400'
                                            : 'bg-red-500/20 text-red-300 border border-red-400' }}">

                                        {{ $msg->status }}

                                    </span>

                                    <!-- Delete -->
                                    <form
                                        method="POST"
                                        action="/sms/{{ $msg->id }}"
                                        class="mt-6">

                                        @csrf
                                        @method('DELETE')

                                        <button
                                            onclick="return confirm('Delete this SMS?')"
                                            class="bg-red-500 hover:bg-red-600 px-5 py-2 rounded-xl text-sm font-semibold transition">
                                            🗑 Delete
                                        </button>

                                    </form>

                                </div>

                            </div>

                        </div>

                        @empty

                        <div class="text-center py-20">

                            <div class="text-7xl mb-4">
                                📭
                            </div>

                            <h2 class="text-2xl font-bold text-slate-300">
                                No SMS History Found
                            </h2>

                            <p class="text-slate-500 mt-2">
                                Start sending SMS to see history here.
                            </p>

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

</body>

</html>