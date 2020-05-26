<?php declare(strict_types=1);

namespace JTL\Backend\Wizard;

use Illuminate\Support\Collection;
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
        if (\is_array($post[0])) {
            $postTMP = [];
            foreach ($post as $postItem) {
                $postTMP[$postItem['name']] = $postItem['value'];
            }
            $post = $postTMP;
        }
        //TODO: errors?
        $errors = false;
        foreach ($this->getSteps() as $step) {
            foreach ($step->getQuestions() as $question) {
                /** @var QuestionInterface $question */
                $question->answerFromPost($post);
            }
        }
        if (!$errors) {
            $this->finish();
        }
    }

    /**
     *
     */
    private function finish(): void
    {
        //TODO: errors?
        $errors = false;
//        foreach ($this->getSteps() as $step) {
//            foreach ($step->getQuestions() as $question) {
//                /** @var QuestionInterface $question */
//                $question->save();
//            }
//        }
        if (!$errors) {
            Shop::Container()->getDB()->update(
                'teinstellungen',
                'cName',
                'global_wizard_done',
                (object)['cWert' => 'Y']
            );
            Shop::Container()->getCache()->flushAll();
        }
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
