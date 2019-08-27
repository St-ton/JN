{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='productlist-layout-options'}
    {if isset($oErweiterteDarstellung->nDarstellung)
    && $Einstellungen.artikeluebersicht.artikeluebersicht_erw_darstellung === 'Y'
    && empty($AktuelleKategorie->categoryFunctionAttributes['darstellung'])
    && $navid === 'header'}
        {buttongroup class="ml-2"}
            {link href=$oErweiterteDarstellung->cURL_arr[$smarty.const.ERWDARSTELLUNG_ANSICHT_LISTE]
                id="ed_list"
                class="btn btn-light btn-option ed list{if $oErweiterteDarstellung->nDarstellung === $smarty.const.ERWDARSTELLUNG_ANSICHT_LISTE} active{/if}"
                role="button"
                title="{lang key='list' section='productOverview'}"
            }
                <span class="fa fa-th-list d-none d-md-inline-flex"></span><span class="fa fa-square d-inline-flex d-md-none"></span>
            {/link}
            {link href=$oErweiterteDarstellung->cURL_arr[$smarty.const.ERWDARSTELLUNG_ANSICHT_GALERIE]
                id="ed_gallery"
                class="btn btn-light btn-option ed gallery{if $oErweiterteDarstellung->nDarstellung === $smarty.const.ERWDARSTELLUNG_ANSICHT_GALERIE} active{/if}"
                role="button"
                title="{lang key='gallery' section='productOverview'}"
            }
                <span class="fa fa-th-large"></span>
            {/link}
        {/buttongroup}
    {/if}
{/block}
