<?php

/**
 * Interface IMedia
 */
interface IMedia
{
    /**
     * @param $request
     * @return mixed
     */
    public function isValid($request);

    /**
     * @param $request
     * @return mixed
     */
    public function handle($request);
}
