{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{if !empty($oUmfrage->getName())}
    <h1>{$oUmfrage->getName()}</h1>
{else}
    <h1>{lang key='umfrage' section='umfrage'}</h1>
{/if}

{if !empty($hinweis)}
    <div class="alert alert-info">{$hinweis}</div>
{/if}
{if !empty($fehler)}
    <div class="alert alert-danger">{$fehler}</div>
{/if}
{include file='snippets/opc_mount_point.tpl' id='opc_poll_content_prepend'}

{if $oUmfrage->getQuestionCount() > 0}
    <form method="post" action="{if empty($oUmfrage->getURL())}{get_static_route id='umfrage.php'}{else}{$ShopURL}/{$oUmfrage->getURL()}{/if}" class="evo-validate">
        {$jtl_token}
        <input name="u" type="hidden" value="{$oUmfrage->getID()}" />
        {foreach $oUmfrage->getQuestions() as $question}
            {assign var=questionID value=$question->getID()}
            <input name="kUmfrageFrage[]" type="hidden" value="{$questionID}">
            <div>
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">{$question->getName()} {if !$question->isRequired()}<span class="optional"> - {lang key='conditionalFillOut' section='checkout'}</span>{/if}</h3>
                    </div>
                    <div class="panel-body form-group">
                        {if !empty($question->getDescription())}
                            <p>{$question->getDescription()}</p>
                            <hr>
                        {/if}

                        {if $question->getType() === \Survey\QuestionType::SELECT_SINGLE}
                            <select name="{$questionID}[]" class="form-control"{if $question->isRequired()} required{/if}>
                                <option value="">{lang key='pleaseChoose'}</option>
                        {elseif $question->getType() === \Survey\QuestionType::SELECT_MULTI}
                            <select name="{$questionID}[]" multiple="multiple" class="form-control"{if $question->isRequired()} required{/if}>
                        {elseif $question->getType() === \Survey\QuestionType::TEXT_SMALL}
                            <input name="{$questionID}[]"
                                   type="text"
                                   value="{if $nSessionFragenWerte_arr[$questionID]->getAnswer(0) !== null}{$nSessionFragenWerte_arr[$questionID]->getAnswer(0)}{/if}"
                                   class="form-control"{if $question->isRequired()} required{/if}>
                        {elseif $question->getType() === \Survey\QuestionType::TEXT_BIG}
                            {strip}
                                <textarea name="{$questionID}[]" rows="7" cols="60" class="form-control"{if $question->isRequired()} required{/if}>
                                    {if $nSessionFragenWerte_arr[$questionID]->getAnswer(0) !== null}{$nSessionFragenWerte_arr[$questionID]->getAnswer(0)}{/if}
                                </textarea>
                            {/strip}
                        {elseif $question->getType() === \Survey\QuestionType::MATRIX_SINGLE}
                            <table class="table table-bordered">
                            <thead>
                                <td>&nbsp;</td>
                                {foreach $question->getMatrixOptions() as $matrixOption}
                                    <td>{$matrixOption->getName()}</td>
                                {/foreach}
                            </thead>
                        {elseif $question->getType() === \Survey\QuestionType::MATRIX_MULTI}
                            <table class="table table-bordered">
                            <tr>
                                <td>&nbsp;</td>
                                {foreach $question->getMatrixOptions() as $matrixOption}
                                    <td>{$matrixOption->getName()}</td>
                                {/foreach}
                            </tr>
                        {/if}
                        {foreach $question->getAnswerOptions() as $answer}
                            {math equation='x-y' x=$answer@iteration y=1 assign='i'}

                            {if $question->getType() === \Survey\QuestionType::MULTI_SINGLE}
                                <div class="radio">
                                    <label>
                                        <input name="{$questionID}[]"
                                               type="radio"
                                               value="{$answer->getID()}" {if !empty($nSessionFragenWerte_arr[$questionID]->getAnswer())}{foreach $nSessionFragenWerte_arr[$questionID]->getAnswer() as $cUmfrageFrageAntwort}{if $cUmfrageFrageAntwort == $answer->getID()} checked="checked"{/if}{/foreach}{/if} {if $question->isRequired()} required{/if}/>
                                        {$answer->getName()}
                                    </label>
                                </div>
                            {/if}

                            {if $question->getType() === \Survey\QuestionType::MULTI}
                                <div class="checkbox">
                                    <label>
                                        <input name="{$questionID}[]"
                                               type="checkbox"
                                               value="{$answer->getID()}"
                                               {if $nSessionFragenWerte_arr[$questionID]->isActive($answer->getID())} checked="checked"{/if}/> {$answer->getName()}
                                    </label>
                                </div>
                            {/if}

                            {if $question->getType() === \Survey\QuestionType::SELECT_SINGLE}
                                <option value="{$answer->getID()}"
                                    {if $nSessionFragenWerte_arr[$questionID]->isActive($answer->getID())} selected{/if}> {$answer->getName()}
                                </option>
                            {/if}

                            {if $question->getType() === \Survey\QuestionType::SELECT_MULTI}
                                <option value="{$answer->getID()}"
                                    {if !empty($nSessionFragenWerte_arr[$questionID]->getAnswer())}{foreach $nSessionFragenWerte_arr[$questionID]->getAnswer() as $cUmfrageFrageAntwort}{if $cUmfrageFrageAntwort == $answer->getID()} selected{/if}{/foreach}{/if}> {$answer->getName()}
                                </option>
                            {/if}

                            {if $question->getType() === \Survey\QuestionType::MATRIX_SINGLE}
                                <tr>
                                    <td>{$answer->getName()}</td>
                                    {foreach $question->getMatrixOptions() as $oUmfrageMatrixOption}
                                        {math equation='x-y' x=$oUmfrageMatrixOption@iteration y=1 assign='i'}
                                        <td>
                                            <div class="radio">
                                                <label>
                                                    <input name="{$questionID}_{$answer->getID()}"
                                                          type="radio"
                                                          value="{$answer->getID()}_{$oUmfrageMatrixOption->getID()}"
                                                          {if $nSessionFragenWerte_arr[$questionID]->isActive($answer->getID(), $oUmfrageMatrixOption->getID())} checked{/if}{if $question->isRequired()} required{/if}/>
                                                </label>
                                            </div>
                                        </td>
                                    {/foreach}
                                </tr>
                            {/if}

                            {if $question->getType() === \Survey\QuestionType::MATRIX_MULTI}
                                <tr>
                                    <td>{$answer->getName()}</td>
                                    {foreach $question->getMatrixOptions() as $oUmfrageMatrixOption}
                                        {math equation='x-y' x=$oUmfrageMatrixOption@iteration y=1 assign='i'}
                                        <td>
                                            <input name="{$questionID}[]"
                                                   type="checkbox"
                                                   value="{$answer->getID()}_{$oUmfrageMatrixOption->getID()}"
                                                   {if !empty($nSessionFragenWerte_arr[$questionID]->getAnswer())}{foreach $nSessionFragenWerte_arr[$questionID]->getAnswer() as $answerTMP}{if $answerTMP->kUmfrageFrageAntwort == $answer->getID() && $oUmfrageMatrixOption->getID() == $answerTMP->kUmfrageMatrixOption} checked{/if}{/foreach}{/if}/>
                                        </td>
                                    {/foreach}
                                </tr>
                            {/if}

                        {/foreach}
                        {if $question->getType() === \Survey\QuestionType::SELECT_SINGLE}
                            </select>
                        {elseif $question->getType() === \Survey\QuestionType::SELECT_MULTI}
                            </select>
                        {elseif $question->getType() === \Survey\QuestionType::MATRIX_SINGLE}
                             </table>
                        {elseif $question->getType() === \Survey\QuestionType::MATRIX_MULTI}
                             </table>
                        {/if}

                        {if $question->hasFreeField()}
                            {if $question->getType() === \Survey\QuestionType::MULTI_SINGLE}
                                <div class="radio">
                                    <label>
                                        <input name="{$questionID}[]"
                                               type="radio"
                                               value="-1"
                                              {if !empty($nSessionFragenWerte_arr[$questionID]->getAnswer())}{foreach $nSessionFragenWerte_arr[$questionID]->getAnswer() as $cUmfrageFrageAntwort}{if $cUmfrageFrageAntwort == -1} checked{/if}{/foreach}{/if} {if $question->isRequired()} required{/if}/>
                                        <input
                                            name="{$questionID}[]"
                                            type="text" class="form-control"
                                            value="{if $nSessionFragenWerte_arr[$questionID]->getAnswer(1) !== null}{$nSessionFragenWerte_arr[$questionID]->getAnswer(1)}{/if}"/>
                                    </label>
                                </div>
                            {elseif $question->getType() === \Survey\QuestionType::MULTI}
                                <input name="{$questionID}[]"
                                       type="checkbox"
                                       value="-1"
                                       {if !empty($nSessionFragenWerte_arr[$questionID]->getAnswer())}{foreach $nSessionFragenWerte_arr[$questionID]->getAnswer() as $cUmfrageFrageAntwort}{if $cUmfrageFrageAntwort == -1} checked{/if}{/foreach}{/if}/>
                                <input name="{$questionID}[]" type="text" class="form-control" value="{if $nSessionFragenWerte_arr[$questionID]->getAnswer(1) !== null}{$nSessionFragenWerte_arr[$questionID]->getAnswer(1)}{/if}" />
                            {else}
                                <input name="{$questionID}[]"
                                       type="text" class="form-control"
                                       value="{if $nSessionFragenWerte_arr[$questionID]->getAnswer(1) !== null}{$nSessionFragenWerte_arr[$questionID]->getAnswer(1)}{/if}"
                                       {if !empty($nSessionFragenWerte_arr[$questionID]->getAnswer())}{foreach $nSessionFragenWerte_arr[$questionID]->getAnswer() as $cUmfrageFrageAntwort}{if $cUmfrageFrageAntwort == -1} checked{/if}{/foreach}{/if} />
                            {/if}
                        {/if}
                        </div>{* /panel-body *}
                    </div>{* /panel *}
                </div>{* /well *}
            {/foreach}
        <div class="row">
            <div class="col-xs-4">
                {if $nAktuelleSeite <= $nAnzahlSeiten && $nAktuelleSeite != 1}
                    <button class="btn btn-default pull-left" name="back" type="submit" value="back" formnovalidate>
                        <span>&laquo; {lang key='umfrageBack' section='umfrage'}</span>
                    </button>
                {/if}
            </div>
            <div class="col-xs-4 text-center">
                <b>{lang key='umfrageQPage' section='umfrage'} {$nAktuelleSeite}</b> {lang key='from' section='product rating'} {$nAnzahlSeiten}
            </div>
            <div class="col-xs-4">
                {if $nAktuelleSeite > 0 && $nAktuelleSeite < $nAnzahlSeiten}
                    <button class="btn btn-default pull-right" name="next" type="submit" value="next">
                        <span>{lang key='umfrageNext' section='umfrage'}</span>
                    </button>
                {/if}
            </div>
        </div>
        <input name="s" type="hidden" value="{$nAktuelleSeite}" />
        {if $nAktuelleSeite == $nAnzahlSeiten}
            <input name="end" type="submit" value="{lang key='umfrageSubmit' section='umfrage'}" class="btn btn-primary submit top17" />
        {/if}
    </form>
{/if}
