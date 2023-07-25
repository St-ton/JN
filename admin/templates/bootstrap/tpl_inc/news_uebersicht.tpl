
<script type="text/javascript" src="{$templateBaseURL}js/sorttable.js"></script>
<script>
    $(window).on('load', function(){
        $('#submitDelete').on('click', function(){
            $('#' + $(this).data('name') + ' input[data-id="loeschen"]').trigger('click');
        });

        $('#kategorien button[data-target=".delete-modal"]').on('click', function(){
            $('.modal-title').html('{__('newsDeleteCat')}');
            $('#submitDelete').data('name', 'kategorien');

            var itemsToDelete = '';
            $('input[name="kNewsKategorie[]"]:checked').each(function(i){
                itemsToDelete += '<li class="list-group-item list-group-item-warning">' + $(this).data('name') + '</li>';
            });
            $('.delete-modal .modal-body').html('<ul class="list-group">' + itemsToDelete + '</ul>');
        });
        $('#aktiv button[data-target=".delete-modal"]').on('click', function(){
            $('.modal-title').html('{__('newsDeleteNews')}');
            $('#submitDelete').data('name', 'aktiv');
        });
        $('#inaktiv button[data-target=".delete-modal"]').on('click', function(){
            $('.modal-title').html('{__('newsDeleteComment')}');
            $('#submitDelete').data('name', 'inaktiv');
        });

        $('#category-list i.nav-toggle').on('click', function(event) {
            event.stopPropagation();
            var tr = $(this).closest('tr');
            var td = $(this).parent();
            var currentLevel = parseInt(tr.data('level')),
                state = td.hasClass('hide-toggle-on'),
                nextEl = tr.next(),
                nextLevel = parseInt(nextEl.data('level'));
            while (currentLevel < nextLevel) {
                nextEl.toggle(state);
                nextEl = nextEl.next();
                nextLevel = parseInt(nextEl.data('level'));
            }
            td.toggleClass('hide-toggle-on');
            td.find('i.fa').toggleClass('fa-caret-right fa-caret-down');
        });
    });
