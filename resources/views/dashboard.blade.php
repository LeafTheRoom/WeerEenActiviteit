<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Dashboard') }}
            </h2>
            <a href="{{ route('activities.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                Nieuwe Activiteit
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
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
                    <h3 class="text-lg font-semibold mb-4">Mijn Activiteiten</h3>
                    
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
