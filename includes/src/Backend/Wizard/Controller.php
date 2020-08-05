<?php declare(strict_types=1);

namespace JTL\Backend\Wizard;

use Illuminate\Support\Collection;
use JTL\Session\Backend;
use JTL\Shop;

/**
 * Class Controller
 * @package JTL\Backend\Wizard
 */
final class Controller
{
    /**
     * @var Collection
     */
    private $steps;

    /**
     * Controller constructor.
     * @param DefaultFactory $factory
     */
    public function __construct(DefaultFactory $factory)
    {
        $this->steps = $factory->getSteps();
    }

    /**
     * @param array $post
     */
    public function answerQuestions(array $post): void
    {
        if (empty($post)) {
            return;
        }
        $post = $this->serializeToArray($post);

        foreach ($this->getSteps() as $step) {
            foreach ($step->getQuestions() as $question) {
                /** @var QuestionInterface $question */
                $question->answerFromPost($post);
            }
        }

        $this->finish();
    }

    /**
     *
     */
    private function finish(): void
    {
        //TODO: errors?
        $errors = false;
        foreach ($this->getSteps() as $step) {
            foreach ($step->getQuestions() as $question) {
                /** @var QuestionInterface $question */
                $question->save();
            }
        }
        if (!$errors) {
            Shop::Container()->getDB()->update(
                'teinstellungen',
                'cName',
                'global_wizard_done',
                (object)['cWert' => 'Y']
            );
            Shop::Container()->getCache()->flushAll();
            unset($_SESSION['wizard']);
        }
    }

    /**
     * @param array $post
     * @return array
     */
    public function validateStep(array $post): array
    {
        $post          = $this->serializeToArray($post);
        $errorMessages = [];
        foreach ($this->getSteps() as $step) {
            foreach ($step->getQuestions() as $question) {
                if (isset($post['question-' . $question->getID()])) {
                    /** @var QuestionInterface $question */
                    $question->answerFromPost($post);
                    if (($validationError = $question->validate()) !== '') {
                        $errorMessages[$question->getID()] = $validationError;
                    }
                }
            }
        }
        Backend::set('wizard', array_merge(Backend::get('wizard') ?? [], $post));

        return $errorMessages;
    }

    /**
     * @param array $post
     * @return array
     */
    public function serializeToArray(array $post): array
    {
        if (\is_array($post[0])) {
            $postTMP = [];
            foreach ($post as $postItem) {
                if (\mb_strpos($postItem['name'], '[]') !== false) {
                    $postTMP[\explode('[]', $postItem['name'])[0]][] = $postItem['value'];
                } else {
                    $postTMP[$postItem['name']] = $postItem['value'];
                }
            }
            $post = $postTMP;
        }

        return $post;
    }

    /**
     * @return Collection
     */
    public function getSteps(): Collection
    {
        return $this->steps;
    }

    /**
     * @param Collection $steps
     */
    public function setSteps(Collection $steps): void
    {
        $this->steps = $steps;
    }
}
