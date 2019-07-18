{include file='tpl_inc/seite_header.tpl' cTitel=__('news') cBeschreibung=__('newsDesc')}
<div id="content">
    <div class="card">
        <div class="card-header category first clearall">
            <div class="subheading1">{$oNews->getTitle()} - {$oNews->getDate()->format('d.m.Y H:i')}</div>
            <hr class="mb-n3">
        </div>
        <div class="card-body">
            {$oNews->getContent()}

        {if $oNewsKommentar_arr|@count > 0}
            <form method="post" action="news.php">
                {$jtl_token}
                <input type="hidden" name="news" value="1" />
                <input type="hidden" name="kNews" value="{$oNews->getID()}" />
                <input type="hidden" name="kommentare_loeschen" value="1" />
                {if isset($cTab)}
                    <input type="hidden" name="tab" value="{$cTab}" />
                {/if}
                {if isset($cSeite)}
                    <input type="hidden" name="s2" value="{$cSeite}" />
                {/if}
                <input type="hidden" name="nd" value="1" />
                <div class="category subheading1 mt-3">
                    {__('newsComments')}
                    <hr class="my-2">
                </div>

                {foreach $oNewsKommentar_arr as $oNewsKommentar}
                    <div class="card ">
                        <div class="card-header">
                            <div class="form-check">
                                <input class="form-check-input" name="kNewsKommentar[]" type="checkbox" value="{$oNewsKommentar->getID()}" id="nk-{$oNewsKommentar->getID()}" />
                                <label class="form-check-label" for="nk-{$oNewsKommentar->getID()}">{$oNewsKommentar->getName()}, {$oNewsKommentar->getDateCreated()->format('d.m.Y H:i')}</label>
                                <a href="news.php?news=1&kNews={$oNews->getID()}&kNewsKommentar={$oNewsKommentar->getID()}{if isset($cBackPage)}&{$cBackPage}{elseif isset($cTab)}&tab={$cTab}{/if}&nkedit=1&token={$smarty.session.jtl_token}" class="btn btn-default btn-circle" title="{__('modify')}">
                                    <i class="fal fa-edit"></i>
                                </a>
                            </div>
                            <hr class="mb-n3">
                        </div>
                        <div class="card-body">
                            {$oNewsKommentar->getText()}
                        </div>
                    </div>
                {/foreach}
                <div class="card-footer save-wrapper">
                    <button name="kommentar_loeschen" type="submit" value="{__('delete')}" class="btn btn-danger"><i class="fas fa-trash-alt"></i> {__('delete')}</button>
                    <a class="btn btn-primary" href="news.php{if isset($cBackPage)}?{$cBackPage}{elseif isset($cTab)}?tab={$cTab}{/if}"><i class="fa fa-angle-double-left"></i> {__('back')}</a>
                </div>
            </form>
        {else}
            <p>
                <a class="btn btn-primary" href="news.php{if isset($cBackPage)}?{$cBackPage}{elseif isset($cTab)}?tab={$cTab}{/if}"><i class="fa fa-angle-double-left"></i> {__('back')}</a>
            </p>
        {/if}
        </div>
    </div>
</div>