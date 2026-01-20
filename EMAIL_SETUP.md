# 📧 Email Notificatie Configuratie

## Huidige Status

De applicatie heeft **graceful email fallback** geïmplementeerd:

### ✅ Wat werkt nu:
- **Database notificaties**: Altijd opgeslagen, ook als email faalt
- **Activity matching**: Werkt perfect en vindt geschikte dagen
- **Safe Mail Channel**: Probeert email te sturen, maar faalt gracefully
- **Uitgebreide logging**: Alle stappen worden gelogd voor debugging

### ⚠️ Gmail SMTP Authenticatie Probleem

De huidige SMTP configuratie met Gmail faalt. Dit komt waarschijnlijk door:
1. **App Password** is verlopen of incorrect
2. **2-Factor Authentication** is vereist
3. Gmail heeft de toegang geblokkeerd

## 🔧 Email Configuratie Oplossingen

### Optie 1: Gmail App Password Herstellen (Aanbevolen voor productie)

1. Ga naar [Google Account Security](https://myaccount.google.com/security)
2. Zorg dat 2-Factor Authentication is ingeschakeld
3. Ga naar "App Passwords" (zoek naar "app wachtwoorden")
4. Genereer een nieuw app password voor "Mail"
5. Update `.env`:
```env
MAIL_PASSWORD=xxxx xxxx xxxx xxxx  # Het 16-cijferige app password
```

### Optie 2: Gebruik Log Driver voor Development

Voor development kun je emails naar de log sturen:

```env
MAIL_MAILER=log
MAIL_LOG_CHANNEL=stack
```

Emails worden dan geschreven naar `storage/logs/laravel.log`

### Optie 3: Gebruik Mailtrap voor Testing

Gratis email testing service:

1. Maak account op [Mailtrap.io](https://mailtrap.io)
2. Krijg je credentials van je inbox
3. Update `.env`:
```env
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your-mailtrap-username
MAIL_PASSWORD=your-mailtrap-password
MAIL_ENCRYPTION=tls
```

## 📊 Hoe het Systeem Nu Werkt

### 1. Activity Matching Flow

```
User maakt activiteit aan
    ↓
Weer wordt opgehaald (API of cache)
    ↓
Matching algoritme vergelijkt:
  - Temperatuur (min/max range)
  - Windsnelheid (max)
  - Neerslag (max)
    ↓
Geschikte matches worden opgeslagen
    ↓
EERSTE geschikte match → notificatie
```

### 2. Notificatie Flow

```
Match gevonden
    ↓
Database notificatie wordt aangemaakt ✅
    ↓
SafeMailChannel probeert email te sturen
    ↓
    ├─→ Success: Email verzonden ✅
    └─→ Failure: Error wordt gelogd ⚠️
        (Database notificatie blijft bestaan)
```

### 3. Wat de Gebruiker Ziet

**In Dashboard**:
- ✅ Activiteiten met hun matches
- ✅ Notificatie badge met aantal ongelezen
- ✅ Lijst van geschikte dagen

**In Notificaties pagina**:
- ✅ Alle database notificaties
- ✅ Details over match (datum, tijd, weer)
- ✅ Link naar activiteit

**Via Email** (als SMTP werkt):
- 📧 Mooie HTML email met alle details
- 📧 Activiteit informatie
- 📧 Weersverwachting
- 📧 Call-to-action naar dashboard

## 🧪 Testing

### Test de Matching Logica

```bash
php artisan tinker
```

```php
// Bekijk actieve activiteiten
$activities = App\Models\Activity::where('is_active', true)->get();
foreach($activities as $activity) {
    echo "{$activity->name}: {$activity->suitableMatches()->count()} matches\n";
}

// Forceer matching (reset notified flag)
App\Models\ActivityMatch::query()->update(['user_notified' => false]);

// Run matching opnieuw
app(App\Services\WeatherService::class)->findActivityMatches();
```

### Bekijk Notificaties

```php
// Laatste notificaties voor een gebruiker
$user = App\Models\User::find(16);
$user->notifications()->latest()->get();
```

### Test Email (met logging)

```php
// Test mail - kijk in logs voor errors
$user = App\Models\User::first();
$match = App\Models\ActivityMatch::where('is_suitable', true)->first();
$user->notify(new App\Notifications\ActivityMatchFound($match));
```

## 📝 Logs Bekijken

```bash
# Live logs
php artisan pail

# Of tail de log file
tail -f storage/logs/laravel.log

# Zoek naar email errors
grep -i "email\|smtp\|mail" storage/logs/laravel.log | tail -20
```

## 🔍 Debugging Tips

### Geen matches gevonden?

Controleer:
1. Is de activiteit `is_active = true`?
2. Zijn er weather forecasts voor de locatie?
3. Zijn de criteria niet te streng?
```php
// Check forecasts voor locatie
App\Models\WeatherForecast::where('location', 'Amsterdam')->count();

// Check activiteit criteria
$activity = App\Models\Activity::find(1);
echo "Temp: {$activity->min_temperature}-{$activity->max_temperature}°C\n";
echo "Wind: max {$activity->max_wind_speed} km/h\n";
echo "Precip: max {$activity->max_precipitation} mm\n";
```

### Notificaties worden niet verzonden?

1. Check of er al een notificatie is:
```php
App\Models\ActivityMatch::where('user_notified', true)->count();
```

2. Reset en probeer opnieuw:
```php
App\Models\ActivityMatch::query()->update(['user_notified' => false]);
```

## ✨ Verbeteringen Gemaakt

1. ✅ **Precipitation Check**: Gebruikt nu 0 als default als max_precipitation NULL is
2. ✅ **Safe Email Channel**: Email failures crashen de app niet meer
3. ✅ **Uitgebreide Logging**: Alle stappen worden gelogd met details
4. ✅ **Better Error Messages**: Duidelijke foutmeldingen
5. ✅ **Database Fallback**: Notificaties worden altijd in database opgeslagen

## 🚀 Volgende Stappen

1. Fix Gmail SMTP (zie Optie 1 hierboven) of
2. Switch naar Mailtrap voor testing (zie Optie 3)
3. Test de volledige flow met werkende email
4. Verifieer dat gebruikers emails ontvangen

## 📧 Email Template

De email gebruikt: `resources/views/emails/activity-match.blade.php`

Bevat:
- Persoonlijke begroeting
- Activiteit details
- Weersverwachting
- Match datum en tijd
- Call-to-action button

Bij email failures wordt een simpele fallback gebruikt met basis informatie.
