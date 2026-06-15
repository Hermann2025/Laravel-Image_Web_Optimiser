<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'PFAC Cameroun – Optimisur d\'Images')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/min/dropzone.min.css">
    <style>
        [x-cloak] { display: none !important; }

        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .gradient-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .dropzone-custom {
            border: 2px dashed #cbd5e1;
            border-radius: 1rem;
            background: #f8fafc;
            transition: all 0.3s ease;
            min-height: 200px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .dropzone-custom:hover,
        .dropzone-custom.dz-drag-hover {
            border-color: #667eea;
            background: #eef2ff;
        }

        .dropzone-custom .dz-message {
            margin: 2em 0;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -5px rgba(102, 126, 234, 0.4);
        }

        .btn-danger {
            background: linear-gradient(135deg, #f43f5e 0%, #e11d48 100%);
            transition: all 0.3s ease;
        }

        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -5px rgba(244, 63, 94, 0.4);
        }

        .gain-bar {
            height: 8px;
            border-radius: 4px;
            background: #e2e8f0;
            overflow: hidden;
        }

        .gain-bar-fill {
            height: 100%;
            border-radius: 4px;
            transition: width 1s ease;
        }

        .fade-in {
            animation: fadeIn 0.3s ease-in;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .pulse-loader {
            animation: pulse 1.5s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen flex flex-col">

    <!-- Navbar -->
    <nav class="gradient-bg text-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center space-x-3">
                    <img src="{{ asset('assets/images/logopfac.jpg') }}" alt="PFAC Cameroun"
                         class="h-10 w-10 rounded-full object-cover border-2 border-white"
                         onerror="this.style.display='none'">
                    <div>
                        <h1 class="text-xl font-bold">PFAC Cameroun</h1>
                        <p class="text-xs text-blue-100">Optimisur d'Images</p>
                    </div>
                </div>
                <div class="hidden sm:flex items-center space-x-4 text-sm">
                    <span class="text-blue-100">Compression • WebP • AVIF</span>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="flex-grow max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full">
        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6" role="alert">
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="gradient-bg text-white mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex flex-col sm:flex-row justify-between items-center space-y-4 sm:space-y-0">
                <div class="text-center sm:text-left">
                    <p class="text-lg font-semibold">PFAC Cameroun</p>
                    <p class="text-sm text-blue-100">DOMTCHUENG HERMANN PATRICK</p>
                </div>
                <div class="text-center sm:text-right text-sm text-blue-100 space-y-1">
                    <p>📞 675957731 / 695116475</p>
                    <p>✉️ domtchuenghp@gmail.com</p>
                </div>
            </div>
            <div class="mt-4 pt-4 border-t border-blue-400/30 text-center text-xs text-blue-200">
                &copy; {{ date('Y') }} PFAC Cameroun – Tous droits réservés
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>