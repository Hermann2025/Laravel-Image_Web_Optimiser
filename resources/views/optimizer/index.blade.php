@extends('layouts.app')

@section('title', 'PFAC Cameroun – Optimisur d\'Images')

@section('content')
<div x-data="imageOptimizer()" x-cloak @csrf>
    <!-- Header Section -->
    <div class="text-center mb-8">
        <h2 class="text-3xl font-bold text-gray-800 mb-2">
            Optimisez vos images en un clic
        </h2>
        <p class="text-gray-500">
            Compression intelligente • Conversion WebP/AVIF • Redimensionnement
        </p>
    </div>

    <!-- Dropzone -->
    <div class="bg-white rounded-2xl shadow-lg p-6 mb-8">
        <form action="{{ url('/api/upload') }}" class="dropzone dropzone-custom" id="imageDropzone" enctype="multipart/form-data">
            @csrf
            <div class="dz-message text-center">
                <div class="text-5xl mb-4">📁</div>
                <h3 class="text-xl font-semibold text-gray-700 mb-2">
                    Déposez vos images ici
                </h3>
                <p class="text-gray-500 mb-4">
                    ou cliquez pour parcourir
                </p>
                <p class="text-sm text-gray-400">
                    JPG, PNG, GIF, WebP, BMP • Max 20 fichiers • ZIP accepté • 50 Mo max/fichier
                </p>
            </div>
        </form>
    </div>

    <!-- Images Grid (after upload) -->
    <div x-show="images.length > 0" x-transition class="mb-8">
        <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
            <h3 class="text-xl font-semibold text-gray-800">
                📸 Images uploadées (<span x-text="images.length"></span>)
                <span class="text-sm font-normal text-gray-500 ml-2">
                    · <span x-text="selectedCount()"></span> sélectionnée(s)
                </span>
            </h3>
            <div class="flex gap-2">
                <button @click="toggleAll()"
                        class="bg-gray-100 text-gray-600 px-4 py-2 rounded-lg text-sm font-medium hover:bg-gray-200 transition">
                    <span x-text="allSelected() ? '🔓 Tout désélectionner' : '🔒 Tout sélectionner'"></span>
                </button>
                <button @click="clearAll()"
                        class="btn-danger text-white px-4 py-2 rounded-lg text-sm font-medium">
                    🗑️ Tout supprimer
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            <template x-for="(image, index) in images" :key="image.id">
                <div class="bg-white rounded-xl shadow p-3 fade-in relative group"
                     :class="image.selected ? 'ring-2 ring-indigo-500' : ''">
                    <div class="relative">
                        <img :src="image.preview_url"
                             :alt="image.original_name"
                             class="w-full h-40 object-cover rounded-lg"
                             loading="lazy">

                        <!-- Bouton supprimer (overlay) -->
                        <button @click="removeImage(image.id, index)"
                                class="absolute top-2 right-2 bg-red-500 text-white rounded-full w-7 h-7 flex items-center justify-center text-sm hover:bg-red-600 transition">
                            ✕
                        </button>

                        <!-- Toggle sélection (overlay bas gauche) -->
                        <button @click="image.selected = !image.selected"
                                class="absolute bottom-2 left-2 rounded-full w-8 h-8 flex items-center justify-center text-lg transition shadow-lg"
                                :class="image.selected ? 'bg-indigo-600 text-white' : 'bg-white/80 text-gray-400 hover:bg-white'">
                            <span x-text="image.selected ? '✓' : '○'"></span>
                        </button>
                    </div>
                    <div class="mt-2 flex items-center justify-between">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-700 truncate" x-text="image.original_name"></p>
                            <p class="text-xs text-gray-500" x-text="formatSize(image.size)"></p>
                        </div>
                        <!-- Toggle switch -->
                        <label class="relative inline-flex items-center cursor-pointer ml-2">
                            <input type="checkbox" x-model="image.selected" class="sr-only peer">
                            <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-indigo-600"></div>
                        </label>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <!-- Options & Optimize Button -->
    <div x-show="images.length > 0" x-transition
         class="bg-white rounded-2xl shadow-lg p-6 mb-8">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">⚙️ Paramètres d'optimisation</h3>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
            <!-- Quality -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Qualité : <span class="font-bold text-indigo-600" x-text="options.quality + '%'"></span>
                </label>
                <input type="range" min="10" max="100" x-model="options.quality"
                       class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-indigo-600">
                <div class="flex justify-between text-xs text-gray-400 mt-1">
                    <span>10%</span>
                    <span>100%</span>
                </div>
            </div>

            <!-- Format -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Format de sortie</label>
                <select x-model="options.format"
                        class="w-full rounded-lg border-gray-300 border p-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="webp">WebP (recommandé)</option>
                    <option value="avif">AVIF</option>
                    <option value="jpeg">JPEG</option>
                    <option value="png">PNG</option>
                    <option value="original">Format original</option>
                </select>
            </div>

            <!-- Max Width -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Largeur max (px)</label>
                <select x-model="options.max_width"
                        class="w-full rounded-lg border-gray-300 border p-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="3840">4K (3840px)</option>
                    <option value="2560">2K (2560px)</option>
                    <option value="1920" selected>Full HD (1920px)</option>
                    <option value="1280">HD (1280px)</option>
                    <option value="800">800px</option>
                    <option value="0">Aucun</option>
                </select>
            </div>
        </div>

        <div class="mt-6 text-center">
            <button @click="optimize()" :disabled="optimizing || selectedCount() === 0"
                    class="btn-primary text-white px-8 py-3 rounded-xl text-lg font-semibold disabled:opacity-50 disabled:cursor-not-allowed inline-flex items-center space-x-2">
                <template x-if="!optimizing">
                    <span>🚀 Lancer l'optimisation (<span x-text="selectedCount()"></span>)</span>
                </template>
                <template x-if="optimizing">
                    <span class="flex items-center space-x-2">
                        <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>Optimisation en cours...</span>
                    </span>
                </template>
            </button>
        </div>
    </div>

    <!-- Results Section -->
    <div x-show="results.length > 0" x-transition
         class="bg-white rounded-2xl shadow-lg p-6 mb-8">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-xl font-semibold text-gray-800">📊 Résultats</h3>
            <button @click="downloadAll()"
                    class="btn-primary text-white px-5 py-2 rounded-lg text-sm font-medium">
                📦 Tout télécharger (ZIP)
            </button>
        </div>

        <div class="space-y-4">
            <template x-for="result in results" :key="result.id">
                <div class="border rounded-xl p-4 hover:shadow-md transition">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center space-x-3">
                            <span class="text-2xl" x-text="getStatusIcon(result.status)"></span>
                            <div>
                                <p class="font-medium text-gray-800" x-text="result.original_name"></p>
                                <p class="text-xs text-gray-500">
                                    <span x-text="formatSize(result.original_size)"></span>
                                    →
                                    <span x-text="formatSize(result.optimized_size)"></span>
                                    <span class="ml-2 font-semibold" x-text="result.format ? '(' + result.format.toUpperCase() + ')' : ''"></span>
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center space-x-3">
                            <span class="text-lg font-bold"
                                  :class="result.gain >= 50 ? 'text-green-600' : result.gain >= 30 ? 'text-yellow-600' : 'text-red-600'"
                                  x-text="result.gain + '%'"></span>
                            <a :href="'/api/download/' + result.id"
                               class="btn-primary text-white px-3 py-1.5 rounded-lg text-xs font-medium hover:no-underline">
                                📥 Télécharger
                            </a>
                        </div>
                    </div>
                    <div class="gain-bar">
                        <div class="gain-bar-fill"
                             :style="'width: ' + Math.max(result.gain, 0) + '%'"
                             :class="result.gain >= 50 ? 'bg-green-500' : result.gain >= 30 ? 'bg-yellow-500' : 'bg-red-500'">
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/min/dropzone.min.js"></script>
<script>
Dropzone.autoDiscover = false;

