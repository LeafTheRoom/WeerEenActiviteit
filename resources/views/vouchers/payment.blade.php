<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Betaling Geslaagd
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="mb-6 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                        <p class="font-semibold text-lg mb-2">Bedankt voor je aankoop!</p>
                        <p class="text-sm">Je betaling is succesvol verwerkt.</p>
                    </div>

                    <div class="mb-6">
                        <h3 class="text-lg font-semibold mb-3">Jouw Premium Code</h3>
                        <div class="bg-gray-100 p-6 rounded-lg border-2 border-blue-500">
                            <p class="text-3xl font-mono font-bold text-center text-blue-600 tracking-wider">
                                {{ $code }}
                            </p>
                        </div>
                        <p class="text-sm text-gray-600 mt-3">
                            Kopieer deze code en vul hem hieronder in om Premium te activeren.
                        </p>
                    </div>

                    <div class="border-t pt-6">
                        <h3 class="text-lg font-semibold mb-4">Activeer Premium</h3>
                        <form method="POST" action="{{ route('voucher.activate') }}" class="space-y-4">
                            @csrf

                            <div>
                                <x-input-label for="code" :value="__('Premium Code')" />
                                <x-text-input 
                                    id="code" 
                                    class="block mt-1 w-full uppercase font-mono" 
                                    type="text" 
                                    name="code" 
                                    value="{{ $code }}"
                                    required 
                                    autofocus
                                    placeholder="XXXX-XXXX-XXXX"
                                />
                                <x-input-error :messages="$errors->get('code')" class="mt-2" />
                            </div>

                            <div class="flex items-center justify-end">
                                <x-primary-button class="bg-green-600 hover:bg-green-700">
                                    Activeer Premium Nu
                                </x-primary-button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
