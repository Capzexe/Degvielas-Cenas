# Degvielas Cenas Latvijā

Degvielas Cenas Latvijā ir latviešu valodā veidots projekts, kas salīdzina publiski pieejamus degvielas cenu ierakstus Latvijas DUS tīklos. Projekts prioritizē uzticamus avotus un nerāda izdomātas cenas.

## Galvenās lapas

- [Degvielas cenu karte]({{ route('gas.index') }}): interaktīva Latvijas degvielas cenu karte ar avotu norādēm.
- [Blogs]({{ route('blog.index') }}): skaidrojumi par degvielas cenu salīdzināšanu, avotiem un vēsturi.
- [Par projektu]({{ route('about') }}): projekta datu principi un ierobežojumi.
- [Sitemap]({{ route('seo.sitemap') }}): XML karte meklētājprogrammām.

## Bloga raksti

@foreach ($posts as $post)
- [{{ $post['title'] }}]({{ route('blog.show', $post['slug']) }}): {{ $post['description'] }}
@endforeach

## Datu principi

- Oficiāla stacijas cena tiek rādīta kartē tikai tad, ja tai ir konkrēta DUS adrese un koordinātas.
- Tīkla cena vai zemākā publicētā cena netiek attiecināta uz katru konkrēto DUS bez droša pamata.
- Testa cenas netiek izmantotas publiskajā cenu salīdzināšanā.
- Ja avotu nevar nolasīt droši, cena netiek aizvietota ar minējumu.

## Valoda un auditorija

Primārā valoda: latviešu.
Auditorija: Latvijas autovadītāji, kuri salīdzina benzīna 95, benzīna 98, dīzeļa un LPG cenas.
