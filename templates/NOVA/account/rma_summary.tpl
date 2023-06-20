{block name='account-rma-summary'}
    {assign var=rmaID value=$rmaID|default:0}
    {foreach JTL\RMA\RMAService::getItems($rmaID) as $oPosition}
        {row}
            {col cols=3 lg=2 class="rma-items-item-image-wrapper"}
                {if !empty($oPosition->Artikel->cVorschaubildURL)}
                    {link href=$oPosition->Artikel->cURLFull title=$oPosition->cName|trans|escape:'html'}
                        {include file='snippets/image.tpl' item=$oPosition->Artikel square=false srcSize='sm'}
                    {/link}
                {/if}
            {/col}
        {/row}
        {col cols=9 lg=10}
            Lorem Ipsum ...
        {/col}
    {/foreach}
{/block}
