<?php declare(strict_types=1);

namespace JTL\Router\Middleware;

use JTL\Language\LanguageHelper;
use JTL\Language\LanguageModel;
use JTL\Router\State;
use JTL\Shop;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Class LocaleCheckMiddleware
 * @package JTL\Router\Middleware
 */
class LocaleCheckMiddleware implements MiddlewareInterface
{
    /**
     * @inheritdoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (($lang = $this->parseLanguageFromArgs($request->getAttribute('lang'))) !== null) {
            /** @var State $state */
            $state = $handler->getStrategy()?->getState();
            if ($state !== null) {
                $state->languageID = $lang->getId();
            }
            Shop::updateLanguage($lang->getId(), $lang->getCode());
        }

        return $handler->handle($request);
    }

    /**
     * @param string|null $lang
     * @return LanguageModel|null
     */
    protected function parseLanguageFromArgs(?string $lang): ?LanguageModel
    {
        if ($lang === null) {
            return null;
        }
        foreach (LanguageHelper::getAllLanguages() as $languageModel) {
            if ($lang === $languageModel->getIso639()) {
                return $languageModel;
            }
        }

        return null;
    }
}
