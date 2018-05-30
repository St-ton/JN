{if $oBox->show()}
    {assign var=from value=$oBox->getProducts()}
    {assign var=nAnzahl value=$oBox->getItemCount()}
    <section class="panel panel-default box box-compare" id="sidebox{$oBox->getID()}">
        <div class="panel-heading">
            <div class="panel-title"><i class="fa fa-tasks"></i> {lang key='compare'}</div>
        </div>{* /panel-heading *}
        <div class="box-body panel-body">
            <ul class="list-unstyled">
                {foreach name=vergleich from=$from item=oArtikel}
                    {if $smarty.foreach.vergleich.iteration <= $nAnzahl}
                        <li data-id="{$oArtikel->kArtikel}">
                            <a href="{$oArtikel->cURLDEL}" class="remove pull-right" data-name="Vergleichsliste.remove" data-toggle="product-actions" data-value='{ldelim}"a":{$oArtikel->kArtikel}{rdelim}'><span class="fa fa-trash-o"></span></a>
                            <a href="{$oArtikel->cURLFull}">
                                <img src="{$oArtikel->Bilder[0]->cURLMini}" alt="{$oArtikel->cName|strip_tags|truncate:60|escape:"html"}" class="img-xs" />
                                {$oArtikel->cName|truncate:25:"..."}
                            </a>
                        </li>
                    {/if}
                {/foreach}
            </ul>
            {if count($from) > 1}
                <hr>
                <a class="btn btn-default btn-sm btn-block{if $Einstellungen.vergleichsliste.vergleichsliste_target === 'popup'} popup{/if}" href="{get_static_route id='vergleichsliste.php'}"{if $Einstellungen.vergleichsliste.vergleichsliste_target === 'blank'} target="_blank"{/if}>{lang key="gotToCompare" section="global"}</a>
            {/if}
        </div>
    </section>
{else}
    <section class="hidden box-compare" id="sidebox{$oBox->getID()}"></section>
{/if}
