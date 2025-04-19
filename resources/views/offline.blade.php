<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>غير متصل بالإنترنت - سندباد</title>
    <link rel="icon" href="/favicon.ico">
    <!-- Load Tailwind directly from CDN for offline page -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @font-face {
            font-family: 'Tajawal';
            src: url('/build/assets/Tajawal-Regular.ttf') format('truetype');
            font-weight: normal;
            font-style: normal;
            font-display: swap;
        }
        body {
            font-family: 'Tajawal', sans-serif;
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="flex flex-col items-center justify-center min-h-screen p-4">
        <div class="w-full max-w-md p-6 bg-white shadow-lg rounded-lg text-center">
            <div class="mb-6">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-24 w-24 mx-auto text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
            </div>
            <h2 class="text-2xl font-bold text-gray-800 mb-2">غير متصل بالإنترنت</h2>
            <p class="text-gray-600 mb-6">
                يبدو أنك غير متصل بالإنترنت حاليًا. يرجى التحقق من اتصالك والمحاولة مرة أخرى.
            </p>
            <button onclick="window.location.reload()" class="w-full py-2 px-4 bg-orange-600 hover:bg-orange-700 text-white font-medium rounded-md transition duration-200">
                إعادة المحاولة
            </button>
        </div>
    </div>

    <script>
        // Check connection status when the page loads
        window.addEventListener('load', function() {
            if (navigator.onLine) {
                console.log('Connection restored, redirecting to homepage');
                setTimeout(() => {
                    window.location.href = '/';
                }, 1000);
            }
        });

        // Listen for online event
        window.addEventListener('online', function() {
            console.log('Connection restored, redirecting to homepage');
            window.location.href = '/';
        });
    </script>
</body>
</html>
