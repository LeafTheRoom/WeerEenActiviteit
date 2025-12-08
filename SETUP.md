# 🌤️ WeerEenActiviteit - Installatie Guide

## Wat je nu moet doen om de app werkend te krijgen:

### Stap 1: Database Migraties Uitvoeren ✅

```bash
wsl php artisan migrate
```

Dit maakt de database tabellen aan voor:
- activities (jouw activiteiten)
- weather_forecasts (weerdata)
- activity_matches (matches tussen activiteit en weer)

### Stap 2: Laravel Breeze Installeren (Authenticatie) 🔐

```bash
wsl composer require laravel/breeze --dev
wsl php artisan breeze:install blade
wsl npm install
wsl npm run build
wsl php artisan migrate
```

Dit voegt login/registratie functionaliteit toe.

### Stap 3: Server Starten & Testen 🚀

```bash
# Start de Laravel server
wsl php artisan serve
```

Ga naar: **http://localhost:8000**

### Stap 4: Account Aanmaken & Testen

1. Klik op "Register"
2. Maak een account aan
3. Log in
4. Klik op "🔄 Weer Updaten" om dummy weerdata te genereren
5. Klik op "+ Nieuwe Activiteit" om je eerste activiteit toe te voegen

### Voorbeeld Activiteit: Fietsen 🚴‍♂️

```
Naam: Fietsen
Beschrijving: Lekker fietsen door de natuur
Min. Temperatuur: 15°C
Max. Temperatuur: 25°C
Max. Windsnelheid: 30 km/h
Max. Neerslag: 0 mm
Duur: 120 minuten
```

Na het toevoegen zie je automatisch wanneer het weer geschikt is!

---

## 🎯 Wat de App Doet

**WeerEenActiviteit** vergelijkt automatisch het weer met jouw activiteit-eisen:

- ✅ Voeg activiteiten toe (fietsen, wandelen, zeilen)
- ✅ Stel weereisen in (temperatuur, wind, neerslag)
- ✅ Krijg automatisch matches met geschikte dagen
- ✅ Zie match scores (0-100%) voor elk moment
- ✅ Dashboard met beste mogelijkheden

**Geen API key nodig!** De app gebruikt dummy weerdata voor testing.

---

## 📝 Quick Commands

```bash
# Database resetten
wsl php artisan migrate:fresh

# Dummy weerdata genereren (in tinker)
wsl php artisan tinker
>>> app(App\Services\WeatherService::class)->generateDummyForecast(7)

# Cache clearen
wsl php artisan cache:clear
wsl php artisan config:clear

# Server starten
wsl php artisan serve
```

---

## 🐛 Problemen?

**"Server running on [http://127.0.0.1:8000]"**  
✅ Perfect! Ga naar die URL in je browser

**"Auth routes not found"**  
→ Run: `wsl php artisan breeze:install blade`

**"Table not found"**  
→ Run: `wsl php artisan migrate`

**"No suitable matches"**  
→ Klik op "🔄 Weer Updaten" in de app

---

Veel succes! 🎉
