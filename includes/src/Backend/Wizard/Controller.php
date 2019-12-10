<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

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
        //TODO: errors?
        $errors = false;
        foreach ($this->getSteps() as $step) {
            foreach ($step->getQuestions() as $question) {
                /** @var QuestionInterface $question */
                $question->answerFromPost($post);
            }
        }
        if (!$errors) {
//            $this->finish();
        }
    }

    /**
     *
     */
    private function finish(): void
    {
        foreach ($this->getSteps() as $step) {
            foreach ($step->getQuestions() as $question) {
                /** @var QuestionInterface $question */
                $question->save();
            }
        }
        Shop::Container()->getCache()->flushAll();
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
