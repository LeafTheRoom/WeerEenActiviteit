<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Mail Preview
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="mb-4 pb-4 border-b">
                        <h3 class="text-lg font-semibold">Onderwerp:</h3>
                        <p class="text-gray-700">{{ $subject }}</p>
                    </div>

                    <div class="border rounded-lg p-6 bg-gray-50">
                        <div class="mb-4">
                            <p class="text-lg font-semibold">{{ $greeting }}</p>
                        </div>

                        @foreach($introLines as $line)
                            <p class="mb-2">{!! $line !!}</p>
                        @endforeach

                        @if($actionText)
                            <div class="my-6">
                                <a href="{{ $actionUrl }}" class="inline-block px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                    {{ $actionText }}
                                </a>
                            </div>
                        @endif

                        @foreach($outroLines as $line)
                            <p class="mb-2">{!! $line !!}</p>
                        @endforeach
                    </div>

                    <div class="mt-6">
                        <form method="POST" action="{{ route('mail.test.send') }}">
                            @csrf
                            <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                                Test Mail Versturen
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
