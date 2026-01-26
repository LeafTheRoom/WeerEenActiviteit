<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Dashboard') }}
            </h2>
            <a href="{{ route('activities.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                + Nieuwe Activiteit
            </a>
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

            <!-- Welkom -->
            <div class="mb-8">
                <h1 class="text-4xl font-bold text-blue-600 mb-2">WeerEenActiviteit</h1>
                <p class="text-xl text-gray-700">Welkom, {{ Auth::user()->name }}</p>
            </div>
            
            <!-- Statistieken -->
            <div class="grid grid-cols-2 gap-4 mb-6">
                <div class="bg-white shadow-sm sm:rounded-lg p-4">
                    <div class="text-gray-500 text-sm">Totaal Activiteiten</div>
                    <div class="text-2xl font-bold text-blue-600">{{ $stats['total_activities'] }}</div>
                </div>
                <div class="bg-white shadow-sm sm:rounded-lg p-4">
                    <div class="text-gray-500 text-sm">Actieve Activiteiten</div>
                    <div class="text-2xl font-bold text-green-600">{{ $stats['active_activities'] }}</div>
                </div>
            </div>

            <!-- Mijn Activiteiten -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Recente Activiteiten</h3>
                    
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
                                                    <span class="text-gray-500">Max. Wind:</span><span class="font-medium">{{ $activity->max_wind_speed }} km/h</span>
                                                </div>
                                                <div>
                                                    <span class="text-gray-500">Max. Neerslag:</span>
                                                    <span class="font-medium">{{ $activity->max_precipitation }} mm</span>
                                                </div>
                                                
                                            </div>
                                            @php
                                                $bestMatch = $activity->getBestMatchDate();
                                            @endphp
                                            @if($bestMatch)
                                                @php
                                                    $startTime = \Carbon\Carbon::parse($bestMatch['time']);
                                                    $endTime = $startTime->copy()->addHours($activity->duration_hours);
                                                @endphp
                                                <div class="mt-3 p-3 bg-green-50 border border-green-200 rounded-lg">
                                                    <div class="text-sm font-semibold text-green-800">Geschikte Dag Gevonden!</div>
                                                    <div class="text-sm text-green-700 mt-1">
                                                        <span class="font-medium">{{ \Carbon\Carbon::parse($bestMatch['date'])->isoFormat('dddd D MMMM YYYY') }}</span>
                                                        van {{ $startTime->format('H:i') }} tot {{ $endTime->format('H:i') }} uur ({{ $activity->duration_hours }} {{ $activity->duration_hours == 1 ? 'uur' : 'uren' }})
                                                    </div>
                                                    @if($bestMatch['weather'])
                                                        <div class="text-xs text-green-600 mt-1">
                                                            Temp: {{ $bestMatch['weather']->temperature }}°C | 
                                                            Wind: {{ $bestMatch['weather']->wind_speed }} km/h | 
                                                            Neerslag: {{ $bestMatch['weather']->precipitation }} mm
                                                        </div>
                                                    @endif
                                                </div>
                                            @endif
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
    
    <!-- Trigger immediate match popup als er een directe match is gevonden -->
    @if (session('immediate_match'))
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                // Wacht tot Alpine.js beschikbaar is
                setTimeout(() => {
                    const matchData = @json(session('immediate_match'));
                    
                    // Voor immediate matches (nieuw toegevoegde activiteit), altijd tonen
                    // Reset eventuele eerdere localStorage voor deze match
                    const matchKey = `match_shown_${matchData.activityName}_${matchData.date}`;
                    localStorage.removeItem(matchKey);
                    
                    window.dispatchEvent(new CustomEvent('match-found', {
                        detail: matchData
                    }));
                }, 100);
            });
        </script>
    @endif</x-app-layout>