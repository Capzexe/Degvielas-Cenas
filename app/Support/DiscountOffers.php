<?php

namespace App\Support;

use Illuminate\Support\Collection;

class DiscountOffers
{
    /**
     * @return Collection<int, array<string, string>>
     */
    public static function all(): Collection
    {
        return collect([
            [
                'brand' => 'Circle K',
                'name' => 'Extra nedēļas nogales kupons',
                'discount' => '-10 c/l',
                'details' => 'Piektdienā Circle K lietotnē aktivizē kuponu; der līdz svētdienai vienam pirkumam.',
                'applies_to' => 'Degviela',
                'source_url' => 'https://www.circlek.lv/degvielas-bonuss-lietotne',
            ],
            [
                'brand' => 'Virši',
                'name' => 'Klienta karte darba dienās',
                'discount' => '-3 c/l',
                'details' => 'Pirmdiena-ceturtdiena ar Viršu klienta karti vai lietotni.',
                'applies_to' => 'Degviela',
                'source_url' => 'https://www.virsi.lv/lv/privatpersonam/degviela/kartes-privatpersonam',
            ],
            [
                'brand' => 'Virši',
                'name' => 'Klienta karte brīvdienās',
                'discount' => '-5 c/l',
                'details' => 'Piektdiena-svētdiena ar Viršu klienta karti vai lietotni.',
                'applies_to' => 'Degviela',
                'source_url' => 'https://www.virsi.lv/lv/privatpersonam/degviela/kartes-privatpersonam',
            ],
            [
                'brand' => 'Neste',
                'name' => 'Neste Privātkarte',
                'discount' => '-3 c/l',
                'details' => 'Pastāvīga atlaide par degvielu ar Neste Privātkarti.',
                'applies_to' => 'Degviela',
                'source_url' => 'https://www.neste.lv/privatpersonam/klientu-ieguvumi/privatkarte',
            ],
            [
                'brand' => 'Neste',
                'name' => 'Goda ģimene',
                'discount' => '-4 c/l',
                'details' => 'Pirms apmaksas autorizē Goda ģimene karti Neste terminālī.',
                'applies_to' => 'Degviela',
                'source_url' => 'https://www.neste.lv/privatpersonam/klientu-ieguvumi/ipasie-piedavajumi/kvalitativa-degviela-kvalitativa-cena',
            ],
            [
                'brand' => 'Neste',
                'name' => 'Privātkarte + Mans Rimi',
                'discount' => '5.5 c/l ieguvums',
                'details' => 'Neste kartes 3 c/l atlaide + 2.5 c/l Mans Rimi naudas uzkrājums.',
                'applies_to' => 'Degviela',
                'source_url' => 'https://www.neste.lv/privatpersonam/klientu-ieguvumi/privatkarte',
            ],
            [
                'brand' => 'Neste',
                'name' => 'Privātkarte + S! karte',
                'discount' => '6 c/l ieguvums',
                'details' => 'Neste kartes 3 c/l atlaide + Siguldas novada ID kartes papildu ieguvums.',
                'applies_to' => 'Degviela',
                'source_url' => 'https://www.neste.lv/privatpersonam/klientu-ieguvumi/privatkarte',
            ],
            [
                'brand' => 'Neste',
                'name' => 'Mobilās lietotnes pirmais piedāvājums',
                'discount' => '-7 c/l',
                'details' => 'Pirmajiem 5 pirkumiem, ja akcija ir pieejama lietotnē un nosacījumi izpildīti.',
                'applies_to' => 'Degviela',
                'source_url' => 'https://www.neste.lv/lv/7cl',
            ],
            [
                'brand' => 'Viada',
                'name' => 'VIADA plus līdz 70 l/mēn.',
                'discount' => '-2.5 c/l',
                'details' => 'Minimālā fiksētā atlaide ar VIADA plus karti.',
                'applies_to' => 'Degviela',
                'source_url' => 'https://www.viada.lv/lojalitate/fiziskam-personam/viada-plus-lojalitates-karte/',
            ],
            [
                'brand' => 'Viada',
                'name' => 'VIADA plus virs 70 l/mēn.',
                'discount' => '-3.5 c/l',
                'details' => 'Fiksētā atlaide, ja iepriekšējā mēnesī iegādāts vairāk nekā 70 l degvielas.',
                'applies_to' => 'Degviela',
                'source_url' => 'https://www.viada.lv/lojalitate/fiziskam-personam/viada-plus-lojalitates-karte/',
            ],
            [
                'brand' => 'Viada',
                'name' => 'Goda ģimene',
                'discount' => '-5 c/l',
                'details' => 'Atlaide visa veida degvielai ar Goda ģimene karti.',
                'applies_to' => 'Degviela',
                'source_url' => 'https://www.viada.lv/goda-gimene/',
            ],
            [
                'brand' => 'ASTARTE',
                'name' => 'Klienta karte brīvdienās',
                'discount' => '-7 c/l',
                'details' => 'Piektdienās, sestdienās un svētdienās ar ASTARTE Klienta karti.',
                'applies_to' => 'Degviela un autogāze',
                'source_url' => 'https://astarte.lv/akcijas/7-centi-l-atlaide-degvielai/',
            ],
        ]);
    }
}
