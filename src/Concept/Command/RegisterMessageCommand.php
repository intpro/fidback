<?php namespace Interpro\QuickStorage\Concept\Command;

class RegisterMessageCommand extends Command {

    public $type_name;
    public $data_arr;

    /**
     * Create a new command instance.
     *
     * @param string $type_name
     * @param array $data_arr
     *
     * @return void
     */
    public function __construct($type_name, $data_arr)
    {
        $this->type_name = $type_name;
        $this->data_arr = $data_arr;
    }

}
