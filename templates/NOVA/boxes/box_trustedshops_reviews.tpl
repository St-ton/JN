{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{card
    class="box box-trustedshops-reviews mb-7"
    id="sidebox{$oBox->getID()}"
    titel="{if $oBox->getPosition() !== JTL\Boxes\Position::BOTTOM}{lang key='trustedshopsRating'}{/if}"
}
    <hr class="mt-0 mb-4">
    <div class="sidebox_content text-center">
        {link href=$oBox->getImageURL() target="_blank" rel="noopener"}
            {image src=$oBox->getImagePath() alt="{lang key='trustedshopsRating'}"}
        {/link}
    </div>
    <span class="review-aggregate">
        <span class="rating">
            <span class="average">{$oBox->getStats()->dDurchschnitt|string_format:"%.2f"}</span>
        </span>&nbsp;/&nbsp;<span class="best">{$oBox->getStats()->dMaximum|string_format:"%.2f"}</span>
        &nbsp;{lang key='from'}&nbsp;
        <span class="count">{$oBox->getStats()->nAnzahl}</span>
        {link href=$oBox->getImageURL() title=$cShopName}{lang key='rating'} {lang key='from'} {$cShopName}
        {/link}
    </span>
{/card}
