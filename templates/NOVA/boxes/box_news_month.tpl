{block name='boxes-box-news-month'}
    {card class="box box-monthlynews box-normal" id="sidebox{$oBox->getID()}"}
        {block name='boxes-box-news-month-content'}
            {block name='boxes-box-news-month-toggle-title'}
                {link id="crd-hdr-{$oBox->getID()}"
                    href="#crd-cllps-{$oBox->getID()}"
                    data=["toggle"=>"collapse"]
                    role="button"
                    aria=["expanded"=>"false","controls"=>"crd-cllps-{$oBox->getID()}"]
                    class="box-normal-link dropdown-toggle"}
                    {lang key='newsBoxMonthOverview'}
                {/link}
            {/block}
            {block name='boxes-box-news-month-title'}
                <div class="productlist-filter-headline d-none d-md-flex">
                    {lang key='newsBoxMonthOverview'}
                </div>
            {/block}
            {block name='boxes-box-news-month-collapse'}
                {collapse
                    class="d-md-block"
                    visible=false
                    id="crd-cllps-{$oBox->getID()}"
                    aria=["labelledby"=>"crd-hdr-{$oBox->getID()}"]}
                    {nav vertical=true class="box-nav-item"}
                        {foreach $oBox->getItems() as $newsMonth}
                            {if $newsMonth@index === 10}{break}{/if}
                            {block name='boxes-box-news-month-news-link'}
                                {navitem href=$newsMonth->cURL  title=$newsMonth->cName router-class="box-link-wrapper"}
                                    <i class="far fa-newspaper snippets-filter-item-icon-right"></i>
                                    {$newsMonth->cName}
                                    {badge variant="outline-secondary" class="ml-auto-util"}{$newsMonth->nAnzahl}{/badge}
                                {/navitem}
                            {/block}
                        {/foreach}
                    {/nav}
                {/collapse}
            {/block}
        {/block}
    {/card}
    {block name='boxes-box-news-month-hr-end'}
        <hr class="box-normal-hr">
    {/block}
{/block}
