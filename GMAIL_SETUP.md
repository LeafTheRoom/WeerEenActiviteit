# Gmail SMTP Setup voor WeerEenActiviteit

## Stap 1: Gmail App Password Aanmaken

1. Ga naar je Google Account: https://myaccount.google.com/
2. Klik op "Security" (Beveiliging) in het linker menu
3. Zorg dat "2-Step Verification" (2-staps-verificatie) **AAN** staat
   - Als dit uit staat, zet het dan eerst aan
4. Scroll naar beneden naar "App passwords" (App-wachtwoorden)
5. Klik op "App passwords"
6. Maak een nieuw app password aan:
   - Selecteer app: "Mail"
   - Selecteer device: "Other" (Custom name)
   - Naam: "WeerEenActiviteit"
7. Klik "Generate"
8. Je krijgt een 16-karakter wachtwoord (bijv: `abcd efgh ijkl mnop`)
9. **KOPIEER DIT WACHTWOORD** (je kunt het maar 1x zien!)

## Stap 2: Voeg het wachtwoord toe aan .env

Open `.env` bestand en voeg het app password toe:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=testerbram123@gmail.com
MAIL_PASSWORD=abcdefghijklmnop    # <-- PLAK HIER JE APP PASSWORD (zonder spaties!)
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="testerbram123@gmail.com"
MAIL_FROM_NAME="WeerEenActiviteit"
```

**LET OP:** Verwijder alle spaties uit het app password!

## Stap 3: Config Cache Clearen

```bash
php artisan config:clear
```

## Stap 4: Test Mail Versturen

Ga naar: http://127.0.0.1:8000/mail/preview

Klik op "Test Mail Versturen" - je zou nu een echte email moeten ontvangen!

## Problemen oplossen

### "Invalid credentials" error
- Check of je het juiste app password hebt gebruikt (geen spaties!)
- Check of je email adres correct is
- Check of 2FA aanstaat in je Google account

### "Less secure app access"
- Dit hoef je NIET aan te zetten
- Gebruik altijd App Passwords met 2FA

### Mail komt niet aan
- Check je spam folder
- Check of het Gmail account niet geblokkeerd is
- Test met een andere email ontvanger

## Alternatief: Outlook/Hotmail SMTP

Als Gmail niet werkt, gebruik dan Outlook:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp-mail.outlook.com
MAIL_PORT=587
MAIL_USERNAME=jouw@outlook.com
MAIL_PASSWORD=jouw_wachtwoord
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="jouw@outlook.com"
MAIL_FROM_NAME="WeerEenActiviteit"
```

Voor Outlook heb je GEEN app password nodig, gewoon je normale wachtwoord.
