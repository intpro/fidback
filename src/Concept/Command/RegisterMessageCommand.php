<?php namespace Interpro\Fidback\Concept\Command;

class RegisterMessageCommand {

    public $type_name;
    public $data_arr;
    public $template;

    /**
     * Create a new command instance.
     *
     * @param string $type_name
     * @param array $data_arr
     *
     * @return void
     */
    public function __construct($type_name, $data_arr, $template)
    {
        $this->type_name = $type_name;
        $this->data_arr = $data_arr;
        $this->template = $template;
    }

}
