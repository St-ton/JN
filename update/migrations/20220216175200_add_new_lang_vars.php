<?php declare(strict_types=1);

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20220216175200
 */
class Migration_20220216175200 extends Migration implements IMigration
{
    protected $author      = 'fm';
    protected $description = 'Add more lang vars';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->setLocalization(
            'ger',
            'messages',
            'searchQueryMinLength',
            'Der Suchbegriff muss mindestens aus %d Zeichen bestehen. Ihr Suchbegriff lautete: %s'
        );
        $this->setLocalization(
            'eng',
            'messages',
            'searchQueryMinLength',
            'The search term must at least consist of %d characters. Your search term was %s'
        );
        $this->setLocalization(
            'ger',
            'messages',
            'minCharLen',
            'Mindestens %d Zeichen!'
        );
        $this->setLocalization(
            'eng',
            'messages',
            'minCharLen',
            '%d characters minimum!'
        );
        $this->setLocalization(
            'ger',
            'checkout',
            'cartSumLabel',
            'Warenkorb (%s)'
        );
        $this->setLocalization(
            'eng',
            'checkout',
            'cartSumLabel',
            'Basket (%s)'
        );
        $this->setLocalization(
            'ger',
            'checkout',
            'yourbasketcontainsItemsSingular',
            'Ihr Warenkorb enthält %s Artikel'
        );
        $this->setLocalization(
            'eng',
            'checkout',
            'yourbasketcontainsItemsSingular',
            'Your basket contains %d item'
        );
        $this->setLocalization(
            'ger',
            'checkout',
            'yourbasketcontainsItemsPlural',
            'Ihr Warenkorb enthält %s Artikel'
        );
        $this->setLocalization(
            'eng',
            'checkout',
            'yourbasketcontainsItemsPlural',
            'Your basket contains %d items'
        );
        $this->setLocalization(
            'ger',
            'checkout',
            'yourbasketcontainsPositionsSingular',
            'Ihr Warenkorb enthält %s Position'
        );
        $this->setLocalization(
            'eng',
            'checkout',
            'yourbasketcontainsPositionsSingular',
            'Your basket contains %d position'
        );
        $this->setLocalization(
            'ger',
            'checkout',
            'yourbasketcontainsPositionsPlural',
            'Ihr Warenkorb enthält %s Positionen'
        );
        $this->setLocalization(
            'eng',
            'checkout',
            'yourbasketcontainsPositionsPlural',
            'Your basket contains %d positions'
        );
        $this->setLocalization(
            'ger',
            'messages',
            'productMinorderQty',
            'Artikel "%s" hat eine Mindestbestellmenge (%s). Ihre gewünschte Menge betrug %s.'
        );
        $this->setLocalization(
            'eng',
            'messages',
            'productMinorderQty',
            'Item "%s" has a minimum order quantity (%s). Your requested quantity is %s.'
        );

        $this->setLocalization(
            'ger',
            'checkout',
            'orderPositionSingularItemsSingular',
            'Ihre aktuelle Bestellung enthält %s Position mit %s Artikel'
        );
        $this->setLocalization(
            'eng',
            'checkout',
            'orderPositionSingularItemsSingular',
            'Your current order contains %s position with %s item'
        );
        $this->setLocalization(
            'ger',
            'checkout',
            'orderPositionSingularItemsPlural',
            'Ihre aktuelle Bestellung enthält %s Position mit %s Artikeln'
        );
        $this->setLocalization(
            'eng',
            'checkout',
            'orderPositionSingularItemsPlural',
            'Your current order contains %s position with %s items'
        );
        $this->setLocalization(
            'ger',
            'checkout',
            'orderPositionPluralItemsSingular',
            'Ihre aktuelle Bestellung enthält %s Positionen mit %s Artikel'
        );
        $this->setLocalization(
            'eng',
            'checkout',
            'orderPositionPluralItemsSingular',
            'Your current order contains %s positions with %s item'
        );
        $this->setLocalization(
            'ger',
            'checkout',
            'orderPositionPluralItemsPlural',
            'Ihre aktuelle Bestellung enthält %s Positionen mit %s Artikeln'
        );
        $this->setLocalization(
            'eng',
            'checkout',
            'orderPositionPluralItemsPlural',
            'Your current order contains %s positions with %s items'
        );
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->removeLocalization('searchQueryMinLength', 'messages');
        $this->removeLocalization('minCharLen', 'messages');
        $this->removeLocalization('cartSumLabel', 'checkout');
        $this->removeLocalization('yourbasketcontainsItemsSingular', 'checkout');
        $this->removeLocalization('yourbasketcontainsItemsPlural', 'checkout');
        $this->removeLocalization('yourbasketcontainsPositionsSingular', 'checkout');
        $this->removeLocalization('yourbasketcontainsPositionsPlural', 'checkout');
        $this->removeLocalization('productMinorderQty', 'messages');
        $this->removeLocalization('orderPositionSingularItemsSingular', 'checkout');
        $this->removeLocalization('orderPositionSingularItemsPlural', 'checkout');
        $this->removeLocalization('orderPositionPluralItemsSingular', 'checkout');
        $this->removeLocalization('orderPositionPluralItemsPlural', 'checkout');
    }
}