</script>
{include file='tpl_inc/seite_header.tpl' cTitel=__('news') cBeschreibung=__('newsDesc') cDokuURL=__('newsURL')}
<div id="content">
    <div class="tabs">
        <nav class="tabs-nav">
            <ul class="nav nav-tabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link {if $cTab === '' || $cTab === 'inaktiv'} active{/if}" data-toggle="tab" role="tab" href="#inaktiv">
                        {__('newsCommentActivate')}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {if $cTab === 'aktiv'} active{/if}" data-toggle="tab" role="tab" href="#aktiv">
                        {__('newsOverview')}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {if $cTab === 'kategorien'} active{/if}" data-toggle="tab" role="tab" href="#kategorien">
                        {__('newsCatOverview')}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {if $cTab === 'einstellungen'} active{/if}" data-toggle="tab" role="tab" href="#einstellungen">
                        {__('settings')}
                    </a>
                </li>
            </ul>
        </nav>
        <div class="tab-content">
            <div id="inaktiv" class="tab-pane fade{if $cTab === '' || $cTab === 'inaktiv'} active show{/if}">
                {if $comments && count($comments) > 0}
                    {include file='tpl_inc/pagination.tpl' pagination=$oPagiKommentar cAnchor='inaktiv'}
                    <form method="post" action="{$adminURL}{$route}">
                        {$jtl_token}
                        <input type="hidden" name="news" value="1" />
                        <input type="hidden" name="newskommentar_freischalten" value="1" />
                        <input type="hidden" name="nd" value="1" />
                        <input type="hidden" name="tab" value="inaktiv" />
                        <div>
                            <div class="subheading1">{__('newsCommentActivate')}</div>
                            <hr class="mb-3">
                            <div class="table-responsive">
                                <table class="list table table-striped">
                                    <thead>
                                    <tr>
                                        <th class="check">&nbsp;</th>
                                        <th class="text-left">{__('visitors')}</th>
                                        <th class="text-left">{__('headline')}</th>
                                        <th class="text-left">{__('text')}</th>
                                        <th class="th-5 text-center">{__('newsDate')}</th>
                                        <th class="th-6 text-center" style="min-width: 140px;"></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    {foreach $comments as $comment}
                                        <tr>
                                            <td class="check">
                                                <div class="custom-control custom-checkbox">
                                                    <input class="custom-control-input" type="checkbox" name="kNewsKommentar[]" value="{$comment->getID()}" id="comment-{$comment->getID()}" />
                                                    <label class="custom-control-label" for="comment-{$comment->getID()}"></label>
                                                </div>
                                            </td>
                                            <td class="TD2">
                                                <label for="comment-{$comment->getID()}">
                                                    {$comment->getName()} ({$comment->getMail()})
                                                </label>
                                            </td>
                                            <td class="TD3">{$comment->getNewsTitle()|truncate:50:'...'}</td>
                                            <td class="TD4">{$comment->getText()|truncate:150:'...'}</td>
                                            <td class="text-center">{$comment->getDateCreatedCompat()}</td>
                                            <td class="text-center">
                                                <div class="btn-group">
                                                    <a href="{$adminURL}{$route}?news=1&kNews={$comment->getNewsID()}&kNewsKommentar={$comment->getID()}&nkedit=1&tab=inaktiv&token={$smarty.session.jtl_token}"
                                                       class="btn btn-link px-2"
                                                       title="{__('modify')}"
                                                       data-toggle="tooltip">
                                                        <span class="icon-hover">
                                                            <span class="fal fa-edit"></span>
                                                            <span class="fas fa-edit"></span>
                                                        </span>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    {/foreach}
                                    </tbody>
                                </table>
                            </div>
                            {include file='tpl_inc/pagination.tpl' pagination=$oPagiKommentar cAnchor='inaktiv' isBottom=true}
                            <div class="card-footer save-wrapper">
                                <div class="row">
                                    <div class="col-sm-6 col-xl-auto text-left">
                                        <div class="custom-control custom-checkbox">
                                            <input class="custom-control-input" name="ALLMSGS" id="ALLMSGS1" type="checkbox" onclick="AllMessages(this.form);" />
                                            <label class="custom-control-label" for="ALLMSGS1">{__('globalSelectAll')}</label>
                                        </div>
                                    </div>
                                    <div class="ml-auto col-sm-6 col-xl-auto">
                                        <input name="kommentareloeschenSubmit" type="submit" data-id="loeschen" value="1" class="hidden-soft">
                                        <button name="kommentareloeschenSubmit" type="button" data-toggle="modal" data-target=".delete-modal" value="{__('delete')}" class="btn btn-danger btn-block"><i class="fas fa-trash-alt"></i> {__('delete')}</button>
                                    </div>
                                    <div class="col-sm-6 col-xl-auto">
                                        <button name="freischalten" type="submit" value="{__('newsActivate')}" class="btn btn-primary btn-block"><i class="fa fa-thumbs-up"></i> {__('newsActivate')}</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                {else}
                    <div class="alert alert-info" role="alert">{__('noDataAvailable')}</div>
                {/if}
            </div>
            <div id="aktiv" class="tab-pane fade{if $cTab === 'aktiv'} active show{/if}">
                {include file='tpl_inc/pagination.tpl' pagination=$oPagiNews cAnchor='aktiv'}
                <form name="news" method="post" action="{$adminURL}{$route}">
                    {$jtl_token}
                    <input type="hidden" name="news" value="1" />
                    <input type="hidden" name="tab" value="aktiv" />
                    <div>
                        <div class="subheading1">{__('newsOverview')}</div>
                        <hr class="mb-3">
                        <div class="table-responsive">
                            <table class="sortable list table table-striped">
                                <thead>
                                <tr>
                                    <th class="check"></th>
                                    <th class="text-left">{__('headline')}</th>
                                    <th class="text-left">{__('customerGroup')}</th>
                                    <th class="text-center">{__('newsValidation')}</th>
                                    <th class="text-center">{__('active')}</th>
                                    <th class="text-center">{__('newsComments')}</th>
                                    <th class="text-center">{__('newsCatLastUpdate')}</th>
                                    <th class="text-center" style="min-width: 100px;"></th>
                                </tr>
                                </thead>
                                <tbody>
                                {if count($oNews_arr) > 0}
                                    {foreach $oNews_arr as $oNews}
                                        <tr>
                                            <td class="check">
                                                <div class="custom-control custom-checkbox">
                                                    <input class="custom-control-input" type="checkbox" name="kNews[]" value="{$oNews->getID()}" id="news-cb-{$oNews->getID()}" />
                                                    <label class="custom-control-label" for="news-cb-{$oNews->getID()}"></label>
                                                </div>
                                            </td>
                                            <td class="TD2"><label for="news-cb-{$oNews->getID()}">{$oNews->getTitle()}</label></td>
                                            <td class="TD4">
                                                {foreach $oNews->getCustomerGroups() as $customerGroupID}
                                                    {if $customerGroupID === -1}{__('all')}{else}{\JTL\Customer\CustomerGroup::getNameByID($customerGroupID)}{/if}{if !$customerGroupID@last},{/if}
                                                {/foreach}
                                            </td>
                                            <td class="text-center">{$oNews->getDateValidFromLocalizedCompat()}</td>
                                            <td class="text-center">
                                                <i class="fal fa-{if $oNews->getIsActive()}check text-success{else}times text-danger{/if}"></i>
                                            </td>
                                            <td class="text-center">
                                                {if $oNews->getCommentCount() > 0}
                                                    <a href="{$adminURL}{$route}?news=1&nd=1&kNews={$oNews->getID()}&tab=aktiv&token={$smarty.session.jtl_token}">{$oNews->getCommentCount()}</a>
                                                {else}
                                                    {$oNews->getCommentCount()}
                                                {/if}
                                            </td>
                                            <td class="text-center">{$oNews->getDateCompat()}</td>
                                            <td class="text-center">
                                                <div class="btn-group">
                                                    <a href="{$adminURL}{$route}?news=1&nd=1&kNews={$oNews->getID()}&tab=aktiv&token={$smarty.session.jtl_token}"
                                                       class="btn btn-link px-2"
                                                       title="{__('preview')}"
                                                       data-toggle="tooltip">
                                                        <span class="icon-hover">
                                                            <span class="fal fa-eye"></span>
                                                            <span class="fas fa-eye"></span>
                                                        </span>
                                                    </a>
                                                    <a href="{$adminURL}{$route}?news=1&news_editieren=1&kNews={$oNews->getID()}&tab=aktiv&token={$smarty.session.jtl_token}"
                                                       class="btn btn-link px-2"
                                                       title="{__('modify')}"
                                                       data-toggle="tooltip">
                                                        <span class="icon-hover">
                                                            <span class="fal fa-edit"></span>
                                                            <span class="fas fa-edit"></span>
                                                        </span>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    {/foreach}
                                {else}
                                    <tr>
                                        <td colspan="9">
                                            <div class="alert alert-info" role="alert">{__('noDataAvailable')}</div>
                                        </td>
                                    </tr>
                                {/if}
                                </tbody>
                            </table>
                        </div>
                        <input type="hidden" name="news" value="1" />
                        <input type="hidden" name="tab" value="aktiv" />
                        {include file='tpl_inc/pagination.tpl' pagination=$oPagiNews cAnchor='aktiv' isBottom=true}
                        <div class="card-footer save-wrapper">
                            <div class="row">
                                <div class="col-sm-6 col-xl-auto text-left">
                                    <div class="custom-control custom-checkbox">
                                        <input class="custom-control-input" name="ALLMSGS" id="ALLMSGS2" type="checkbox" onclick="AllMessages(this.form);" />
                                        <label class="custom-control-label" for="ALLMSGS2">{__('globalSelectAll')}</label>
                                    </div>
                                </div>
                                <div class="ml-auto col-sm-6 col-xl-auto">
                                    <input name="news_loeschen" type="submit" data-id="loeschen" value="1" class="hidden-soft">
                                    <button name="news_loeschen" type="button" data-toggle="modal" data-target=".delete-modal" value="{__('delete')}" class="btn btn-danger btn-block"><i class="fas fa-trash-alt"></i> {__('delete')}</button>
                                </div>
                                <div class="col-sm-6 col-xl-auto">
                                    <button name="news_erstellen" type="submit" value="1" class="btn btn-primary btn-block"><i class="fa fa-share"></i> {__('newAdd')}</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
                <div class="container2">
                    <form name="erstellen" method="post" action="{$adminURL}{$route}">
                        {$jtl_token}
                    </form>
                </div>
            </div>
            <div id="kategorien" class="tab-pane fade{if $cTab === 'kategorien'} active show{/if}">
                {include file='tpl_inc/pagination.tpl' pagination=$oPagiKats cAnchor='kategorien'}
                <form name="news" method="post" action="{$adminURL}{$route}">
                    {$jtl_token}
                    <input type="hidden" name="news" value="1" />
                    <input type="hidden" name="tab" value="kategorien" />
                    <div>
                        <div class="subheading1">{__('newsCatOverview')}</div>
                        <hr class="mb-3">
                        <div class="table-responsive">
                            <table id="category-list" class="list table table-striped">
                                <thead>
                                <tr>
                                    <th class="check"></th>
                                    <th class="text-left">{__('name')}</th>
                                    <th class=" text-center">{__('sorting')}</th>
                                    <th class="th-4 text-center">{__('active')}</th>
                                    <th class="th-5 text-center">{__('newsCatLastUpdate')}</th>
                                    <th class="th-5 text-center">&nbsp;</th>
                                </tr>
                                </thead>
                                <tbody>
                                {if count($newsCategories) > 0}
                                    {foreach $newsCategories as $category}
                                        <tr scope="row" class="tab_bg{$category@iteration % 2}{if $category->getLevel() > 1} hidden-soft{/if}" data-level="{$category->getLevel()}">
                                            <th class="check">
                                                <div class="custom-control custom-checkbox">
                                                    <input class="custom-control-input" type="checkbox" name="kNewsKategorie[]" data-name="{$category->getName()}" value="{$category->getID()}" id="newscat-{$category->getID()}" />
                                                    <label class="custom-control-label" for="newscat-{$category->getID()}"></label>
                                                </div>
                                            </th>
                                            <td class="TD2{if $category->getLevel() === 1} hide-toggle-on{/if}" data-name="category">
                                                <i class="fa fa-caret-right nav-toggle{if $category->getChildren()->count() === 0} hidden{/if} cursor-pointer"></i>
                                                <label for="newscat-{$category->getID()}">{$category->getName()|default:'???'}</label>
                                            </td>
                                            <td class="text-center">{$category->getSort()}</td>
                                            <td class="text-center">
                                                <i class="fal fa-{if $category->getIsActive()}check text-success{else}times text-danger{/if}"></i>
                                            </td>
                                            <td class="text-center">{$category->getDateLastModified()->format('d.m.Y H:i')}</td>
                                            <td class="text-center">
                                                <div class="btn-group">
                                                    <a href="{$adminURL}{$route}?news=1&newskategorie_editieren=1&kNewsKategorie={$category->getID()}&tab=kategorien&token={$smarty.session.jtl_token}"
                                                       class="btn btn-link px-2"
                                                       title="{__('modify')}"
                                                       data-toggle="tooltip">
                                                        <span class="icon-hover">
                                                            <span class="fal fa-edit"></span>
                                                            <span class="fas fa-edit"></span>
                                                        </span>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                        {include 'tpl_inc/newscategories_recursive.tpl' children=$category->getChildren() level=$category->getLevel()}
                                    {/foreach}
                                {else}
                                    <tr>
                                        <td colspan="6">
                                            <div class="alert alert-info" role="alert">{__('noDataAvailable')}</div>
                                        </td>
                                    </tr>
                                {/if}
                                </tbody>
                            </table>
                        </div>
                        <input type="hidden" name="news" value="1" />
                        <input type="hidden" name="tab" value="kategorien" />
                        {include file='tpl_inc/pagination.tpl' pagination=$oPagiKats cAnchor='kategorien' isBottom=true}
                        <div class="card-footer save-wrapper">
                            <div class="row">
                                <div class="col-sm-6 col-xl-auto text-left">
                                    <div class="custom-control custom-checkbox">
                                        <input class="custom-control-input" name="ALLMSGS" id="ALLMSGS3" type="checkbox" onclick="AllMessages(this.form);" />
                                        <label class="custom-control-label" for="ALLMSGS3">{__('globalSelectAll')}</label>
                                    </div>
                                </div>
                                <div class="ml-auto col-sm-6 col-xl-auto">
                                    <input name="news_kategorie_loeschen" type="submit" data-id="loeschen" value="1" class="hidden-soft">
                                    <button name="news_kategorie_loeschen" type="button" data-toggle="modal" data-target=".delete-modal" value="{__('delete')}" class="btn btn-danger btn-block">
                                        <i class="fas fa-trash-alt"></i> {__('delete')}
                                    </button>
                                </div>
                                <div class="col-sm-6 col-xl-auto">
                                    <button name="news_kategorie_erstellen" type="submit" value="1" class="btn btn-primary btn-block">
                                        <i class="fa fa-share"></i> {__('newsCatCreate')}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div id="einstellungen" class="tab-pane fade{if $cTab === 'einstellungen'} active show{/if}">
                {include file='tpl_inc/news_month_prefixes.tpl' assign='additional'}
                {include file='tpl_inc/config_section.tpl'
                    name='einstellen'
                    a='saveSettings'
                    action=$adminURL|cat:$route
                    buttonCaption=__('saveWithIcon')
                    title=__('settings')
                    skipHeading=true
                    additional=$additional
                    tab='einstellungen'}
            </div>
        </div>
    </div>
</div>
<div class="modal delete-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">{__('deleteComment')}</h2>
            </div>
            <div class="modal-body"></div>
            <div class="modal-footer">
                <p>{__('wantToConfirm')}</p>
                <div class="row">
                    <div class="ml-auto col-sm-6 col-xl-auto">
                        <button type="button" class="btn btn-outline-primary" data-dismiss="modal">
                            {__('cancelWithIcon')}
                        </button>
                    </div>
                    <div class="col-sm-6 col-xl-auto">
                        <button type="button" id="submitDelete" data-name="" class="btn btn-danger">{__('delete')}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
