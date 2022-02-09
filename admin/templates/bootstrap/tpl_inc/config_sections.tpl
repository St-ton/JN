{$skipHeading = $skipHeading|default:false}
{foreach $sections as $section}
    {if $section->hasSectionMarkup()}
        {$section->getSectionMarkup()}
    {/if}
    {foreach $section->getSubsections() as $subsection}
        {if $subsection->show() === true}
            {if !$skipHeading && $subsection->getShownItemsCount() > 0}
                {include file='tpl_inc/config_heading.tpl' subsection=$subsection}
            {/if}
            {foreach $subsection->getItems() as $cnf}
                {if $cnf->isConfigurable() && $cnf->getShowDefault() > 0}
                    {include file='tpl_inc/config_item.tpl' cnf=$cnf}
                {/if}
            {/foreach}
            {if !$skipHeading && $subsection->getShownItemsCount() > 0}
                {include file='tpl_inc/config_footer.tpl'}
            {/if}
        {/if}
    {/foreach}
{/foreach}
