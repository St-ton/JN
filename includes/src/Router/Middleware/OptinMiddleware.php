<?php declare(strict_types=1);

namespace JTL\Router\Middleware;

use JTL\Exceptions\EmptyResultSetException;
use JTL\Exceptions\InvalidInputException;
use JTL\Optin\Optin;
use JTL\Shop;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Class OptinMiddleware
 * @package JTL\Router\Middleware
 */
class OptinMiddleware implements MiddlewareInterface
{
    /**
     * @inheritdoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $optin = $handler->getStrategy()?->getState()->optinCode ?? '';
        if (\mb_strlen($optin) > 8) {
            try {
                $successMsg = (new Optin())
                    ->setCode($optin)
                    ->handleOptin();
                Shop::Container()->getAlertService()->addInfo(
                    Shop::Lang()->get($successMsg, 'messages'),
                    'optinSucceeded'
                );
            } catch (EmptyResultSetException $e) {
                Shop::Container()->getLogService()->notice($e->getMessage());
                Shop::Container()->getAlertService()->addError(
                    Shop::Lang()->get('optinCodeUnknown', 'errorMessages'),
                    'optinCodeUnknown'
                );
            } catch (InvalidInputException) {
                Shop::Container()->getAlertService()->addError(
                    Shop::Lang()->get('optinActionUnknown', 'errorMessages'),
                    'optinUnknownAction'
                );
            }
        }

        return $handler->handle($request);
    }
}
