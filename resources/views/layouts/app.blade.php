<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>

        <!-- Toast Notifications -->
        <div x-data="{ show: false, message: '', type: 'success' }" 
             @toast.window="show = true; message = $event.detail.message; type = $event.detail.type || 'success'; setTimeout(() => show = false, 5000)"
             x-show="show"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform translate-y-2"
             x-transition:enter-end="opacity-100 transform translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 transform translate-y-0"
             x-transition:leave-end="opacity-0 transform translate-y-2"
             class="fixed bottom-4 right-4 z-50 max-w-sm w-full"
             style="display: none;">
            <div :class="{
                'bg-green-500': type === 'success',
                'bg-blue-500': type === 'info',
                'bg-yellow-500': type === 'warning',
                'bg-red-500': type === 'error'
            }" class="rounded-lg shadow-lg p-4 text-white">
                <div class="flex items-center justify-between">
                    <p x-text="message" class="font-medium"></p>
                    <button @click="show = false" class="ml-4 text-white hover:text-gray-200">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Match Found Modal -->
        <div x-data="matchNotificationModal()" 
             @match-found.window="handleMatchFound($event.detail)"
             x-show="showModal"
             x-cloak
             class="fixed inset-0 z-50 overflow-y-auto"
             style="display: none;">
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"
                 @click="closeModal()"></div>

            <!-- Modal -->
            <div class="flex items-center justify-center min-h-screen p-4">
                <div @click.away="closeModal()" 
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 transform scale-90"
                     x-transition:enter-end="opacity-100 transform scale-100"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100 transform scale-100"
                     x-transition:leave-end="opacity-0 transform scale-90"
                     class="relative bg-white rounded-lg shadow-xl max-w-lg w-full p-6">
                    
                    <!-- Success Icon -->
                    <div class="flex items-center justify-center w-16 h-16 mx-auto bg-green-100 rounded-full mb-4">
                        <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>

                    <!-- Title -->
                    <h3 class="text-2xl font-bold text-center text-gray-900 mb-2">
                        Geschikte Dag Gevonden! 🎉
                    </h3>

                    <!-- Activity Name -->
                    <p class="text-center text-lg font-semibold text-blue-600 mb-4" x-text="matchData.activityName"></p>

                    <!-- Match Details -->
                    <div class="bg-gray-50 rounded-lg p-4 mb-4">
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Datum:</span>
                                <span class="font-semibold" x-text="matchData.date"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Tijd:</span>
                                <span class="font-semibold" x-text="matchData.time"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Temperatuur:</span>
                                <span class="font-semibold" x-text="matchData.temperature + '°C'"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Wind:</span>
                                <span class="font-semibold" x-text="matchData.windSpeed + ' km/h'"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Neerslag:</span>
                                <span class="font-semibold" x-text="matchData.precipitation + ' mm'"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Close Button -->
                    <button @click="closeModal()" 
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded-lg transition">
                        Geweldig!
                    </button>
                </div>
            </div>
        </div>

        <!-- Notification Checker -->
        @auth
        <script>
            function matchNotificationModal() {
                return {
                    showModal: false,
                    matchData: {
                        activityName: '',
                        date: '',
                        time: '',
                        temperature: '',
                        windSpeed: '',
                        precipitation: ''
                    },
                    handleMatchFound(data) {
                        this.matchData = data;
                        this.showModal = true;
                    },
                    closeModal() {
                        this.showModal = false;
                        // Sla op dat deze match is getoond en weggeklikt
                        if (this.matchData.activityName) {
                            const matchKey = `match_shown_${this.matchData.activityName}_${this.matchData.date}`;
                            localStorage.setItem(matchKey, 'true');
                        }
                    }
                }
            }

            // Check voor nieuwe notificaties elke 30 seconden
            let lastNotificationCheck = Date.now();
            
            function checkForNewMatches() {
                fetch('/api/check-notifications', {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.hasNewMatches && data.matches) {
                        data.matches.forEach(match => {
                            // Check of deze match al eerder is getoond
                            const matchKey = `match_shown_${match.activityName}_${match.date}`;
                            const alreadyShown = localStorage.getItem(matchKey);
                            
                            // Toon alleen als nog niet eerder getoond
                            if (!alreadyShown) {
                                window.dispatchEvent(new CustomEvent('match-found', {
                                    detail: match
                                }));
                            }
                        });
                    }
                })
                .catch(error => {
                    console.error('Error checking for matches:', error);
                });
            }

            // Check direct bij laden en daarna elke 30 seconden
            document.addEventListener('DOMContentLoaded', () => {
                checkForNewMatches();
                setInterval(checkForNewMatches, 30000);
            });
        </script>
        @endauth
    </body>
</html>
