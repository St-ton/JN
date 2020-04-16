{if $nSeitenTyp !== $smarty.const.PAGE_ARTIKEL}
    {include
        file='productdetails/pushed_success.tpl'
        Artikel=$zuletztInWarenkorbGelegterArtikel
        cartNote={lang key='basketAdded' section='messages'}
    }
{/if}
