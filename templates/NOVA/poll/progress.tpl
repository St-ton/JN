{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{if !empty($oUmfrage->getName())}
    <h1>{$oUmfrage->getName()}</h1>
{else}
    <h1>{lang key='umfrage' section='umfrage'}</h1>
{/if}

{include file='snippets/opc_mount_point.tpl' id='opc_poll_content_prepend'}

{if $oUmfrage->getQuestionCount() > 0}
    {form method="post" action="{if empty($oUmfrage->getURL())}{get_static_route id='umfrage.php'}{else}{$ShopURL}/{$oUmfrage->getURL()}{/if}" class="evo-validate"}
        {$jtl_token}
        {input name="u" type="hidden" value="{$oUmfrage->getID()}"}
        {foreach $oUmfrage->getQuestions() as $question}
            {assign var=questionID value=$question->getID()}
            {input name="kUmfrageFrage[]" type="hidden" value="{$questionID}"}
                {card no-body=true}
                    {cardheader}
                        <div id="poll-question-label-{$questionID}" class="h3">{$question->getName()} {if !$question->isRequired()}<span class="optional"> - {lang key='optional'}</span>{/if}</div>
                    {/cardheader}
                    {cardbody}
                        {if !empty($question->getDescription())}
                            <p>{$question->getDescription()}</p>
                            <hr>
                        {/if}

                        {if $question->getType() === \JTL\Survey\QuestionType::SELECT_SINGLE}
                            <select name="sq{$questionID}[]" class="form-control"{if $question->isRequired()} required{/if} aria-labelledby="poll-question-label-{$questionID}">
                                <option value="">{lang key='pleaseChoose'}</option>
                        {elseif $question->getType() === \JTL\Survey\QuestionType::SELECT_MULTI}
                            <select name="sq{$questionID}[]" multiple="multiple" class="form-control"{if $question->isRequired()} required{/if} aria-labelledby="poll-question-label-{$questionID}">
                        {elseif $question->getType() === \JTL\Survey\QuestionType::TEXT_SMALL}
                            {input name="sq{$questionID}[]"
                                type="text"
                                value="{if $nSessionFragenWerte_arr[$questionID]->getAnswer(0) !== null}{$nSessionFragenWerte_arr[$questionID]->getAnswer(0)}{/if}"
                                required=$question->isRequired()
                                aria=["labelledby"=>"poll-question-label-{$questionID}"]
                            }
                        {elseif $question->getType() === \JTL\Survey\QuestionType::TEXT_BIG}
                            {strip}
                                {textarea name="sq{$questionID}[]"
                                    rows="7"
                                    cols="60"
                                    required=$question->isRequired()
                                    aria=["labelledby"=>"poll-question-label-{$questionID}"]
                                }
                                    {if $nSessionFragenWerte_arr[$questionID]->getAnswer(0) !== null}
                                        {$nSessionFragenWerte_arr[$questionID]->getAnswer(0)}
                                    {/if}
                                {/textarea}
                            {/strip}
                        {elseif $question->getType() === \JTL\Survey\QuestionType::MATRIX_SINGLE}
                            <table class="table table-bordered">
                            <thead>
                                <td>&nbsp;</td>
                                {foreach $question->getMatrixOptions() as $matrixOption}
                                    <td>{$matrixOption->getName()}</td>
                                {/foreach}
                            </thead>
                        {elseif $question->getType() === \JTL\Survey\QuestionType::MATRIX_MULTI}
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

                            {if $question->getType() === \JTL\Survey\QuestionType::MULTI_SINGLE}
                                {radio name="sq{$questionID}[]"
                                    value="{$answer->getID()}"
                                    checked=($nSessionFragenWerte_arr[$questionID]->isActive($answer->getID()))
                                    aria=["label"=>"{$answer->getName()}"]
                                }
                                    {$answer->getName()}
                                {/radio}
                            {/if}

                            {if $question->getType() === \JTL\Survey\QuestionType::MULTI}
                                {checkbox name="sq{$questionID}[]"
                                    value="{$answer->getID()}"
                                    checked=($nSessionFragenWerte_arr[$questionID]->isActive($answer->getID()))
                                    aria=["label"=>"{$answer->getName()}"]
                                }
                                    {$answer->getName()}
                                {/checkbox}
                            {/if}

                            {if $question->getType() === \JTL\Survey\QuestionType::SELECT_SINGLE}
                                <option value="{$answer->getID()}"
                                    {if $nSessionFragenWerte_arr[$questionID]->isActive($answer->getID())} selected{/if}>
                                    {$answer->getName()}
                                </option>
                            {/if}

                            {if $question->getType() === \JTL\Survey\QuestionType::SELECT_MULTI}
                                <option value="{$answer->getID()}"
                                    {if !empty($nSessionFragenWerte_arr[$questionID]->getAnswer())}{foreach $nSessionFragenWerte_arr[$questionID]->getAnswer() as $cUmfrageFrageAntwort}{if $cUmfrageFrageAntwort == $answer->getID()} selected{/if}{/foreach}{/if}>
                                    {$answer->getName()}
                                </option>
                            {/if}

                            {if $question->getType() === \JTL\Survey\QuestionType::MATRIX_SINGLE}
                                <tr>
                                    <td>{$answer->getName()}</td>
                                    {foreach $question->getMatrixOptions() as $oUmfrageMatrixOption}
                                        {math equation='x-y' x=$oUmfrageMatrixOption@iteration y=1 assign='i'}
                                        <td>
                                            {radio name="sq{$questionID}_{$answer->getID()}"
                                                value="{$answer->getID()}_{$oUmfrageMatrixOption->getID()}"
                                                checked=($nSessionFragenWerte_arr[$questionID]->isActive($answer->getID(), $oUmfrageMatrixOption->getID()))
                                                aria=["label"=>"{$answer->getName()}"]
                                                required=$question->isRequired()
                                            }
                                            {/radio}
                                        </td>
                                    {/foreach}
                                </tr>
                            {/if}

                            {if $question->getType() === \JTL\Survey\QuestionType::MATRIX_MULTI}
                                <tr>
                                    <td>{$answer->getName()}</td>
                                    {foreach $question->getMatrixOptions() as $oUmfrageMatrixOption}
                                        {math equation='x-y' x=$oUmfrageMatrixOption@iteration y=1 assign='i'}
                                        <td>
                                            {checkbox name="sq{$questionID}[]"
                                                value="{$answer->getID()}_{$oUmfrageMatrixOption->getID()}"
                                                checked="{if !empty($nSessionFragenWerte_arr[$questionID]->getAnswer())}{foreach $nSessionFragenWerte_arr[$questionID]->getAnswer() as $answerTMP}{if $answerTMP->kUmfrageFrageAntwort == $answer->getID() && $oUmfrageMatrixOption->getID() == $answerTMP->kUmfrageMatrixOption} true{/if}{/foreach}{/if}"
                                                aria=["label"=>"{$answer->getName()}"]
                                            }
                                            {/checkbox}
                                        </td>
                                    {/foreach}
                                </tr>
                            {/if}

                        {/foreach}
                        {if $question->getType() === \JTL\Survey\QuestionType::SELECT_SINGLE}
                            </select>
                        {elseif $question->getType() === \JTL\Survey\QuestionType::SELECT_MULTI}
                            </select>
                        {elseif $question->getType() === \JTL\Survey\QuestionType::MATRIX_SINGLE}
                             </table>
                        {elseif $question->getType() === \JTL\Survey\QuestionType::MATRIX_MULTI}
                             </table>
                        {/if}

                        {if $question->hasFreeField()}
                            {if $question->getType() === \JTL\Survey\QuestionType::MULTI_SINGLE}
                                <div class="radio">
                                    <label>
                                        {input
                                            name="sq{$questionID}[]"
                                            type="radio"
                                            value="-1"
                                            checked="{if !empty($nSessionFragenWerte_arr[$questionID]->getAnswer())}{foreach $nSessionFragenWerte_arr[$questionID]->getAnswer() as $cUmfrageFrageAntwort}{if $cUmfrageFrageAntwort == -1}true{/if}{/foreach}{/if}"
                                            required=$question->isRequired()
                                        }
                                        {input
                                            name="sq{$questionID}[]"
                                            type="text"
                                            value="{if $nSessionFragenWerte_arr[$questionID]->getAnswer(1) !== null}{$nSessionFragenWerte_arr[$questionID]->getAnswer(1)}{/if}"
                                        }
                                    </label>
                                </div>
                            {elseif $question->getType() === \JTL\Survey\QuestionType::MULTI}
                                {input name="sq{$questionID}[]"
                                       type="checkbox"
                                       size="sm"
                                       value="-1"
                                       checked="{if !empty($nSessionFragenWerte_arr[$questionID]->getAnswer())}{foreach $nSessionFragenWerte_arr[$questionID]->getAnswer() as $cUmfrageFrageAntwort}{if $cUmfrageFrageAntwort == -1} true{/if}{/foreach}{/if}"}
                                {input name="sq{$questionID}[]" type="text" class="form-control" value="{if $nSessionFragenWerte_arr[$questionID]->getAnswer(1) !== null}{$nSessionFragenWerte_arr[$questionID]->getAnswer(1)}{/if}"}
                            {else}
                                {input name="sq{$questionID}[]"
                                       type="text"
                                       value="{if $nSessionFragenWerte_arr[$questionID]->getAnswer(1) !== null}{$nSessionFragenWerte_arr[$questionID]->getAnswer(1)}{/if}"
                                       checked="{if !empty($nSessionFragenWerte_arr[$questionID]->getAnswer())}{foreach $nSessionFragenWerte_arr[$questionID]->getAnswer() as $cUmfrageFrageAntwort}{if $cUmfrageFrageAntwort == -1} true{/if}{/foreach}{/if}"}
                            {/if}
                        {/if}
                    {/cardbody}
                {/card}
            {/foreach}
        {row}
            {col cols=4}
                {if $nAktuelleSeite <= $nAnzahlSeiten && $nAktuelleSeite != 1}
                    {button class="float-left" name="back" type="submit" value="back" formnovalidate=true}
                        <span>&laquo; {lang key='umfrageBack' section='umfrage'}</span>
                    {/button}
                {/if}
            {/col}
            {col cols=4 class="text-center"}
                <b>{lang key='umfrageQPage' section='umfrage'} {$nAktuelleSeite}</b>
                {lang key='from' section='product rating'}
                {$nAnzahlSeiten}
            {/col}
            {col cols=4}
                {if $nAktuelleSeite > 0 && $nAktuelleSeite < $nAnzahlSeiten}
                    <button class="float-right" name="next" type="submit" value="next">
                        <span>{lang key='umfrageNext' section='umfrage'}</span>
                    </button>
                {/if}
            {/col}
        {/row}
        {input name="s" type="hidden" value="{$nAktuelleSeite}"}
        {if $nAktuelleSeite == $nAnzahlSeiten}
            {input name="end" type="submit" value="{lang key='umfrageSubmit' section='umfrage'}" class="btn btn-primary submit mt-3"}
        {/if}
    {/form}
{/if}
