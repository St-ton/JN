<?php

/*
 * This file is part of ShopCli.
 *
 * (c) JTL-Software GmbH <info@jtl-software.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace ShopCli\Faker\de_DE;

class Commerce extends \ShopCli\Faker\Commerce
{
    protected static $department = array(
        'Antiquitäten & Kunst', 'Auto & Motorrad: Fahrzeuge', 'Auto & Motorrad: Teile', 'Baby', 'Beauty & Gesundheit', 'Briefmarken',
        'Business & Industrie', 'Bücher', 'Büro & Schreibwaren', 'Computer, Tablets & Netzwerk', 'Feinschmecker', 'Filme & DVDs',
        'Foto & Camcorder', 'Garten & Terrasse', 'Handys & Kommunikation', 'Haushaltsgeräte', 'Haustierbedarf', 'Heimwerker', 'Immobilien',
        'Kleidung & Accessoires', 'Modellbau', 'Musik', 'Musikinstrumente', 'Möbel & Wohnen', 'Münzen', 'PC- & Videospiele', 'Reisen',
        'Sammeln & Seltenes', 'Spielzeug', 'Sport', 'Tickets', 'TV, Video & Audio', 'Uhren & Schmuck', 'Verschiedenes',
    );

    protected static $adjective = array(
        'Klein', 'Ergonomisch', 'Rustikal', 'Intelligent', 'Herrlich', 'Unglaublich', 'Fantastisch',
        'Praktisch', 'Geschmeidig', 'Enorm', 'Mittelmäßig', 'Leicht', 'Aerodynamisch', 'Langlebig',
    );

    protected static $material = array(
        'Stahl', 'Beton', 'Kunststoff', 'Baumwolle', 'Granit', 'Gummi', 'Leder', 'Seide', 'Wolle',
        'Leinen', 'Marmor', 'Eisen', 'Bronze', 'Kupfer', 'Aluminium', 'Papier',
    );

    protected static $product = array(
        'Stuhl', 'Auto', 'Computer', 'Handschuhe', 'Hose', 'Hemd', 'Tabelle', 'Schuhe', 'Hut', 'Platte', 'Messer', 'Flasche', 'Mantel',
        'Lampe', 'Tastatur', 'Tasche', 'Bank', 'Uhr', 'Portemonnaie',
    );

    // maskulin = 0, feminin = 1, neutral = 2
    protected static $article = array(0, 2, 0, 0, 1, 2, 1, 1, 0, 1, 2, 1, 0, 1, 1, 1, 1, 1, 2);

    protected static $suffix = array(0 => 'er', 1 => 'e', 2 => 'es');

    public function productName()
    {
        $product = static::randomElement(static::$product);
        $suffix = $this->adjectiveSuffix($product) ?: '';

        return static::randomElement(static::$adjective).$suffix.' '.static::randomElement(static::$material).'-'.$product;
    }

    public function adjectiveSuffix($name)
    {
        $key = array_search($name, static::$product, true);
        if (!array_key_exists($key, static::$article)) {
            return null;
        }
        $article = static::$article[$key];

        return static::$suffix[$article];
    }
}
