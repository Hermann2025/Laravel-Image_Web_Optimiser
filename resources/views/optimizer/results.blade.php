@extends('layouts.app')

@section('title', 'Résultats – PFAC Cameroun')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="text-center mb-8">
        <h2 class="text-3xl font-bold text-gray-800 mb-2">📊 Résultats de l'optimisation</h2>
        <p class="text-gray-500">Vous pouvez télécharger chaque image individuellement ou toutes ensemble</p>
    </div>

    @if($images->count() > 0)
        <div class="bg-white rounded-2xl shadow-lg p-6 mb-6">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-lg font-semibold text-gray-800">
                    {{ $images->count() }} image(s) optimisée(s)
                </h3>
                <a href="{{ route('optimizer.index') }}"
                   class="bg-gray-100 text-gray-600 px-4 py-2 rounded-lg text-sm hover:bg-gray-200 transition">
                    ← Nouvelle optimisation
                </a>
            </div>

            @foreach($images as $image)
                <div class="border rounded-xl p-4 mb-4 hover:shadow-md transition">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <span class="text-2xl">
                                @if($image->status === 'completed') ✅
                                @elseif($image->status === 'failed') ❌
                                @else ⏳
                                @endif
                            </span>
                            <div>
                                <p class="font-medium text-gray-800">{{ $image->original_name }}</p>
                                <p class="text-sm text-gray-500">
                                    @if($image->original_size && $image->optimized_size)
                                        {{ number_format($image->original_size / 1024, 1) }} KB
                                        →
                                        {{ number_format($image->optimized_size / 1024, 1) }} KB
                                        @if($image->format_converted_to)
                                            <span class="ml-2 font-semibold text-indigo-600">
                                                ({{ strtoupper($image->format_converted_to) }})
                                            </span>
                                        @endif
                                    @else
                                        En attente...
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center space-x-3">
                            @if($image->status === 'completed')
                                <span class="text-lg font-bold
                                    @if($image->gain >= 50) text-green-600
                                    @elseif($image->gain >= 30) text-yellow-600
                                    @else text-red-600 @endif">
                                    {{ $image->gain }}%
                                </span>
                                <a href="{{ url('/api/download/' . $image->id) }}"
                                   class="btn-primary text-white px-4 py-2 rounded-lg text-sm font-medium hover:no-underline">
                                    📥 Télécharger
                                </a>
                            @elseif($image->status === 'failed')
                                <span class="text-red-500 text-sm">Échec</span>
                            @else
                                <span class="text-gray-400 text-sm">⏳ En cours...</span>
                            @endif
                        </div>
                    </div>
                    @if($image->status === 'completed' && $image->gain)
                        <div class="gain-bar mt-3">
                            <div class="gain-bar-fill"
                                 style="width: {{ max($image->gain, 0) }}%"
                                 class="{{ $image->gain >= 50 ? 'bg-green-500' : ($image->gain >= 30 ? 'bg-yellow-500' : 'bg-red-500') }}">
                            </div>
                        </div>
                        <div class="flex justify-between text-xs text-gray-400 mt-1">
                            <span>0%</span>
                            <span>Gain: {{ $image->gain }}%</span>
                            <span>100%</span>
                        </div>
                    @endif
                </div>
            @endforeach

            <div class="mt-6 text-center">
                <a href="{{ url('/api/download-all/' . $sessionId) }}"
                   class="btn-primary text-white px-8 py-3 rounded-xl text-lg font-semibold inline-flex items-center space-x-2 hover:no-underline">
                    <span>📦</span>
                    <span>Tout télécharger (ZIP)</span>
                </a>
            </div>
        </div>
    @else
        <div class="bg-white rounded-2xl shadow-lg p-12 text-center">
            <div class="text-6xl mb-4">📭</div>
            <h3 class="text-xl font-semibold text-gray-700 mb-2">Aucun résultat</h3>
            <p class="text-gray-500 mb-6">Cette session est vide ou a expiré.</p>
            <a href="{{ route('optimizer.index') }}"
               class="btn-primary text-white px-6 py-3 rounded-xl font-medium hover:no-underline">
                ← Retour à l'optimiseur
            </a>
        </div>
    @endif
</div>
@endsection