<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Mijn Activiteiten') }}
            </h2>
            <a href="{{ route('activities.create') }}" 
               class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                Nieuwe Activiteit
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-md">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-md">
                    {{ session('error') }}
                    @if(!Auth::user()->is_premium)
                        <a href="{{ route('premium') }}" class="underline font-semibold ml-2">Activeer Premium</a>
                    @endif
                </div>
            @endif

            <div class="mb-4 p-4 bg-blue-50 border border-blue-200 rounded-md">
                <p class="text-sm text-gray-700">
                    Je hebt <strong>{{ Auth::user()->activities()->count() }}</strong> van <strong>{{ Auth::user()->max_activities }}</strong> activiteiten.
                    @if(!Auth::user()->is_premium)
                        <a href="{{ route('premium') }}" class="text-blue-600 hover:underline ml-2">Upgrade naar Premium</a>
                    @endif
                </p>
            </div>

            @if($activities->isEmpty())
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-center text-gray-500">
                        <p class="mb-4">Je hebt nog geen activiteiten toegevoegd.</p>
                        <a href="{{ route('activities.create') }}" 
                           class="inline-block px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                            Voeg je eerste activiteit toe
                        </a>
                    </div>
                </div>
            @else
                <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                    @foreach($activities as $activity)
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <div class="flex justify-between items-start mb-2">
                                    <h3 class="text-lg font-semibold text-gray-800">{{ $activity->name }}</h3>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $activity->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                        {{ $activity->is_active ? 'Actief' : 'Inactief' }}
                                    </span>
                                </div>

                                @if($activity->description)
                                    <p class="text-sm text-gray-600 mb-4">{{ Str::limit($activity->description, 100) }}</p>
                                @endif

                                <div class="space-y-2 text-sm text-gray-700 mb-4">
                                    @if($activity->min_temperature || $activity->max_temperature)
                                        <div class="flex items-center">
                                            <svg class="w-4 h-4 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                            </svg>
                                            <span>
                                                @if($activity->min_temperature && $activity->max_temperature)
                                                    {{ $activity->min_temperature }}°C - {{ $activity->max_temperature }}°C
                                                @elseif($activity->min_temperature)
                                                    Min. {{ $activity->min_temperature }}°C
                                                @else
                                                    Max. {{ $activity->max_temperature }}°C
                                                @endif
                                            </span>
                                        </div>
                                    @endif

                                    @if($activity->max_wind_speed)
                                        <div class="flex items-center">
                                            <svg class="w-4 h-4 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                                            </svg>
                                            <span>Max. wind: {{ $activity->max_wind_speed }} km/h</span>
                                        </div>
                                    @endif

                                    @if($activity->max_precipitation !== null)
                                        <div class="flex items-center">
                                            <svg class="w-4 h-4 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                            </svg>
                                            <span>Max. neerslag: {{ $activity->max_precipitation }} mm</span>
                                        </div>
                                    @endif

                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <span>{{ $activity->duration_hours }} {{ $activity->duration_hours == 1 ? 'uur' : 'uren' }}</span>
                                    </div>
                                </div>

                                @if($activity->matches_count > 0)
                                    <div class="mb-4 text-sm">
                                        <span class="text-gray-600">Matches: </span>
                                        <span class="font-medium text-green-600">{{ $activity->suitable_matches_count }}</span>
                                        <span class="text-gray-400"> / {{ $activity->matches_count }}</span>
                                    </div>
                                @endif

                                <div class="flex gap-2">
                                    <a href="{{ route('activities.edit', $activity) }}" 
                                       class="flex-1 text-center px-3 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 text-sm">
                                        Bewerken
                                    </a>
                                    <form action="{{ route('activities.destroy', $activity) }}" method="POST" 
                                          onsubmit="return confirm('Weet je zeker dat je deze activiteit wilt verwijderen?');"
                                          class="flex-1">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="w-full px-3 py-2 bg-red-100 text-red-700 rounded-md hover:bg-red-200 text-sm">
                                            Verwijderen
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
