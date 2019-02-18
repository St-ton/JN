{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{form method="post" action="{if !empty($Artikel->cURLFull)}{$Artikel->cURLFull}{else}index.php{/if}"}
    {if $Einstellungen.artikeldetails.tagging_freischaltung !== 'N'}
        {input type="hidden" name="a" value=$Artikel->kArtikel}
        {input type="hidden" name="produktTag" value="1"}
        {if !empty($Artikel->kVariKindArtikel)}
            {input type="hidden" name="variKindArtikel" value=$Artikel->kVariKindArtikel}
        {/if}
        {if ($Einstellungen.artikeldetails.tagging_freischaltung === 'Y' && !empty($smarty.session.Kunde->kKunde)) || $Einstellungen.artikeldetails.tagging_freischaltung === 'O'}
            {row class="mb-3"}
                {col md="{if $ProduktTagging|@count > 0}6{else}12{/if}"}
                    <label class="sr-only" for="add-tag">{lang key='addTag' section='productDetails'}</label>
                    {inputgroup}
                        {input type="text" id="add-tag" name="tag" placeholder="{lang key='addTag' section='productDetails'}"}
                        {inputgroupaddon}
                            {button type="submit" name="submit" value="1" variant="primary" class="w-auto"}
                                {lang key='addYourTag' section='productDetails'}
                            {/button}
                        {/inputgroupaddon}
                    {/inputgroup}
                {/col}
            {/row}
        {else}
            <p>{lang key='tagloginnow' section='productDetails'}</p>
            {button type="submit" name="einloggen" value="1"}
                {lang key='taglogin' section='productDetails'}
            {/button}
        {/if}
    {/if}


    {if $ProduktTagging|@count > 0}
        <div class="mb-3">
            <p>
                {lang key='productTagsDesc' section='productDetails'}
            </p>
            {foreach $ProduktTagging as $produktTagging}
                {link href=$produktTagging->cURLFull title=$produktTagging->cName class="badge badge-light"}
                    {$produktTagging->cName}
                {/link}
            {/foreach}
        </div>
    {/if}
{/form}
