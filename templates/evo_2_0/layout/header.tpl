{extends file="{$parent_template_path}/layout/header.tpl"}

{block name='content-container-block-starttag'}
    <div class="{if !$isFluidContent} beveled{/if}">
{/block}

{block name='content-starttag'}
    <div id="content" class="col-xs-12{if !$bExclusive && !empty($boxes.left|strip_tags|trim)} {if $nSeitenTyp === 2} col-md-8 col-md-push-4 {/if} col-lg-8 col-lg-push-4{/if}">
{/block}