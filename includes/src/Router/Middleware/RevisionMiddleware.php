<?php declare(strict_types=1);

namespace JTL\Router\Middleware;

use JTL\Backend\Revision;
use JTL\DB\DbInterface;
use JTL\Events\Dispatcher;
use JTL\Events\Event;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Class RevisionMiddleware
 * @package JTL\Router\Middleware
 */
class RevisionMiddleware implements MiddlewareInterface
{
    /**
     * @param DbInterface $db
     */
    public function __construct(private DbInterface $db)
    {
    }

    /**
     * @inheritdoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $body = $request->getParsedBody();
        if (\is_array($body)
            && (isset($body['revision-action'], $body['revision-type'], $body['revision-id'], $body['jtl_token']))
            && Form::validateToken($body['jtl_token'])
        ) {
            $revision = new Revision($this->db);
            Dispatcher::getInstance()->fire(Event::REVISION_RESTORE_DELETE, ['revision' => $revision]);
            if ($body['revision-action'] === 'restore') {
                $revision->restoreRevision(
                    $body['revision-type'],
                    (int)$body['revision-id'],
                    Request::postInt('revision-secondary') === 1
                );
            } elseif ($body['revision-action'] === 'delete') {
                $revision->deleteRevision((int)$body['revision-id']);
            }
        }

        return $handler->handle($request);
    }
}
