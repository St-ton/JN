{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{form method="post" action="{get_static_route id='jtl.php'}" name="Wunschliste"}
    {block name='wishlist'}
    {input type="hidden" name="wla" value="1"}
    {input type="hidden" name="wl" value=$CWunschliste->kWunschliste}

    {if isset($wlsearch)}
        {input type="hidden" name="wlsearch" value="1"}
        {input type="hidden" name="cSuche" value=$wlsearch}
    {/if}
    <div id="edit-widhlist-name">
        {inputgroup}
            {inputgroupaddon prepend=true}
                {inputgrouptext}
                    <strong>{lang key='name' section='global'}</strong>
                {/inputgrouptext}
            {/inputgroupaddon}
            {input id="wishlist-name" type="text" placeholder="name" name="WunschlisteName" value=$CWunschliste->cName autofocus=true}
            {inputgroupaddon append=true}
                {button type="submit"}{lang key='edit' section='global'}{/button}
            {/inputgroupaddon}
        {/inputgroup}
    </div>
    {if !empty($CWunschliste->CWunschlistePos_arr)}
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>{lang key='wishlistProduct' section='login'}</th>
                    <th class="d-none d-sm-block d-md-block">&nbsp;</th>
                    <th>{lang key='wishlistComment' section='login'}</th>
                    <th class="text-center">{lang key='wishlistPosCount' section='login'}</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            {foreach $CWunschliste->CWunschlistePos_arr as $CWunschlistePos}
                <tr>
                    <td class="img-col d-none d-sm-block d-md-block">
                        {link href=$CWunschlistePos->Artikel->cURLFull}
                            {image alt=$CWunschlistePos->Artikel->cName src=$CWunschlistePos->Artikel->cVorschaubild fluid=true}
                        {/link}
                    </td>
                    <td>
                        <div class="text-content">
                            {link href=$CWunschlistePos->Artikel->cURLFull}{$CWunschlistePos->cArtikelName}{/link}
                            {if $CWunschlistePos->Artikel->getOption('nShowOnlyOnSEORequest', 0) === 1}
                                <p>{lang key='productOutOfStock' section='productDetails'}</p>
                            {elseif $CWunschlistePos->Artikel->Preise->fVKNetto == 0 && $Einstellungen.global.global_preis0 === 'N'}
                                <p>{lang key='priceOnApplication' section='global'}</p>
                            {else}
                                {include file='productdetails/price.tpl' Artikel=$CWunschlistePos->Artikel tplscope='wishlist'}
                            {/if}
                            {foreach $CWunschlistePos->CWunschlistePosEigenschaft_arr as $CWunschlistePosEigenschaft}
                                {if $CWunschlistePosEigenschaft->cFreifeldWert}
                                    <p>
                                    <b>{$CWunschlistePosEigenschaft->cEigenschaftName}:</b>
                                    {$CWunschlistePosEigenschaft->cFreifeldWert}{if $CWunschlistePos->CWunschlistePosEigenschaft_arr|@count > 1 && !$CWunschlistePosEigenschaft@last}</p>{/if}
                                {else}
                                    <p>
                                    <b>{$CWunschlistePosEigenschaft->cEigenschaftName}:</b>
                                    {$CWunschlistePosEigenschaft->cEigenschaftWertName}{if $CWunschlistePos->CWunschlistePosEigenschaft_arr|@count > 1 && !$CWunschlistePosEigenschaft@last}</p>{/if}
                                {/if}
                            {/foreach}
                        </div>
                    </td>
                    <td>
                        <div class="text-content">
                            {textarea rows="2" name="Kommentar_{$CWunschlistePos->kWunschlistePos}"}{$CWunschlistePos->cKommentar}{/textarea}
                        </div>
                    </td>
                    {if $CWunschlistePos->Artikel->Preise->fVKNetto == 0 && $Einstellungen.global.global_preis0 === 'N'}
                        <td width="1%"></td>
                        <td class="text-right">
                            {link href="{get_static_route id='jtl.php'}?wl={$CWunschliste->kWunschliste}&wlplo={$CWunschlistePos->kWunschlistePos}{if isset($wlsearch)}&wlsearch=1&cSuche={$wlsearch}{/if}"
                                class="btn btn-secondary"
                                title="{lang key='wishlistremoveItem' section='login'}"
                                data=["toggle" => "tooltip", "placement" => "bottom"]}
                                <span class="fa fa-trash-o"></span>
                            {/link}
                        </td>
                    {else}
                        <td>
                            {input name="Anzahl_{$CWunschlistePos->kWunschlistePos}" class="wunschliste_anzahl" type="text" size="1" value=$CWunschlistePos->fAnzahl|replace_delim}
                            <br />{$CWunschlistePos->Artikel->cEinheit}
                        </td>
                        <td class="text-right">
                            {buttongroup vertical=true}
                                {if $CWunschlistePos->Artikel->bHasKonfig}
                                    {link
                                        class="btn btn-secondary"
                                        href=$CWunschlistePos->Artikel->cURLFull
                                        title="{lang key='product' section='global'} {lang key='configure' section='global'}"
                                        data=["toggle" => "tooltip", "placement" => "bottom"]
                                    }
                                        <span class="fa fa-gears"></span>
                                    {/link}
                                {else}
                                    {link
                                        class="btn btn-primary"
                                        href="{get_static_route id='jtl.php'}?wl={$CWunschliste->kWunschliste}&wlph={$CWunschlistePos->kWunschlistePos}{if isset($wlsearch)}&wlsearch=1&cSuche={$wlsearch}{/if}"
                                        title="{lang key='wishlistaddToCart' section='login'}"
                                        data=["toggle" => "tooltip", "placement" => "bottom"]
                                    }
                                        <span class="fas fa-shopping-cart"></span>
                                    {/link}
                                {/if}
                                {link
                                    class="btn btn-secondary"
                                    href="{get_static_route id='jtl.php'}?wl={$CWunschliste->kWunschliste}&wlplo={$CWunschlistePos->kWunschlistePos}{if isset($wlsearch)}&wlsearch=1&cSuche={$wlsearch}{/if}"
                                    title="{lang key='wishlistremoveItem' section='login'}"
                                    data=["toggle" => "tooltip", "placement" => "bottom"]
                                }
                                    <span class="fa fa-trash-alt"></span>
                                {/link}
                            {/buttongroup}
                        </td>
                    {/if}
                </tr>
            {/foreach}
            </tbody>
        </table>
        <hr>
        {row}
            {col cols=6 md=8}
                {block name='wishlist-body'}
                    {if $CWunschliste->nOeffentlich == 1}
                        {inputgroup}
                            {input type="text" name="wishlist-url" readonly=true value="{$ShopURL}/index.php?wlid={$CWunschliste->cURLID}"}
                            {buttongroup}
                                {if $Einstellungen.global.global_wunschliste_freunde_aktiv === 'Y'}
                                    {button type="submit"
                                        name="wlvm"
                                        value="1"
                                        title="{lang key='wishlistViaEmail' section='login'}"
                                        data=["toggle" => "tooltip", "placement" => "bottom"]}
                                       <i class="fa fa-envelope"></i>
                                   {/button}
                                {/if}
                                {button type="submit"
                                    name="wlAction"
                                    value="setPrivate"
                                    title="{lang key='wishlistSetPrivate' section='login'}"
                                    data=["toggle" => "tooltip", "placement" => "bottom"]}
                                    <i class="fa fa-eye-slash"></i> <span class="d-none d-md-inline-block">{lang key='wishlistSetPrivate' section='login'}</span>
                                {/button}
                            {/buttongroup}
                        {/inputgroup}
                    {else}
                        {button type="submit" name="wlAction" value="setPublic"}
                            <i class="fa fa-eye"></i> <span class="d-none d-md-inline-block">{lang key='wishlistSetPublic' section='login'}</span>
                        {/button}
                    {/if}
                {/block}
            {/col}
            {col cols=6 md=4}
                {buttongroup class="float-right"}
                    {link
                        class="btn btn-primary"
                        href="{get_static_route id='jtl.php'}?wl={$CWunschliste->kWunschliste}&wlpah=1{if isset($wlsearch)}&wlsearch=1&cSuche={$wlsearch}{/if}"
                        type="submit"
                        title="{lang key='wishlistAddAllToCart' section='login'}"
                        data=["toggle" => "tooltip", "placement" => "bottom"]
                    }
                        <i class="fas fa-shopping-cart"></i>
                    {/link}
                    {button type="submit" title="{lang key='wishlistUpdate' section='login'}" data=["toggle" => "tooltip", "placement" => "bottom"]}
                        <i class="fa fa-sync"></i>
                    {/button}
                    {link
                        class="btn btn-secondary"
                        href="{get_static_route id='jtl.php'}?wl={$CWunschliste->kWunschliste}&wldl=1"
                        type="submit"
                        title="{lang key='wishlistDelAll' section='login'}"
                        data=["toggle" => "tooltip", "placement" => "bottom"]
                    }
                        <i class="fa fa-trash-alt"></i>
                    {/link}
                {/buttongroup}
            {/col}
        {/row}
    {else}
        {alert variant="info"}{lang key="noDataAvailable" section="global"}{/alert}
    {/if}

    <script>
        $(function() {
            $('input[name="wishlist-url"]').on('focus', function() {
                $(this).select();
            });

        });
    </script>

    {/block}
{/form}
