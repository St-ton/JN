{foreach $newsCategories as $category}
    <option value="{$category->getID()}"
            {if isset($selectedCat)}
                {if is_array($selectedCat)}
                    {foreach $selectedCat as $singleCat}
                        {if $singleCat == $category->getID()} selected{/if}
                    {/foreach}
                {elseif $selectedCat == $category->getID()} selected{/if}
            {/if}>
            {for $j=1 to $i}&nbsp;&nbsp;&nbsp;{/for}{$category->getName()}
    </option>
    {if count($category->getChildren()) > 0}
        {include file='snippets/newscategories_recursive.tpl' i=$i+1 newsCategories=$category->getChildren() selectedCat=$selectedCat}
    {/if}
{/foreach}
