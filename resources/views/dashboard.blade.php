<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Dashboard') }}
            </h2>
            <div class="flex gap-2">
                <form action="{{ route('weather.update') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        Weer Updaten
                    </button>
                </form>
                <a href="{{ route('activities.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                    + Nieuwe Activiteit
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Success/Error Messages -->
            @if (session('success'))
                <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif
            
            @if (session('error'))
                <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            <!-- Welkom, "GEBRUIKER NAAM" -->
            <div class="h1 text-center">
                <h1 class="text-5xl font-bold text-blue-600 mb-4">WeerEenActiviteit</h1>
                <p class="text-2xl text-gray-700">Welkom, {{ Auth::user()->name }}</p>
            </div>
            <!-- Statistieken -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-gray-500 text-sm">Totaal Activiteiten</div>
                        <div class="text-3xl font-bold text-blue-600">{{ $stats['total_activities'] }}</div>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-gray-500 text-sm">Actieve Activiteiten</div>
                        <div class="text-3xl font-bold text-green-600">{{ $stats['active_activities'] }}</div>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-gray-500 text-sm">Geschikte Dagen</div>
                        <div class="text-3xl font-bold text-purple-600">{{ $stats['suitable_matches'] }}</div>
                    </div>
                </div>
            </div>

            <!-- Mijn Activiteiten -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Recenste toegevoegde activiteit</h3>
                    
                    @if($activities->count() > 0)
                        <div class="space-y-3">
                            @foreach($activities as $activity)
                                <div class="border rounded-lg p-4 hover:bg-gray-50 transition">
                                    <div class="flex justify-between items-start">
                                        <div class="flex-1">
                                            <h4 class="font-semibold text-lg">{{ $activity->name }}</h4>
                                            @if($activity->description)
                                                <p class="text-sm text-gray-600 mt-1">{{ Str::limit($activity->description, 100) }}</p>
                                            @endif
                                            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mt-3 text-sm">
                                                <div>
                                                    <span class="text-gray-500">Temp:</span>
                                                    <span class="font-medium">{{ $activity->min_temperature }}°C - {{ $activity->max_temperature }}°C</span>
                                                </div>
                                                <div>
                                                    <span class="text-gray-500">Max. Wind:</span>
                                                    <span class="font-medium">{{ $activity->max_wind_speed }} km/h</span>
                                                </div>
                                                <div>
                                                    <span class="text-gray-500">Max. Neerslag:</span>
                                                    <span class="font-medium">{{ $activity->max_precipitation }} mm</span>
                                                </div>
                                                <div>
                                                    <span class="text-gray-500">Geschikte dagen:</span>
                                                    <span class="font-medium text-green-600">{{ $activity->suitable_matches_count }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="ml-4 flex flex-col gap-2">
                                            <a href="{{ route('activities.edit', $activity) }}" class="text-sm text-blue-600 hover:underline">
                                                Bewerken
                                            </a>
                                            <form action="{{ route('activities.destroy', $activity) }}" method="POST" 
                                                  onsubmit="return confirm('Weet je zeker dat je deze activiteit wilt verwijderen?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-sm text-red-600 hover:underline">
                                                    Verwijderen
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8 text-gray-500">
                            <p>Je hebt nog geen activiteiten</p>
                            <a href="{{ route('activities.create') }}" class="text-blue-600 hover:underline mt-2 inline-block">
                                Voeg je eerste activiteit toe
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
