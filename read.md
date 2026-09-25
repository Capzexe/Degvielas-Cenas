# Latvijas degvielas cenu karte - palaišanas ceļvedis

Šis projekts darbojas bez Docker.

## Kā palaist mājaslapu

Atver divus termināļus.

### 1. terminālis - frontend

```powershell
cd "C:\Users\helvi\OneDrive\Documents\ChatGPT\New project\laravel-task-manager"
npm run dev
```

Atstāj šo termināli atvērtu.

### 2. terminālis - Laravel/PHP serveris

```powershell
cd "C:\Users\helvi\OneDrive\Documents\ChatGPT\New project\laravel-task-manager"
& "C:\xampp\php\php.exe" -S 127.0.0.1:9000 -t public
```

Atstāj arī šo termināli atvērtu.

Atver mājaslapu:

```text
http://127.0.0.1:9000/
```

## Kā ielādēt reālās publiskās cenas

Palaid šo komandu, lai mēģinātu ielasīt cenas no oficiālajām publiskajām DUS tīklu lapām:

```powershell
cd "C:\Users\helvi\OneDrive\Documents\ChatGPT\New project\laravel-task-manager"
& "C:\xampp\php\php.exe" artisan fetch:fuel-prices
```

Svarīgi: ne visi tīkli publicē pilnas aktuālās cenas tiešsaistē. Neste cenām šobrīd vajadzīgs lietotāju ziņojumu vai partnera/API risinājums.

Ja oficiālā lapa publicē tikai tīkla zemāko cenu, nevis pilnu katras stacijas cenu sarakstu, aplikācija šo cenu rāda kā publicētu cenu ierakstu, nevis kā precīzu cenu visām konkrētā zīmola stacijām.

## Cenu vēsture

Projekts turpina krāt cenu vēsturi tabulā `prices`. Katru reizi, kad palaid:

```powershell
& "C:\xampp\php\php.exe" artisan fetch:fuel-prices
```

tiek saglabāts jauns cenu ieraksts ar laiku `fetched_at`. Vēsture kļūst noderīga pēc vairākām ielādēm dažādos laikos vai tad, kad šī komanda regulāri darbojas uz servera.

Šobrīd vēstures diagramma nav sākumlapā, bet dati tiek vākti, lai vēlāk varētu uzbūvēt atsevišķu analītikas skatu.

## Testa cenas

Testa cenas projektā vairs netiek izmantotas. Ja oficiālā lapa nav sasniedzama vai cenu nevar droši nolasīt, aplikācija cenu nerāda, nevis aizvieto to ar izdomātu vērtību.

## Pirmreizējā sagatavošana

Ja datubāze vēl nav sagatavota, palaid:

```powershell
cd "C:\Users\helvi\OneDrive\Documents\ChatGPT\New project\laravel-task-manager"
& "C:\xampp\php\php.exe" artisan migrate --force
& "C:\xampp\php\php.exe" artisan fetch:fuel-prices
npm install
```

## Noderīgas saites

Mājaslapa:

```text
http://127.0.0.1:9000/
http://127.0.0.1:9000/degvielas-atlaides
http://127.0.0.1:9000/blog
http://127.0.0.1:9000/par-projektu
```

API:

```text
http://127.0.0.1:9000/api/stations/cheapest?fuel_type=95
http://127.0.0.1:9000/api/stations/cheapest?fuel_type=98
http://127.0.0.1:9000/api/stations/cheapest?fuel_type=Diesel
http://127.0.0.1:9000/api/stations/cheapest?fuel_type=LPG
```

## Problēmu novēršana

Ja `php artisan serve` nestrādā, izmanto šo:

```powershell
& "C:\xampp\php\php.exe" -S 127.0.0.1:9000 -t public
```

Ja `php` netiek atpazīts, izmanto XAMPP PHP pilno ceļu:

```powershell
& "C:\xampp\php\php.exe" -v
```

Ja lapa atveras, bet cenas ir tukšas, palaid:

```powershell
& "C:\xampp\php\php.exe" artisan fetch:fuel-prices
```

Ja oficiālās lapas neatdod nolasāmas cenas, pārbaudi interneta savienojumu, avota lapas pieejamību un palaid importu vēlreiz. Izdomātas cenas netiek saglabātas.
