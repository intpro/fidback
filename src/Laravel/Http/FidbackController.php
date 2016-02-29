<?php

namespace Interpro\Fidback\Laravel\Http;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Request;
use Interpro\Fidback\Concept\Command\RegisterMessageCommand;

class FidbackController extends Controller
{

    public function __construct()
    {

    }

    public function getForm($type)
    {
        try {

            $block_name = config('fidback')['block_name'];

            $complhtml = view('back/'.$block_name.'/forms/'.$type, [])->render();

            $status = 'OK';

            return compact('status', 'complhtml');

        } catch(\Exception $exception) {

            return ['status'=>('Что-то пошло не так. '.$exception->getMessage())];
        }
    }

    public function sendMessage()
    {
        if(Request::has('type_name'))
        {
            $dataobj = Request::all();

            try {

                $this->dispatch(new RegisterMessageCommand($dataobj['type_name'], $dataobj));

                return ['status' => 'OK'];

            } catch(\Exception $exception) {
                return ['status' => ('Что-то пошло не так. '.$exception->getMessage())];
            }
        } else {

            return ['status' => 'Не хватает параметров для сохранения.'];
        }
    }

}
