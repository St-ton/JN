<?php

namespace JTL\Interfaces;

interface SettingsRepositoryInterface extends RepositoryInterface
{
    /**
     * @return array
     */
    public function getConfig(): array;
}
