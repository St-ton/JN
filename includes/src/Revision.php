<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */
ifndef('MAX_REVISIONS', 5);

/**
 * Class Revision
 */
class Revision
{
    /**
     * @var array
     */
    private $mapping;

    /**
     * Revision constructor.
     */
    public function __construct()
    {
        $this->mapping = [
            'link'   => [
                'table'         => 'tlink',
                'id'            => 'kLink',
                'reference'     => 'tlinksprache',
                'reference_id'  => 'kLink',
                'reference_key' => 'cISOSprache'
            ],
            'export' => [
                'table' => 'texportformat',
                'id'    => 'kExportformat'
            ],
            'mail'   => [
                'table'         => 'temailvorlage',
                'id'            => 'kEmailvorlage',
                'reference'     => 'temailvorlagesprache',
                'reference_id'  => 'kEmailvorlage',
                'reference_key' => 'kSprache'
            ],
            'news'   => [
                'table' => 'tnews',
                'id'    => 'kNews'
            ],
            'box'    => [
                'table'         => 'tboxen',
                'id'            => 'kBox',
                'reference'     => 'tboxsprache',
                'reference_id'  => 'kBox',
                'reference_key' => 'cISO'
            ]
        ];
    }

    /**
     * @param string $type
     * @return string|null
     */
    private function getMapping($type)
    {
        return $this->mapping[$type] ?? null;
    }

    /**
     * @param string $name
     * @param array  $mapping
     * @return $this
     */
    public function addMapping($name, $mapping)
    {
        $this->mapping[$name] = $mapping;

        return $this;
    }

    /**
     * @param int $id
     * @return stdClass|null
     */
    public function getRevision($id)
    {
        return Shop::Container()->getDB()->select('trevisions', 'id', (int)$id);
    }

    /**
     * @param string      $type
     * @param int         $key
     * @param bool        $secondary
     * @param null|string $author
     * @param bool        $utf8 - @deprecated since 5.0
     * @return bool
     * @throws InvalidArgumentException
     */
    public function addRevision($type, $key, $secondary = false, $author = null, $utf8 = true): bool
    {
        if (MAX_REVISIONS <= 0) {
            return false;
        }
        $key = (int)$key;
        if (empty($key) || ($mapping = $this->getMapping($type)) === null) {
            throw new InvalidArgumentException('Invalid type/key given. Got type ' . $type . ' and key ' . $key);
        }
        if ($author === null) {
            $author = $_SESSION['AdminAccount']->cLogin ?? '?';
        }
        $field           = $mapping['id'];
        $currentRevision = Shop::Container()->getDB()->select($mapping['table'], $mapping['id'], $key);
        if ($currentRevision === null || empty($currentRevision->$field)) {
            return false;
        }
        $revision                     = new stdClass();
        $revision->type               = $type;
        $revision->reference_primary  = $key;
        $revision->content            = $currentRevision;
        $revision->author             = $author;
        $revision->custom_table       = $mapping['table'];
        $revision->custom_primary_key = $mapping['id'];

        if ($secondary !== false && !empty($mapping['reference'])) {
            $field               = $mapping['reference_key'];
            $referencedRevisions = Shop::Container()->getDB()->selectAll($mapping['reference'],
                $mapping['reference_id'], $key);
            if (empty($referencedRevisions)) {
                return false;
            }
            $revision->content->references = [];
            foreach ($referencedRevisions as $referencedRevision) {
                $revision->content->references[$referencedRevision->$field] = $referencedRevision;
            }
        }
        $revision->content = json_encode($revision->content);
        $this->storeRevision($revision);
        $this->housekeeping($type, $key);

        return true;
    }

    /**
     * @param string $type
     * @param int    $primary
     * @return array
     */
    public function getRevisions($type, $primary): array
    {
        return array_map(function ($e) {
            $e->content = json_decode($e->content);

            return $e;
        }, Shop::Container()->getDB()->selectAll(
            'trevisions',
            ['type', 'reference_primary'],
            [$type, $primary],
            '*',
            'timestamp DESC'
        ));
    }

    /**
     * @return $this
     */
    public function deleteAll()
    {
        Shop::Container()->getDB()->query('TRUNCATE table trevisions', 3);

        return $this;
    }

    /**
     * @param object $revision
     * @return int
     */
    private function storeRevision($revision): int
    {
        return Shop::Container()->getDB()->insert('trevisions', $revision);
    }

    /**
     * @param string $type
     * @param int    $id
     * @param bool   $secondary
     * @param bool   $utf8 - @deprecated since 5.0
     * @return bool
     */
    public function restoreRevision($type, $id, $secondary = false, $utf8 = true): bool
    {
        $revision = $this->getRevision($id);
        $mapping  = $this->getMapping($type); // get static mapping from build in content types
        if ($mapping === null && !empty($revision->custom_table) && !empty($revision->custom_primary_key)) {
            // load dynamic mapping from DB
            $mapping = ['table' => $revision->custom_table, 'id' => $revision->custom_primary_key];
        }
        if (isset($revision->id) && $mapping !== null) {
            $oldCOntent = json_decode($revision->content);
            $primaryRow = $mapping['id'];
            $primaryKey = $oldCOntent->$primaryRow;
            $updates    = 0;
            unset($oldCOntent->$primaryRow);
            if ($secondary === false) {
                $updates = Shop::Container()->getDB()->update($mapping['table'], $primaryRow, $primaryKey, $oldCOntent);
            }
            if ($secondary === true && isset($mapping['reference_key'], $oldCOntent->references)) {
                $tableToUpdate = $mapping['reference'];
                $secondaryRow  = $mapping['reference_key']; // most likely something like "kSprache"
                foreach ($oldCOntent->references as $key => $value) {
                    // $key is the index in the reference array - which corresponds to the foreign key
                    unset($value->$primaryRow, $value->$secondaryRow);
                    $updates += Shop::Container()->getDB()->update(
                        $tableToUpdate,
                        [$primaryRow, $secondaryRow],
                        [$primaryKey, $key],
                        $value
                    );
                }
            }
            if ($updates > 0) {
                Shop::Container()->getDB()->delete('trevisions', 'id', $id);

                return true;
            }
        }

        return false;
    }

    /**
     * delete single revision
     *
     * @param int $id
     * @return int
     */
    public function deleteRevision($id): int
    {
        return Shop::Container()->getDB()->delete('trevisions', 'id', (int)$id);
    }

    /**
     * remove revisions that would add up to more then MAX_REVISIONS
     *
     * @param string $type
     * @param int    $key
     * @return int
     */
    private function housekeeping($type, $key): int
    {
        return Shop::Container()->getDB()->queryPrepared(
            'DELETE a 
                FROM trevisions AS a 
                JOIN ( 
                    SELECT id 
                        FROM trevisions 
                        WHERE type = :type 
                            AND reference_primary = :prim
                        ORDER BY timestamp DESC 
                        LIMIT 99999 OFFSET :max) AS b
                ON a.id = b.id',
            [
                'type' => $type,
                'prim' => $key,
                'max'  => MAX_REVISIONS
            ],
            \DB\ReturnType::AFFECTED_ROWS
        );
    }
}
