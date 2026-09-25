# Degvielas Cenas Latvijā

Laravel + Vue aplikācija, kas apkopo publiski pieejamas degvielas cenas Latvijā un parāda lētākās DUS kartē un sarakstā.

Projekta mērķis ir rādīt tikai datus, kurus var droši iegūt no publiskiem avotiem vai lietotāju/partneru iesniegumiem. Izdomātas testa cenas netiek rādītas.

## Funkcijas

- Lētāko cenu saraksts pēc degvielas veida: 95, 98, dīzelis, LPG.
- Pilna platuma karte ar DUS cenu marķieriem.
- Reālo cenu ielāde no publiskām avotu lapām, kur tas ir iespējams.
- Cenu vēstures krāšana datubāzē katrā ielādes reizē.
- Atsevišķa sadaļa ar konkrētām degvielas atlaidēm: `/akcijas`.
- Bloga sadaļa SEO saturam: `/blog`.
- SEO tehniskie faili: `/sitemap.xml`, `/robots.txt`, `/llms.txt`.
- API endpointi frontendam un nākotnes integrācijām.

## Tehnoloģijas

- Laravel 12
- PHP 8.2+
- Vue 3 Composition API
- Tailwind CSS
- Leaflet kartei
- SQLite lokāli, MySQL produkcijā
- Vite frontend buildam

## Lokālā palaišana ar XAMPP

Atver divus PowerShell logus.

### 1. logs - Vue/Vite

```powershell
cd "C:\Users\helvi\OneDrive\Documents\ChatGPT\New project\cenalitra-lv"
npm run dev
```

### 2. logs - Laravel/PHP

```powershell
cd "C:\Users\helvi\OneDrive\Documents\ChatGPT\New project\cenalitra-lv"
& "C:\xampp\php\php.exe" -S 127.0.0.1:9000 -t public
```

Pēc tam atver:

```text
http://127.0.0.1:9000/
```

## Datu sagatavošana

Pirmreizējai datubāzes sagatavošanai:

```powershell
cd "C:\Users\helvi\OneDrive\Documents\ChatGPT\New project\cenalitra-lv"
& "C:\xampp\php\php.exe" artisan migrate --force
& "C:\xampp\php\php.exe" artisan fetch:fuel-prices
```

Atkārtotai cenu atjaunošanai:

```powershell
& "C:\xampp\php\php.exe" artisan fetch:fuel-prices
```

## Datu avoti

Šobrīd projekts mēģina nolasīt cenas tikai no avotiem, kur publiski ir iespējams iegūt cenu informāciju:

- Circle K
- Viada
- Virši
- Straujupīte

Neste un KOOL netiek aizpildīti ar izdomātām cenām. Ja nav droša publiska avota ar aktuālu cenu, cena netiek rādīta.

## Svarīgākie URL

```text
/
/degvielas-atlaides
/blog
/par-projektu
/api/stations/cheapest?fuel_type=95
/api/stations/cheapest?fuel_type=98
/api/stations/cheapest?fuel_type=Diesel
/api/stations/cheapest?fuel_type=LPG
/sitemap.xml
/llms.txt
```

## Produkcijas izvietošana

Uz hostinga dokumenta saknei jānorāda Laravel `public` mape. Regulārai cenu atjaunošanai jāuzliek cron:

```bash
php artisan schedule:run
```

vai tieši:

```bash
php artisan fetch:fuel-prices
```

Scheduler projektā ir sagatavots tā, lai cenu ielādes komanda varētu darboties automātiski.
