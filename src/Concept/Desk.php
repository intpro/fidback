<?php

namespace Interpro\Fidback\Concept;

interface Desk
{
    /**
     * @param string $group_name
     *
     * @param array $data_arr
     *
     */
    public function registerMessage($group_name, $data_arr);

}