{block name='boxes-box-news-categories'}
    {card class="box box-newscategories mb-md-4" id="sidebox{$oBox->getID()}"}
        {block name='boxes-box-news-categories-content'}
            {block name='boxes-box-news-categories-toggle-title'}
                {link id="crd-hdr-{$oBox->getID()}"
                    href="#crd-cllps-{$oBox->getID()}"
                    data=["toggle"=>"collapse"]
                    role="button"
                    aria=["expanded"=>"false","controls"=>"crd-cllps-{$oBox->getID()}"]
                    class="text-decoration-none-util font-weight-bold-util d-md-none dropdown-toggle"}
                    {lang key='newsBoxCatOverview'}
                {/link}
            {/block}
            {block name='boxes-box-news-categories-title'}
                <div class="productlist-filter-headline d-none d-md-flex">
                    {lang key='newsBoxCatOverview'}
                </div>
            {/block}
            {block name='boxes-box-news-categories-collapse'}
                {collapse
                    class="d-md-block"
                    visible=false
                    id="crd-cllps-{$oBox->getID()}"
                    aria=["labelledby"=>"crd-hdr-{$oBox->getID()}"]}
                    {nav vertical=true class="mt-2 mt-md-0"}
                        {foreach $oBox->getItems() as $newsCategory}
                            {if $newsCategory@index === 10}{break}{/if}
                            {navitem href=$newsCategory->cURLFull title=$newsCategory->cName router-class="align-items-center-util d-flex"}
                                {$newsCategory->cName}
                                {badge variant="outline-secondary" class="ml-auto-util"}{$newsCategory->nAnzahlNews}{/badge}
                            {/navitem}
                        {/foreach}
                    {/nav}
                {/collapse}
            {/block}
        {/block}
    {/card}
    {block name='boxes-box-news-categories-hr-end'}
        <hr class="my-3 d-flex d-md-none">
    {/block}
{/block}
