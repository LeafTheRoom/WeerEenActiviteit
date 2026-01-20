<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Meldingen
            </h2>
            @if($unreadCount > 0)
                <form action="{{ route('notifications.readAll') }}" method="POST">
                    @csrf
                    <button type="submit" class="text-sm text-blue-600 hover:underline">
                        Markeer alles als gelezen ({{ $unreadCount }})
                    </button>
                </form>
            @endif
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

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @if($notifications->count() > 0)
                        <div class="space-y-3">
                            @foreach($notifications as $notification)
                                <div class="border rounded-lg p-4 {{ $notification->read_at ? 'bg-gray-50' : 'bg-blue-50 border-blue-200' }}">
                                    <div class="flex justify-between items-start">
                                        <div class="flex-1">
                                            @if($notification->data['type'] === 'activity_match')
                                                <div class="flex items-center mb-2">
                                                    <h3 class="font-semibold text-lg">Geschikte Dag Gevonden!</h3>
                                                    @if(!$notification->read_at)
                                                        <span class="ml-2 px-2 py-1 bg-blue-600 text-white text-xs rounded-full">Nieuw</span>
                                                    @endif
                                                </div>
                                                <p class="text-gray-700 mb-2">{{ $notification->data['message'] }}</p>
                                                <div class="grid grid-cols-2 md:grid-cols-3 gap-2 text-sm">
                                                    <div>
                                                        <span class="text-gray-500">Activiteit:</span>
                                                        <span class="font-medium">{{ $notification->data['activity_name'] }}</span>
                                                    </div>
                                                    <div>
                                                        <span class="text-gray-500">Locatie:</span>
                                                        <span class="font-medium">{{ $notification->data['location'] }}</span>
                                                    </div>
                                                    <div>
                                                        <span class="text-gray-500">Tijd:</span>
                                                        <span class="font-medium">{{ \Carbon\Carbon::parse($notification->data['match_time'])->format('H:i') }} uur</span>
                                                    </div>
                                                </div>
                                            @elseif($notification->data['type'] === 'weather_changed')
                                                <div class="flex items-center mb-2">
                                                    <h3 class="font-semibold text-lg">Weersverandering</h3>
                                                    @if(!$notification->read_at)
                                                        <span class="ml-2 px-2 py-1 bg-orange-600 text-white text-xs rounded-full">Nieuw</span>
                                                    @endif
                                                </div>
                                                <p class="text-gray-700 mb-2">{{ $notification->data['message'] }}</p>
                                                <p class="text-sm text-gray-600">{{ $notification->data['change_reason'] }}</p>
                                            @endif

                                            <div class="mt-3 text-xs text-gray-500">
                                                {{ $notification->created_at->diffForHumans() }}
                                            </div>
                                        </div>

                                        <div class="ml-4 flex flex-col gap-2">
                                            @if(!$notification->read_at)
                                                <form action="{{ route('notifications.read', $notification->id) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="text-sm text-blue-600 hover:underline">
                                                        Markeer als gelezen
                                                    </button>
                                                </form>
                                            @endif
                                            <form action="{{ route('notifications.destroy', $notification->id) }}" method="POST"
                                                  onsubmit="return confirm('Weet je zeker dat je deze melding wilt verwijderen?')">
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

                        <div class="mt-6">
                            {{ $notifications->links() }}
                        </div>
                    @else
                        <div class="text-center py-12">
                            <span class="text-6xl"></span>
                            <p class="mt-4 text-gray-500 text-lg">Je hebt nog geen meldingen</p>
                            <a href="{{ route('dashboard') }}" class="mt-4 inline-block text-blue-600 hover:underline">
                                Terug naar dashboard
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
