<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Premium Activeren
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @if(session('error'))
                        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                            {{ session('error') }}
                        </div>
                    @endif

                    @if(auth()->user()->is_premium)
                        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                            <p class="font-semibold">Je hebt Premium!</p>
                            <p class="text-sm mt-1">Lifetime Premium</p>
                        </div>
                    @endif

                    <div class="mb-6">
                        <h3 class="text-lg font-semibold mb-2">Premium Voordelen</h3>
                        <ul class="list-disc list-inside space-y-2 text-gray-700">
                            <li>Maximaal 20 activiteiten (ipv 5)</li>
                            <li>Lifetime toegang</li>
                        </ul>
                    </div>

                    @if(!auth()->user()->is_premium)
                        <div class="mb-6">
                            <form method="POST" action="{{ route('premium.generate') }}">
                                @csrf
                                <button type="submit" class="w-full bg-blue-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-blue-700 transition">
                                    Betalen voor Premium
                                </button>
                            </form>
                        </div>

                        <div class="border-t pt-6">
                            <h3 class="text-lg font-semibold mb-4">Code Activeren</h3>
                            <form method="POST" action="{{ route('voucher.activate') }}" class="space-y-4">
                                @csrf

                                <div>
                                    <x-input-label for="code" :value="__('Premium Code')" />
                                    <x-text-input 
                                        id="code" 
                                        class="block mt-1 w-full uppercase" 
                                        type="text" 
                                        name="code" 
                                        :value="old('code')" 
                                        required 
                                        placeholder="XXXX-XXXX-XXXX"
                                        maxlength="20"
                                    />
                                    <x-input-error :messages="$errors->get('code')" class="mt-2" />
                                </div>

                                <div class="flex items-center justify-end">
                                    <x-primary-button>
                                        Activeer Code
                                    </x-primary-button>
                                </div>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

