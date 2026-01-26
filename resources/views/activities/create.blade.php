<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Nieuwe Activiteit Toevoegen') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('activities.store') }}"
                          x-data="{ submitting: false }"
                          @submit="submitting = true">
                        @csrf

                        <!-- Loading Overlay -->
                        <div x-show="submitting" 
                             x-cloak
                             class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                            <div class="bg-white rounded-lg p-8 max-w-sm w-full mx-4 text-center">
                                <div class="animate-spin rounded-full h-16 w-16 border-b-4 border-blue-600 mx-auto mb-4"></div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-2">Activiteit wordt aangemaakt...</h3>
                                <p class="text-sm text-gray-600">We zijn op zoek naar geschikte dagen voor je activiteit</p>
                            </div>
                        </div>

                        <!-- Naam -->
                        <div class="mb-4">
                            <label for="name" class="block text-sm font-medium text-gray-700">Activiteit Naam *</label>
                            <input type="text" name="name" id="name" required 
                                   value="{{ old('name') }}"
                                   placeholder="bijv. Fietsen, Wandelen, Zeilen"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Beschrijving -->
                        <div class="mb-4">
                            <label for="description" class="block text-sm font-medium text-gray-700">Beschrijving</label>
                            <textarea name="description" id="description" rows="3" 
                                      placeholder="Beschrijf je activiteit..."
                                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('description') }}</textarea>
                            @error('description')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Locatie -->
                        <div class="mb-4">
                            <label for="location" class="block text-sm font-medium text-gray-700">Locatie *</label>
                            <input type="text" name="location" id="location" required
                                   value="{{ old('location', Auth::user()->default_location ?? 'Amsterdam') }}"
                                   placeholder="bijv. Amsterdam, Rotterdam, Utrecht"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <p class="mt-1 text-sm text-gray-500">Vul de stad in waar je deze activiteit wilt doen</p>
                            @error('location')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Temperatuur -->
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div>
                                <label for="min_temperature" class="block text-sm font-medium text-gray-700">Min. Temperatuur (°C)</label>
                                <input type="number" name="min_temperature" id="min_temperature" step="0.5"
                                       value="{{ old('min_temperature', 10) }}"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                @error('min_temperature')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="max_temperature" class="block text-sm font-medium text-gray-700">Max. Temperatuur (°C)</label>
                                <input type="number" name="max_temperature" id="max_temperature" step="0.5"
                                       value="{{ old('max_temperature', 30) }}"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                @error('max_temperature')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Wind & Neerslag -->
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div>
                                <label for="max_wind_speed" class="block text-sm font-medium text-gray-700">Max. Windsnelheid (km/h)</label>
                                <input type="number" name="max_wind_speed" id="max_wind_speed" step="1"
                                       value="{{ old('max_wind_speed', 30) }}"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                @error('max_wind_speed')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="max_precipitation" class="block text-sm font-medium text-gray-700">Max. Neerslag (mm)</label>
                                <input type="number" name="max_precipitation" id="max_precipitation" step="0.1"
                                       value="{{ old('max_precipitation', 0) }}"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                @error('max_precipitation')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Duur -->
                        <div class="mb-4">
                            <label for="duration_hours" class="block text-sm font-medium text-gray-700">Duur (uren) *</label>
                            <input type="number" name="duration_hours" id="duration_hours" required
                                   value="{{ old('duration_hours', 1) }}"
                                   min="1" max="24"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            @error('duration_hours')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Buttons -->
                        <div class="flex justify-end gap-2">
                            <a href="{{ route('activities.index') }}" 
                               class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                                Annuleren
                            </a>
                            <button type="submit" 
                                    :disabled="submitting"
                                    :class="{ 'opacity-50 cursor-not-allowed': submitting }"
                                    class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 flex items-center">
                                <span x-show="!submitting">Activiteit Toevoegen</span>
                                <span x-show="submitting" class="flex items-center">
                                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Bezig...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
