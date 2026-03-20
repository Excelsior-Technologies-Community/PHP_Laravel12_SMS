<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Send SMS</title>

    <!-- Tailwind CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-500 min-h-screen flex items-center justify-center">

    <div class="bg-white shadow-2xl rounded-2xl w-full max-w-md p-8">

        <!-- Heading -->
        <h2 class="text-2xl font-bold text-center text-gray-800 mb-6">
            📩 Send SMS
        </h2>

        <!-- Success Message -->
        @if(session('success'))
        <div class="bg-green-100 text-green-700 p-3 rounded mb-4 text-sm">
            {{ session('success') }}
        </div>
        @endif

        @if(session('error'))
        <div class="bg-red-100 text-red-700 p-3 rounded mb-4 text-sm">
            {{ session('error') }}
        </div>
        @endif

        <!-- Form -->
        <form method="POST" action="/sms/send" class="space-y-5">
            @csrf

            <!-- Phone Number -->
            <div>
                <label class="block text-gray-600 text-sm mb-1">Phone Number</label>
                <input
                    type="text"
                    name="number"
                    placeholder="+919876543210"
                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-400 focus:outline-none"
                    required>
            </div>

            <!-- Message -->
            <div>
                <label class="block text-gray-600 text-sm mb-1">Message</label>
                <textarea
                    name="message"
                    rows="4"
                    placeholder="Type your message..."
                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-400 focus:outline-none"
                    required></textarea>
            </div>

            <!-- Button -->
            <button
                type="submit"
                class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-2 rounded-lg font-semibold transition duration-300">
                🚀 Send SMS
            </button>
        </form>

    </div>

</body>

</html>