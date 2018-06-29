{if $oBox->show()}
    <section class="panel panel-default box box-trustedshops-reviews" id="sidebox{$oBox->getID()}">
        {if $oBox->getPosition() !== \Boxes\BoxPosition::BOTTOM}
            <div class="panel-heading">
                <div class="panel-title">{lang key='trustedshopsRating'}</div>
            </div>
        {/if}
        <div class="sidebox_content text-center">
            <a href="{$oBox->getImageURL()}" target="_blank" rel="noopener">
                <img src="{$oBox->getImagePath()}" alt="Trusted-Shops-Kundenbewertung" />
            </a>
        </div>
        <span class="review-aggregate">
            <span class="rating">
                <span class="average">{$oBox->getStats()->dDurchschnitt|string_format:"%.2f"}</span>
            </span>&nbsp;/&nbsp;<span class="best">{$oBox->getStats()->dMaximum|string_format:"%.2f"}</span>
            &nbsp;von&nbsp;
            <span class="count">{$oBox->getStats()->nAnzahl}</span>
            <a href="{$oBox->getImageURL()}" title="Bewertungen von {$cShopName}">Bewertungen
                von {$cShopName}
            </a>
        </span>
    </section>
{/if}
