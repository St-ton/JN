{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
<section class="panel panel-default box box-priceradar" id="sidebox{$oBox->getID()}">
    <div class="panel-heading">
        <div class="panel-title">{lang key='priceRadar'}</div>
    </div>
    <div class="box-body panel-body text-center">
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
    </div>
</section>
