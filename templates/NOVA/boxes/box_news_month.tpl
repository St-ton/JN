{block name='boxes-box-news-month'}
    {card class="box box-monthlynews mb-md-4" id="sidebox{$oBox->getID()}"}
        {block name='boxes-box-news-month-content'}
            {block name='boxes-box-news-month-toggle-title'}
                {link id="crd-hdr-{$oBox->getID()}"
                    href="#crd-cllps-{$oBox->getID()}"
                    data=["toggle"=>"collapse"]
                    role="button"
                    aria=["expanded"=>"false","controls"=>"crd-cllps-{$oBox->getID()}"]
                    class="text-decoration-none font-weight-bold-util d-md-none dropdown-toggle"}
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
                    {nav vertical=true class="mt-2 mt-md-0"}
                        {foreach $oBox->getItems() as $newsMonth}
                            {if $newsMonth@index === 10}{break}{/if}
                            {block name='boxes-box-news-month-news-link'}
                                {navitem href=$newsMonth->cURL  title=$newsMonth->cName router-class="align-items-center d-flex"}
                                    <i class="far fa-newspaper mr-2"></i>
                                    {$newsMonth->cName}
                                    {badge variant="outline-secondary" class="ml-auto"}{$newsMonth->nAnzahl}{/badge}
                                {/navitem}
                            {/block}
                        {/foreach}
                    {/nav}
                {/collapse}
            {/block}
        {/block}
    {/card}
    {block name='boxes-box-news-month-hr-end'}
        <hr class="my-3 d-flex d-md-none">
    {/block}
{/block}
