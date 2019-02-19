{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{card
    class="box box-trustedshops-seal mb-7"
    id="sidebox{$oBox->getID()}"
    title="{if $oBox->getPosition() !== JTL\Boxes\Position::BOTTOM}{lang key='safety'}{/if}"
}
    <hr class="mt-0 mb-4">
    <div class="box-body text-center">
        <p>
            {link href="{$oBox->getLogoURL()}"}
                {image src="{$oBox->getImageURL()}" alt="{lang key='ts_signtitle'}"}
            {/link}
        </p>
        <small class="description">
            {link title="{lang key='ts_info_classic_title'} {$cShopName}" href="{$oBox->getLogoSealURL()}"}
                {$cShopName} {lang key='ts_classic_text'}
            {/link}
        </small>
    </div>
{/card}
