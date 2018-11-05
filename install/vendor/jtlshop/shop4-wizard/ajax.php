<?php

//require_once __DIR__ . '/src/Shop4Wizard.php';
require_once __DIR__ . '/../../autoload.php';

$questions = $_POST['questions'];
$stepId    = isset($_REQUEST['stepId']) ? $_REQUEST['stepId'] : 0;
$wizard    = new \jtl\Wizard\Shop4Wizard($stepId);

foreach ($wizard->getQuestions() as $questionId => $question) {
    if ($question->getType() === \jtl\Wizard\Question::TYPE_BOOL) {
        $wizard->answerQuestion(
            $questionId, $questions[$questionId] === 'true'
        );
    } else {
        $wizard->answerQuestion($questionId, $questions[$questionId]);
    }
}

echo json_encode($wizard->getAvailableQuestions());
