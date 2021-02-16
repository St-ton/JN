{block name='boxes-box-custom'}
    {card class="box box-custom box-normal" id="sidebox{$oBox->getID()}"}
        {block name='boxes-box-custom-title'}
            <div class="productlist-filter-headline">
                {$oBox->getTitle()}
            </div>
        {/block}
        <div class="box-content-wrapper">
            {eval var=$oBox->getContent()}
        </div>
    {/card}
{/block}
