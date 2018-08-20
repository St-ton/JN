{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{if $oBox->show()}
    <section class="panel panel-default box box-priceradar" id="sidebox{$oBox->getID()}">
        <div class="panel-heading">
            <div class="panel-title">{lang key='priceRadar'}</div>
        </div>
        <div class="box-body panel-body text-center">
            {if $BoxenEinstellungen.boxen.boxen_preisradar_scrollbar > 0}
                <marquee behavior="scroll" direction="{if $BoxenEinstellungen.boxen.boxen_preisradar_scrollbar == 1}down{else}up{/if}" onmouseover="this.stop()" onmouseout="this.start()" scrollamount="2" scrolldelay="70">
            {/if}
            <ul class="list-unstyled">
                {foreach $oBox->getProducts() as $oArtikel}
                    <li>
                        <div class="text-center clearall">
                            <p><a href="{$oArtikel->cURLFull}"><img src="{$oArtikel->cVorschaubild}" alt="{$oArtikel->cName|strip_tags|escape:'quotes'|truncate:60}" class="image" /></a></p>
                            <p><a href="{$oArtikel->cURLFull}">{$oArtikel->cName}</a></p>

                            {lang key='oldPrice'}: <del>{$oArtikel->oPreisradar->fOldVKLocalized[$NettoPreise]}</del>
                            {include file='productdetails/price.tpl' Artikel=$oArtikel tplscope='box'}
                        </div>
                    </li>
                {/foreach}
            </ul>
            {if $BoxenEinstellungen.boxen.boxen_preisradar_scrollbar > 0}
                </marquee>
            {/if}
        </div>
    </section>
{/if}
