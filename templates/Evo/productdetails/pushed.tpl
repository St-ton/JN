{lang key="basketAdded" section="messages" assign="pushed_msg"}
{if $nSeitenTyp != $smarty.const.PAGE_ARTIKEL} {*if page == 1 no footer-popup*}
    {include file='productdetails/pushed_success.tpl' Artikel=$zuletztInWarenkorbGelegterArtikel hinweis=$pushed_msg}
{/if}
