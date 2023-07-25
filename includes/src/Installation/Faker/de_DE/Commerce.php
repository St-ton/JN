<?php declare(strict_types=1);

namespace JTL\Installation\Faker\de_DE;

/**
 * Class Commerce
 * @package JTL\Installation\Faker\de_DE
 */
class Commerce extends \JTL\Installation\Faker\Commerce
{
    /**
     * @var string[]
     */
    protected static array $department = [
        'Antiquitäten & Kunst', 'Auto & Motorrad: Fahrzeuge', 'Auto & Motorrad: Teile', 'Baby',
        'Beauty & Gesundheit', 'Briefmarken', 'Business & Industrie', 'Bücher', 'Büro & Schreibwaren',
        'Computer, Tablets & Netzwerk', 'Feinschmecker', 'Filme & DVDs', 'Foto & Camcorder',
        'Garten & Terrasse', 'Handys & Kommunikation', 'Haushaltsgeräte', 'Haustierbedarf', 'Heimwerker',
        'Immobilien', 'Kleidung & Accessoires', 'Modellbau', 'Musik', 'Musikinstrumente', 'Möbel & Wohnen',
        'Münzen', 'PC- & Videospiele', 'Reisen', 'Sammeln & Seltenes', 'Spielzeug', 'Sport', 'Tickets',
        'TV, Video & Audio', 'Uhren & Schmuck', 'Romane', 'Software', 'Hardware', 'Damenschmuck',
        'Spirituosen', 'Heimkino', 'Kamera & Foto', 'Kopfhörer', 'Drogerieartikel', 'Navigation',
        'Ladegeräte', 'Festnetztelefone', 'Wearables', 'MP3-Player', 'CD-Player', 'Verschiedenes',
        'Schuhe', 'Zubehör', 'Pullover', 'Smart Home', 'Innenleuchten', 'Außenleuchten', 'Terrasse', 'Camping',
        'Ventilatoren', 'Kinderzimmer', 'Rucksäcke', 'Sandalen', 'Sneaker', 'Ausrüstung', 'Yoga', 'Körperpflege',
        'Make-Up', 'Düfte', 'Sonnenschutz', 'Zahnpflege', 'Snowboards', 'Fußball', 'Basketball', 'Handball',
        'Badminton', 'Gold', 'Sporttaschen', 'Unterwäsche', 'Radsport', 'Nahrungsergänzungsmittel', 'Hundefutter'
    ];

    /**
     * @var string[]
     */
    protected static array $adjective = [
        'Klein', 'Ergonomisch', 'Rustikal', 'Intelligent', 'Herrlich', 'Unglaublich', 'Fantastisch',
        'Praktisch', 'Geschmeidig', 'Enorm', 'Mittelmäßig', 'Leicht', 'Aerodynamisch', 'Langlebig',
        'Schick', 'Wertig', 'Toll', 'Günstig', 'Exorbitant', 'Hervorragend', 'Gut', 'Beliebt', 'Weich', 'Hart',
        'Imposant', 'Gigantisch', 'Schmierig', 'Klebrig', 'Unterhaltsam', 'Besorgniserregend', 'Adrett', 'Kühn',
        'Lebhaft', 'Eigenwillig', 'Luftig', 'Reichhaltig', 'Elegant', 'Überteuert',
    ];

    /**
     * @var string[]
     */
    protected static array $material = [
        'Stahl', 'Beton', 'Kunststoff', 'Baumwolle', 'Granit', 'Gummi', 'Leder', 'Seide',
        'Wolle', 'Leinen', 'Marmor', 'Eisen', 'Bronze', 'Kupfer', 'Aluminium', 'Papier',
        'Plutonium', 'Plastik', 'Wasser', 'Polyamid', 'Viscose', 'Elastan', 'Glas', 'Samt'
    ];

    /**
     * @var string[]
     */
    protected static array $product = [
        'Stuhl', 'Auto', 'Computer', 'Handschuhe', 'Hose', 'Hemd', 'Bikini', 'Schuhe', 'Hut',
        'Platte', 'Messer', 'Flasche', 'Mantel', 'Briefbeschwerer', 'Unterhose', 'Stuhl', 'Bett', 'Handy',
        'Lampe', 'Tastatur', 'Tasche', 'Bank', 'Uhr', 'Portemonnaie', 'Zange', 'Kanne', 'Rutsche',
        'Socke', 'Brille', 'Schirm', 'Laptop', 'Telefon', 'Brett', 'Glas', 'Teller', 'Gabel',
        'BH', 'Ring', 'Kissen', 'Decke', 'Schal', 'Mütze', 'Handy', 'Badehose', 'Tanktop',
        'Lappen', 'Ventilator', 'Luftreiniger', 'Briefkasten', 'Akku', 'Klositz', 'Rasierer', 'Pflaster', 'Mixer'
    ];

    /**
     * maskulin = 0, feminin = 1, neutral = 2
     *
     * @var int[]
     */
    protected static array $article = [
        0, 2, 0, 0, 1, 2, 0, 1, 0,
        1, 2, 1, 0, 0, 1, 1, 2, 2,
        1, 1, 1, 1, 1, 2, 1, 1, 1,
        1, 1, 0, 0, 2, 2, 2, 0, 1,
        0, 0, 2, 1, 0, 1, 2, 1, 2,
        0, 0, 0, 0, 0, 0, 0, 2, 0
    ];

    /**
     * @var array
     */
    protected static array $suffix = [0 => 'er', 1 => 'e', 2 => 'es'];

    /**
     * @return string
     */
    public function productName(): string
    {
        $product = static::randomElement(static::$product);
        $suffix  = $this->adjectiveSuffix($product) ?: '';

        return static::randomElement(static::$adjective)
        . $suffix . ' ' . static::randomElement(static::$material) . '-' . $product;
    }

    /**
     * @param string $name
     * @return mixed|null
     */
    public function adjectiveSuffix(string $name)
    {
        $key = \array_search($name, static::$product, true);
        if (!\array_key_exists($key, static::$article)) {
            return null;
        }
        $article = static::$article[$key];

        return static::$suffix[$article];
    }
}
