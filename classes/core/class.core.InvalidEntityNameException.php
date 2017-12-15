<?php

class InvalidEntityNameException extends Exception
{
    protected $entityName;

    public function __construct($entityName)
    {
        $this->entityName = $entityName;
        parent::__construct('Invalid entity name ' . $entityName);
    }
}