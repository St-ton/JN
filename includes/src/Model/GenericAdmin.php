<?php declare(strict_types=1);

namespace JTL\Model;

use JTL\DB\DbInterface;
use JTL\Services\JTL\AlertServiceInterface;
use JTL\Smarty\JTLSmarty;

/**
 * Class GenericAdmin
 * @package JTL\Model
 * @deprecated since 5.2.0
 */
class GenericAdmin
{
    /**
     * GenericAdmin constructor.
     * @param DataModelInterface    $model
     * @param string                $adminBaseFile
     * @param DbInterface           $db
     * @param AlertServiceInterface $alertService
     * @deprecated since 5.2.0
     */
    public function __construct(
        DataModelInterface $model,
        string $adminBaseFile,
        DbInterface $db,
        AlertServiceInterface $alertService
    ) {
        \trigger_error(__CLASS__ . ' is deprecated and should not be used anymore.', \E_USER_DEPRECATED);
    }

    public function handle(): void
    {
    }

    /**
     * @param int  $itemID
     * @param bool $continue
     */
    protected function save(int $itemID, bool $continue): void
    {
    }

    /**
     * @param bool  $continue
     * @param array $modelIDs
     */
    protected function update(bool $continue, array $modelIDs): void
    {
    }

    protected function setMessages(): void
    {
    }

    /**
     * @param array $ids
     */
    protected function enable(array $ids): void
    {
    }

    /**
     * @param array $ids
     */
    protected function disable(array $ids): void
    {
    }

    /**
     * @param array $ids
     * @param int   $state
     * @return bool
     */
    protected function setState(array $ids, int $state): bool
    {
        return false;
    }

    /**
     * @param JTLSmarty $smarty
     * @param string    $template
     * @return void
     */
    public function display(JTLSmarty $smarty, string $template): void
    {
    }

    /**
     * @param int $code
     */
    public function modelPRG(int $code = 303): void
    {
    }

    /**
     * @param DataModelInterface $model
     * @param array              $post
     * @return bool
     */
    public function updateFromPost(DataModelInterface $model, array $post): bool
    {
        return false;
    }

    /**
     * @param int[] $ids
     * @return bool
     */
    public function deleteFromPost(array $ids): bool
    {
        return false;
    }

    /**
     * @return void
     */
    public function saveSettings(): void
    {
    }
}
