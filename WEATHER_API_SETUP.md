# 🌤️ OpenWeatherMap API Setup Guide

## Step 1: Get Your Free API Key

1. Go to [OpenWeatherMap](https://openweathermap.org/api)
2. Click on "Get API Key" or "Sign Up"
3. Create a free account
4. Navigate to your API keys section
5. Copy your API key (it may take a few minutes to activate)

## Step 2: Configure Your Application

### Add API Key to `.env` File

1. Copy `.env.example` to `.env` if you haven't already:
   ```bash
   cp .env.example .env
   ```

2. Open `.env` and add your OpenWeatherMap API key:
   ```env
   WEATHER_API_KEY=your_api_key_here
   WEATHER_API_URL=https://api.openweathermap.org/data/2.5
   WEATHER_DEFAULT_LOCATION=Amsterdam
   WEATHER_DEFAULT_COUNTRY=NL
   ```

3. Replace `your_api_key_here` with your actual API key

### Configuration Options

You can customize these settings in your `.env` file:

- **WEATHER_API_KEY**: Your OpenWeatherMap API key (required)
- **WEATHER_DEFAULT_LOCATION**: Default city (default: Amsterdam)
- **WEATHER_DEFAULT_COUNTRY**: Country code (default: NL for Netherlands)

## Step 3: Clear Cache

After updating your `.env` file, clear the config cache:

```bash
php artisan config:clear
```

## Step 4: Test the Integration

1. Start your Laravel server:
   ```bash
   php artisan serve
   ```

2. Log in to your dashboard at: `http://localhost:8000/dashboard`

3. Click the "Weer Updaten" (Update Weather) button

4. You should see a success message with the number of matches found

## How It Works

### Automatic Fallback
- If no API key is configured, the app automatically uses **dummy weather data** for testing
- This means the app will work even without an API key!

### Smart Caching
- Weather data is cached for **30 minutes** to reduce API calls
- The free tier allows **1,000 API calls per day**
- With caching, you can update weather many times without hitting limits

### Real-Time Matching
- When you click "Weer Updaten", it:
  1. Fetches 5-day forecast from OpenWeatherMap
  2. Stores it in your database
  3. Automatically matches all your activities
  4. Shows you how many suitable matches were found

## Free Tier Limitations

The free OpenWeatherMap API tier includes:
- ✅ 5-day forecast (3-hour intervals)
- ✅ 1,000 calls per day
- ✅ Current weather data
- ❌ Not included: 16-day forecast, historical data

This is perfect for WeerEenActiviteit! 🎉

## Troubleshooting

### "Kon weergegevens niet ophalen"
- Check that your API key is correct in `.env`
- Wait a few minutes if the API key is brand new
- Run `php artisan config:clear`

### API Key Not Working
- New API keys can take 10-60 minutes to activate
- Check your email for confirmation
- Verify the key is copied correctly (no extra spaces)

### Using Dummy Data Instead
- If you see "Using dummy data" in logs, it means:
  - No API key configured (working as intended for testing)
  - API key is invalid
  - API request failed (network issue)

## Commands

### Update Weather Manually (via Tinker)
```bash
php artisan tinker
>>> app(App\Services\WeatherService::class)->fetchForecast()
>>> app(App\Services\WeatherService::class)->findActivityMatches()
```

### Clear Weather Cache
```bash
php artisan cache:clear
```

### View Logs
```bash
tail -f storage/logs/laravel.log
```

## Support

For OpenWeatherMap API support:
- [API Documentation](https://openweathermap.org/api)
- [FAQ](https://openweathermap.org/faq)
- [Support](https://openweathermap.org/support)

Enjoy using real weather data! 🌦️
