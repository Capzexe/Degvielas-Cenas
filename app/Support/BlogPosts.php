<?php

namespace App\Support;

use Illuminate\Support\Collection;

class BlogPosts
{
    /**
     * @return Collection<int, array<string, mixed>>
     */
    public static function all(): Collection
    {
        return collect(self::posts());
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function find(string $slug): ?array
    {
        return self::all()->first(function (array $post) use ($slug): bool {
            return $post['slug'] === $slug || in_array($slug, $post['aliases'] ?? [], true);
        });
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private static function posts(): array
    {
        return [
            [
                'slug' => 'degvielas-cenas-latvija-ka-salidzinat',
                'aliases' => ['ka-salidzinat-degvielas-cenas-latvija'],
                'title' => 'Kā salīdzināt degvielas cenas Latvijā',
                'seo_title' => 'Kā salīdzināt degvielas cenas Latvijā | Praktisks ceļvedis',
                'description' => 'Praktisks ceļvedis, kā salīdzināt degvielas cenas Latvijā, nepārmaksāt par braucienu līdz DUS un saprast cenu avotu kvalitāti.',
                'tag' => 'Padomi',
                'minutes' => '5 min',
                'published_at' => '2026-09-25',
                'updated_at' => '2026-09-25',
                'intro' => 'Lētākā cena kartē ne vienmēr nozīmē lētāko pirkumu. Reālais ietaupījums rodas tikai tad, ja ņem vērā attālumu līdz stacijai, auto patēriņu un to, cik litrus plāno pildīt.',
                'sections' => [
                    [
                        'heading' => 'Salīdzini tikai vienādu degvielas tipu',
                        'body' => [
                            'Benzīns 95, benzīns 98, dīzelis un LPG nav savstarpēji aizvietojami cenu salīdzināšanā. Ja tavs auto izmanto 95, cenu kartei jābūt filtrētai tieši uz 95, nevis uz kopējo lētāko cenu.',
                            'Tas pats attiecas uz dīzeli un LPG. Katram degvielas tipam cenu kustība var būt atšķirīga, un lētākā DUS vienam tipam var nebūt lētākā citam.',
                        ],
                    ],
                    [
                        'heading' => 'Aprēķini brauciena izmaksu līdz DUS',
                        'body' => [
                            'Ja lētākā stacija ir tālu, ietaupījumu var apēst pats brauciens. Piemēram, ja jābrauc papildus 8 kilometri un auto patērē 7 litrus uz 100 km, papildu degvielas izmaksas var būt lielākas par cenu starpību.',
                            'Tāpēc šajā projektā karte ir tikpat svarīga kā cenu saraksts: cena bez atrašanās vietas nav pilns salīdzinājums.',
                        ],
                    ],
                    [
                        'heading' => 'Pārbaudi cenu avotu',
                        'body' => [
                            'Oficiāla stacijas cena ir spēcīgākais signāls, jo tā ir piesaistīta konkrētai DUS adresei. Publicēta tīkla cena vai zemākā tīkla cena ir noderīga, bet tā nedrīkst tikt rādīta kā cena katrā konkrētā stacijā.',
                            'Tieši tāpēc aplikācijā cenu avots tiek rādīts pie katra ieraksta. Ja avots nav pietiekami precīzs, karte nerāda maldinošu punktu.',
                        ],
                    ],
                ],
                'faq' => [
                    ['question' => 'Vai lētākā degvielas cena vienmēr ir labākā izvēle?', 'answer' => 'Nē. Jāņem vērā attālums līdz DUS, pildāmais litru skaits un degvielas tips.'],
                    ['question' => 'Kāpēc dažas cenas nav redzamas kartē?', 'answer' => 'Karte rāda tikai cenu ierakstus ar konkrētu pārbaudāmu DUS lokāciju.'],
                ],
            ],
            [
                'slug' => 'dus-degvielas-cenas-interneta-kapec-atskiras',
                'aliases' => ['kapec-dus-cenas-interneta-nav-vienadas'],
                'title' => 'Kāpēc DUS cenas internetā nav vienādas',
                'seo_title' => 'Kāpēc DUS degvielas cenas internetā atšķiras | Datu avoti',
                'description' => 'Skaidrojums par to, kāpēc Circle K, Viada, Virši, Straujupīte, Neste un citi tīkli cenas publicē atšķirīgi.',
                'tag' => 'Datu avoti',
                'minutes' => '6 min',
                'published_at' => '2026-09-25',
                'updated_at' => '2026-09-25',
                'intro' => 'Degvielas cenu datiem nav viena universāla standarta. Daži tīkli publicē stacijas cenas, citi tīkla cenu vai zemāko cenu, bet daļai aktuālā cena pieejama tikai pašā stacijā.',
                'sections' => [
                    [
                        'heading' => 'Stacijas cena pret tīkla cenu',
                        'body' => [
                            'Stacijas cena ir piesaistīta konkrētai adresei, tāpēc to var droši rādīt kartē. Tīkla cena ir plašāks ieraksts, kas var nebūt precīzs katrai stacijai.',
                            'Ja cenu avots rāda tikai zemāko cenu tīklā, tā ir vērtīga informācija, bet to nedrīkst automātiski attiecināt uz visām šī zīmola DUS.',
                        ],
                    ],
                    [
                        'heading' => 'Kāpēc daļa cenu pazūd vai mainās',
                        'body' => [
                            'Oficiālās lapas var mainīt HTML struktūru, slēpt datus JavaScript slānī vai pārtraukt publisku cenu plūsmu. Tāpēc scraperim jābūt konservatīvam: ja cenu nevar nolasīt droši, labāk to nerādīt.',
                            'Šāda pieeja ir mazāk iespaidīga īstermiņā, bet ilgtermiņā uzticamāka lietotājiem un meklētājprogrammām.',
                        ],
                    ],
                    [
                        'heading' => 'Kāpēc Neste vajag citu risinājumu',
                        'body' => [
                            'Ja tīkls nepublicē pilnu aktuālo cenu plūsmu tiešsaistē, cenu kartei vajag partnera datu piekļuvi, oficiālu API vai lietotāju ziņojumus ar pārbaudes mehānismu.',
                            'Bez tā cenas nedrīkst aizvietot ar minējumiem, jo degvielas cenu projekts zaudē galveno vērtību: uzticību.',
                        ],
                    ],
                ],
                'faq' => [
                    ['question' => 'Vai publiska tīkla cena ir slikta?', 'answer' => 'Nē, bet tā nav tas pats, kas precīza cena konkrētā DUS adresē.'],
                    ['question' => 'Kāpēc aplikācija nerāda izdomātas cenas tukšo vietu aizpildīšanai?', 'answer' => 'Tāpēc, ka šāds saturs maldina autovadītājus un bojā datu uzticamību.'],
                ],
            ],
            [
                'slug' => 'benzins-95-98-dizelis-lpg-cenas',
                'aliases' => ['benzins-95-98-dizelis-lpg-cenu-salidzinasana'],
                'title' => 'Benzīns 95, 98, dīzelis un LPG: ko salīdzināt',
                'seo_title' => 'Benzīns 95, 98, dīzelis un LPG cenas Latvijā | Ko salīdzināt',
                'description' => 'Kā pareizi salīdzināt benzīna 95, 98, dīzeļa un LPG cenas Latvijas degvielas cenu kartē.',
                'tag' => 'Salīdzināšana',
                'minutes' => '4 min',
                'published_at' => '2026-09-25',
                'updated_at' => '2026-09-25',
                'intro' => 'Dažādiem degvielas veidiem ir dažādas cenu līknes, pieejamība un autovadītāju vajadzības. Tāpēc degvielas cenu kartē vienmēr jāsāk ar pareizo filtru.',
                'sections' => [
                    [
                        'heading' => 'Benzīns 95',
                        'body' => [
                            '95 ir biežāk izmantotais benzīna tips ikdienas automašīnām. Salīdzināšanā svarīgi skatīties tieši 95 cenu, nevis kopējo lētāko cenu stacijā.',
                        ],
                    ],
                    [
                        'heading' => 'Benzīns 98',
                        'body' => [
                            '98 parasti ir dārgāks un ne vienmēr pieejams visās stacijās. Ja auto ražotājs neprasa 98, lētāka 95 cena var būt praktiskāka izvēle.',
                        ],
                    ],
                    [
                        'heading' => 'Dīzelis un LPG',
                        'body' => [
                            'Dīzeļa cenas bieži interesē lielāka nobraukuma vadītājus, tāpēc cenu starpība uz litru var būt ļoti nozīmīga. LPG savukārt jāvērtē kopā ar pieejamību, jo ne katrā DUS ir autogāze.',
                            'Kartē degvielas tips jāizvēlas pirms salīdzinājuma, lai rezultāti būtu lietojami reālam braucienam.',
                        ],
                    ],
                ],
                'faq' => [
                    ['question' => 'Vai var salīdzināt 95 un dīzeļa cenu savā starpā?', 'answer' => 'Praktiski nē, jo auto parasti izmanto vienu konkrētu degvielas tipu.'],
                    ['question' => 'Kāpēc LPG rezultātu var būt mazāk?', 'answer' => 'Autogāze nav pieejama katrā stacijā un ne visi avoti to publicē vienādi.'],
                ],
            ],
            [
                'slug' => 'degvielas-cenu-vesture-latvija',
                'aliases' => ['ka-veidot-degvielas-cenu-vesturi'],
                'title' => 'Kā veidot degvielas cenu vēsturi',
                'seo_title' => 'Degvielas cenu vēsture Latvijā | Kā vākt un salīdzināt datus',
                'description' => 'Kā regulāra cenu importēšana palīdz saprast, vai degvielas cenas Latvijā kāpj, krīt vai paliek stabilas.',
                'tag' => 'Cenu vēsture',
                'minutes' => '5 min',
                'published_at' => '2026-09-25',
                'updated_at' => '2026-09-25',
                'intro' => 'Viena cena rāda tikai šodienu. Cenu vēsture sāk kļūt vērtīga tad, kad sistēma regulāri savāc ierakstus ar laiku un avotu.',
                'sections' => [
                    [
                        'heading' => 'Kāpēc vajag vairākus ierakstus laikā',
                        'body' => [
                            'Ja cena tiek savākta katru dienu vai vairākas reizes dienā, var redzēt cenu kustību. Tas palīdz pamanīt kāpumu, kritumu vai stabilu periodu.',
                            'Bez vēstures nav iespējams saprast, vai šodienas cena ir laba, slikta vai vienkārši ierasta.',
                        ],
                    ],
                    [
                        'heading' => 'Kāpēc avots jāglabā kopā ar cenu',
                        'body' => [
                            'Viena un tā pati cena var būt oficiāla stacijas cena, tīkla cena vai lietotāja ziņojums. Šie avoti nav vienādi, tāpēc vēsturē jāsaglabā arī avota tips.',
                        ],
                    ],
                    [
                        'heading' => 'Kā automatizēt importu',
                        'body' => [
                            'Lokāli cenu importu var palaist ar Artisan komandu. Uz servera šo pašu komandu vajag darbināt ar Laravel scheduler vai cron, lai cenas krājas automātiski.',
                            'Svarīgi ir nevis saražot daudz ierakstu, bet regulāri un uzticami vākt tikai tos datus, kurus var pārbaudīt.',
                        ],
                    ],
                ],
                'faq' => [
                    ['question' => 'Vai cenu vēsturi var izveidot uzreiz?', 'answer' => 'Nē. Tā kļūst vērtīga pēc vairākiem importiem dažādos laikos.'],
                    ['question' => 'Vai vēsturē jāglabā arī neprecīzi dati?', 'answer' => 'Nē. Labāk mazāk ierakstu, bet ar uzticamu avotu.'],
                ],
            ],
        ];
    }
}
