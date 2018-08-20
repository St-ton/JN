{extends file="{$parent_template_path}/layout/footer.tpl"}

{block name='footer-sidepanel-left'}
    <aside id="sidepanel_left" class="hidden-print col-xs-12 {if $nSeitenTyp === $smarty.const.PAGE_ARTIKELLISTE} col-md-4 col-md-pull-8 {/if} col-lg-3 col-lg-pull-8">
        {block name='footer-sidepanel-left-content'}{$boxes.left}{/block}
    </aside>
{/block}