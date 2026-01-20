<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Geschikte dag gevonden!</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        h1 {
            color: #2563eb;
            margin-top: 0;
        }
        .match-info {
            background-color: #eff6ff;
            padding: 20px;
            border-radius: 6px;
            margin: 20px 0;
        }
        .weather-info {
            background-color: #f0fdf4;
            padding: 20px;
            border-radius: 6px;
            margin: 20px 0;
        }
        .preferences {
            background-color: #fef3c7;
            padding: 20px;
            border-radius: 6px;
            margin: 20px 0;
        }
        .info-row {
            margin: 10px 0;
        }
        .label {
            font-weight: bold;
            color: #555;
        }
        .value {
            color: #222;
        }
        .button {
            display: inline-block;
            padding: 12px 30px;
            background-color: #2563eb;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            margin-top: 20px;
        }
        .button:hover {
            background-color: #1d4ed8;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            color: #666;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🎉 Geschikte dag gevonden!</h1>
        
        <p>Hallo {{ $userName }},</p>
        
        <p>Goed nieuws! We hebben een perfecte dag gevonden voor je activiteit <strong>{{ $activityName }}</strong>.</p>
        
        <div class="match-info">
            <h2 style="margin-top: 0; color: #2563eb;">📅 Match details</h2>
            <div class="info-row">
                <span class="label">Datum:</span>
                <span class="value">{{ $matchDate }}</span>
            </div>
            <div class="info-row">
                <span class="label">Tijd:</span>
                <span class="value">{{ $matchTime }} - {{ $endTime ?? $matchTime }} uur</span>
            </div>
            <div class="info-row">
                <span class="label">Locatie:</span>
                <span class="value">{{ $location }}</span>
            </div>
            <div class="info-row">
                <span class="label">Duur:</span>
                <span class="value">{{ $duration }} uur</span>
            </div>
        </div>

        <div class="preferences">
            <h2 style="margin-top: 0; color: #92400e;">✨ Jouw wensen</h2>
            @if($minTemperature !== null || $maxTemperature !== null)
            <div class="info-row">
                <span class="label">Temperatuur:</span>
                <span class="value">
                    @if($minTemperature !== null && $maxTemperature !== null)
                        Tussen {{ $minTemperature }}°C en {{ $maxTemperature }}°C
                    @elseif($minTemperature !== null)
                        Minimaal {{ $minTemperature }}°C
                    @else
                        Maximaal {{ $maxTemperature }}°C
                    @endif
                </span>
            </div>
            @endif
            
            @if($maxWindSpeed !== null)
            <div class="info-row">
                <span class="label">Windsnelheid:</span>
                <span class="value">Maximaal {{ $maxWindSpeed }} km/h</span>
            </div>
            @endif
            
            @if($maxPrecipitation !== null)
            <div class="info-row">
                <span class="label">Neerslag:</span>
                <span class="value">Maximaal {{ $maxPrecipitation }} mm</span>
            </div>
            @endif

            @if(!empty($preferredTimes))
            <div class="info-row">
                <span class="label">Voorkeurstijden:</span>
                <span class="value">{{ implode(', ', $preferredTimes) }}</span>
            </div>
            @endif
        </div>

        <div class="weather-info">
            <h2 style="margin-top: 0; color: #065f46;">🌤️ Weersverwachting</h2>
            <div class="info-row">
                <span class="label">Temperatuur:</span>
                <span class="value">{{ $weatherTemperature }}°C</span>
            </div>
            <div class="info-row">
                <span class="label">Windsnelheid:</span>
                <span class="value">{{ $weatherWindSpeed }} km/h</span>
            </div>
            <div class="info-row">
                <span class="label">Neerslag:</span>
                <span class="value">{{ $weatherPrecipitation }} mm</span>
            </div>
            <div class="info-row">
                <span class="label">Omschrijving:</span>
                <span class="value">{{ $weatherDescription }}</span>
            </div>
        </div>

        <a href="{{ $dashboardUrl }}" class="button">Bekijk op Dashboard</a>

        <p style="margin-top: 30px;">Veel plezier met je activiteit!</p>

        <div class="footer">
            <p>Je ontvangt deze mail omdat er een match is gevonden voor je activiteit in WeerEenActiviteit.</p>
        </div>
    </div>
</body>
</html>
