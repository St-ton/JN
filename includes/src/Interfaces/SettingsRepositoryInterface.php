<?php

namespace JTL\Interfaces;

interface SettingsRepositoryInterface extends RepositoryInterface
{
    public function getConfig(): array;
}
