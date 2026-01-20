<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;

echo "=== Gmail SMTP Test ===" . PHP_EOL . PHP_EOL;

echo "Configuratie:" . PHP_EOL;
echo "MAIL_MAILER: " . Config::get('mail.default') . PHP_EOL;
echo "MAIL_HOST: " . Config::get('mail.mailers.smtp.host') . PHP_EOL;
echo "MAIL_PORT: " . Config::get('mail.mailers.smtp.port') . PHP_EOL;
echo "MAIL_USERNAME: " . Config::get('mail.mailers.smtp.username') . PHP_EOL;
echo "MAIL_FROM: " . Config::get('mail.from.address') . PHP_EOL;
echo PHP_EOL;

echo "Versturen test email..." . PHP_EOL;

try {
    Mail::raw('Dit is een test email vanuit Laravel naar Gmail.', function($message) {
        $message->to('testerbram123@gmail.com')
                ->subject('🎉 Test Email van WeerEenActiviteit');
    });
    
    echo "✅ SUCCESS! Email verstuurd naar testerbram123@gmail.com" . PHP_EOL;
    echo "   Check je inbox (en spam folder) binnen 30 seconden!" . PHP_EOL;
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . PHP_EOL;
    echo PHP_EOL;
    echo "Mogelijke oorzaken:" . PHP_EOL;
    echo "1. Gmail app password is incorrect voor testerbram123@gmail.com" . PHP_EOL;
    echo "2. 2FA staat niet aan in Google account" . PHP_EOL;
    echo "3. App password is niet gegenereerd voor dit account" . PHP_EOL;
    echo PHP_EOL;
    echo "Zie GMAIL_SETUP.md voor instructies!" . PHP_EOL;
}
