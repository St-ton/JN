{block name='boxes-box-filter-characteristics'}
    {if $nSeitenTyp === $smarty.const.PAGE_ARTIKELLISTE
        && !($isMobile || $Einstellungen.template.productlist.filter_placement === 'modal')}
        {foreach $oBox->getItems() as $characteristic}
            <div id="sidebox{$oBox->getID()}-{$characteristic->getID()}" class="box box-filter-characteristics d-none d-lg-block">
                {button
                    variant="link"
                    class="btn-filter-box dropdown-toggle"
                    role="button"
                    block=true
                    data=["toggle"=> "collapse", "target"=>"#cllps-box{$oBox->getID()}-{$characteristic->getID()}"]
                }
                    {$img = $characteristic->getImage(\JTL\Media\Image::SIZE_XS)}
                    {if $Einstellungen.navigationsfilter.merkmal_anzeigen_als !== 'T'
                    && $img !== null
                    && $img|strpos:$smarty.const.BILD_KEIN_MERKMALBILD_VORHANDEN === false
                    && $img|strpos:$smarty.const.BILD_KEIN_ARTIKELBILD_VORHANDEN === false}
                        {image fluid=true webp=true lazy=true
                            src=$img
                            srcset="{$characteristic->getImage(\JTL\Media\Image::SIZE_XS)} {$Einstellungen.bilder.bilder_merkmal_mini_breite}w,
                                    {$characteristic->getImage(\JTL\Media\Image::SIZE_SM)} {$Einstellungen.bilder.bilder_merkmal_klein_breite}w,
                                    {$characteristic->getImage(\JTL\Media\Image::SIZE_MD)} {$Einstellungen.bilder.bilder_merkmal_normal_breite}w"
                            sizes="24px"
                            alt=$characteristic->getName() class="img-xs vmiddle"
                        }
                    {/if}
                    {if $Einstellungen.navigationsfilter.merkmal_anzeigen_als !== 'B'}
                        <span class="text-truncate">
                            {$characteristic->cName}
                        </span>
                    {/if}
                {/button}
                {collapse
                    id="cllps-box{$oBox->getID()}-{$characteristic->getID()}"
                    visible=$characteristic->isActive() || $Einstellungen.template.productlist.filter_items_always_visible === 'Y'}
                    {block name='boxes-box-filter-characteristics-characteristics'}
                        {if ($characteristic->getData('cTyp') === 'SELECTBOX') && $characteristic->getOptions()|@count > 0}
                            {block name='boxes-box-filter-characteristics-select'}
                                {dropdown variant="outline-secondary" text="{lang key='selectFilter' section='global'} " toggle-class="btn-block text-left-util"}
                                {block name='boxes-box-filter-characteristics-include-characteristics-dropdown'}
                                    {include file='snippets/filter/characteristic.tpl' Merkmal=$characteristic}
                                {/block}
                                {/dropdown}
                            {/block}
                        {else}
                            {block name='boxes-box-filter-characteristics-link'}
                                {block name='boxes-box-filter-characteristics-include-characteristics-link'}
                                    {include file='snippets/filter/characteristic.tpl' Merkmal=$characteristic}
                                {/block}
                            {/block}
                        {/if}
                    {/block}
                {/collapse}
                {block name='boxes-box-filter-characteristics-hr'}
                    <hr class="my-2">
                {/block}
            </div>
        {/foreach}
    {/if}
{/block}