function imageOptimizer() {
    return {
        images: [],
        results: [],
        optimizing: false,
        sessionId: null,
        options: {
            quality: 80,
            format: 'webp',
            max_width: 1920,
        },

        init() {
            const self = this;
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            const dropzone = new Dropzone('#imageDropzone', {
                url: '/api/upload',
                paramName: 'images',
                maxFilesize: 50,
                maxFiles: 20,
                acceptedFiles: '.jpeg,.jpg,.png,.gif,.webp,.bmp,.zip',
                addRemoveLinks: false,
                dictDefaultMessage: '',
                uploadMultiple: true,
                parallelUploads: 20,
                headers: {
                    'X-CSRF-TOKEN': token,
                },
                init: function() {
                    this.on('successmultiple', function(files, response) {
                        if (response.success) {
                            self.sessionId = response.session_id;
                            response.images.forEach(img => {
                                if (!self.images.find(i => i.id === img.id)) {
                                    img.selected = true;
                                    self.images.push(img);
                                }
                            });
                        }
                    });
                    this.on('success', function(file, response) {
                        if (response.success) {
                            self.sessionId = response.session_id;
                            response.images.forEach(img => {
                                if (!self.images.find(i => i.id === img.id)) {
                                    img.selected = true;
                                    self.images.push(img);
                                }
                            });
                        }
                    });
                    this.on('error', function(file, message) {
                        console.error('Upload error:', message);
                        let msg = typeof message === 'string' ? message :
                                (message.message || 'Fichier non valide');
                        alert('Erreur: ' + msg);
                    });
                    this.on('maxfilesexceeded', function(file) {
                        this.removeFile(file);
                        alert('Maximum 20 fichiers autorisés.');
                    });
                }
            });
        },

        selectedCount() {
            return this.images.filter(i => i.selected).length;
        },

        allSelected() {
            return this.images.length > 0 && this.images.every(i => i.selected);
        },

        toggleAll() {
            const newVal = !this.allSelected();
            this.images.forEach(i => i.selected = newVal);
        },

        removeImage(id, index) {
            if (!confirm('Supprimer cette image ?')) return;

            fetch('/api/images/' + id, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    this.images.splice(index, 1);
                    if (this.images.length === 0) {
                        this.sessionId = null;
                    }
                }
            })
            .catch(err => {
                console.error(err);
                // Supprimer quand même en local si API erreur
                this.images.splice(index, 1);
            });
        },

        clearAll() {
            if (!this.images.length) return;
            if (!confirm('Supprimer toutes les images ?')) return;

            if (this.sessionId) {
                fetch('/api/session/' + this.sessionId, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                    }
                }).catch(() => {});
            }
            this.images = [];
            this.results = [];
            this.sessionId = null;
        },

        optimize() {
            const selected = this.images.filter(i => i.selected);
            if (!this.sessionId || selected.length === 0 || this.optimizing) return;

            this.optimizing = true;
            this.results = [];

            fetch('/api/optimize', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    session_id: this.sessionId,
                    ...this.options,
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    this.results = data.results;
                    // Enlever les images sélectionnées (elles sont traitées)
                    selected.forEach(img => {
                        const idx = this.images.indexOf(img);
                        if (idx !== -1) this.images.splice(idx, 1);
                    });
                } else {
                    alert(data.message || 'Erreur lors de l\'optimisation');
                }
            })
            .catch(err => {
                console.error(err);
                alert('Erreur réseau lors de l\'optimisation');
            })
            .finally(() => {
                this.optimizing = false;
            });
        },

        downloadAll() {
            if (!this.sessionId) return;
            window.location.href = '/api/download-all/' + this.sessionId;
        },

        formatSize(bytes) {
            if (!bytes) return '0 B';
            const units = ['B', 'KB', 'MB', 'GB'];
            let i = 0;
            let size = bytes;
            while (size >= 1024 && i < units.length - 1) {
                size /= 1024;
                i++;
            }
            return size.toFixed(i > 0 ? 1 : 0) + ' ' + units[i];
        },

        getStatusIcon(status) {
            return status === 'completed' ? '✅' : status === 'failed' ? '❌' : '⏳';
        }
    };
}
</script>
@endpush