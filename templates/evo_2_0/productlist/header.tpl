{extends file="{$parent_template_path}/productlist/header.tpl"}

{block name='productlist-header'}
    {if $oNavigationsinfo->hasData()}
        <div class="row desc clearfix">
            {if $oNavigationsinfo->getImageURL() !== 'gfx/keinBild.gif' && $oNavigationsinfo->getImageURL() !== 'gfx/keinBild_kl.gif'}
                <div class="img col-xs-12 bottom17">
                    <img class="img-responsive" src="{$imageBaseURL}/{$oNavigationsinfo->getImageURL()}" alt="{if $oNavigationsinfo->getCategory() !== null}{$oNavigationsinfo->getCategory()->cBeschreibung|strip_tags|truncate:40|escape:'html'}{elseif $oNavigationsinfo->getManufacturer() !== null}{$oNavigationsinfo->getManufacturer()->cBeschreibung|strip_tags|truncate:40|escape:'html'}{/if}" />
                </div>
            {/if}
            <div class="title col-xs-12">{if $oNavigationsinfo->getName()}<h1>{$oNavigationsinfo->getName()}</h1>{/if}</div>
            {if $Einstellungen.navigationsfilter.kategorie_beschreibung_anzeigen === 'Y'
            && $oNavigationsinfo->getCategory() !== null
            && $oNavigationsinfo->getCategory()->cBeschreibung|strlen > 0}
                <div class="item_desc custom_content col-xs-12">{$oNavigationsinfo->getCategory()->cBeschreibung}</div>
            {/if}
            {if $Einstellungen.navigationsfilter.hersteller_beschreibung_anzeigen === 'Y'
            && $oNavigationsinfo->getManufacturer() !== null
            && $oNavigationsinfo->getManufacturer()->cBeschreibung|strlen > 0}
                <div class="item_desc custom_content col-xs-12">{$oNavigationsinfo->getManufacturer()->cBeschreibung}</div>
            {/if}
            {if $Einstellungen.navigationsfilter.merkmalwert_beschreibung_anzeigen === 'Y'
            && $oNavigationsinfo->getAttributeValue() !== null
            && $oNavigationsinfo->getAttributeValue()->cBeschreibung|strlen > 0}
                <div class="item_desc custom_content col-xs-12">{$oNavigationsinfo->getAttributeValue()->cBeschreibung}</div>
            {/if}
        </div>
    {/if}
{/block}

{block name='productlist-pageinfo'}
    {include file='./pagination.tpl'}
{/block}