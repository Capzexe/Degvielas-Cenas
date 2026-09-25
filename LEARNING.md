# Degvielas cenu projekta tehniskā karte

Šis fails ir īss ceļvedis, kur projektā atrodas galvenās daļas.

## Pamata plūsma

```text
publiska cenu lapa -> scraperis -> prices/stations tabulas -> API -> Vue karte un saraksts
```

## Faili, ar kuriem sākt

- `routes/web.php` - publiskās lapas: sākumlapa, akcijas, blogs, par projektu, SEO faili.
- `routes/api.php` - API endpointi frontendam.
- `app/Console/Commands/FetchFuelPrices.php` - komanda cenu ielādei.
- `app/Services/FuelPrices/LatvianFuelPriceSources.php` - saraksts ar izmantotajiem cenu avotiem.
- `app/Services/FuelPrices/OfficialFuelPricePage.php` - cenu un staciju datu nolasīšana no publiskām lapām.
- `app/Http/Controllers/Api/StationController.php` - lētāko cenu API.
- `resources/js/components/GasTracker.vue` - galvenā Vue karte un cenu saraksts.
- `app/Support/DiscountOffers.php` - konkrētās atlaides sadaļai `/akcijas`.
- `app/Support/BlogPosts.php` - SEO bloga raksti.

## Datubāze

- `stations` - DUS nosaukums, zīmols, adrese un koordinātes.
- `prices` - degvielas veids, cena, ielādes laiks un avota tips.

Katru reizi, kad tiek palaista cenu ielādes komanda, projekts saglabā jaunu cenu ierakstu. Tā veidojas vēsture, pat ja diagramma pašlaik nav sākumlapā.

## Noderīgas komandas

Šajā datorā izmanto XAMPP PHP:

```powershell
& "C:\xampp\php\php.exe" artisan migrate --force
& "C:\xampp\php\php.exe" artisan fetch:fuel-prices
& "C:\xampp\php\php.exe" artisan route:list
& "C:\xampp\php\php.exe" artisan test
```

Frontendam:

```powershell
npm run dev
npm run build
```

## Ko uzmanīt

- Nepievieno izdomātas degvielas cenas.
- Ja avots nav droši nolasāms, labāk nerādīt cenu nekā rādīt nepatiesu.
- Kartē precīzi jāparāda tikai tās stacijas, kurām ir īstas koordinātes.
- Atlaides sadaļā jāiekļauj tikai piedāvājumi ar konkrētu cenu vai centu/litra vērtību.
