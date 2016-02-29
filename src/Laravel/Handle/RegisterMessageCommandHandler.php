<?php namespace Interpro\Fidback\Laravel\Handle;

use Illuminate\Support\Facades\Bus;
use Interpro\Fidback\Concept\Desk;
use Interpro\Fidback\Concept\Command\RegisterMessageCommand;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Interpro\QuickStorage\Concept\Command\UpdateGroupItemCommand;

class RegisterMessageCommandHandler {

    private $desc;

    /**
     * Update the command handler.
     *
     * @return void
     */
    public function __construct(Desk $desk)
    {
        $this->desk = $desk;
    }

    /**
     * Handle the command.
     *
     * @param  RegisterMessageCommand  $command
     * @return void
     */
    public function handle(RegisterMessageCommand $command)
    {
        $message = $this->desk->registerMessage($command->type_name, $command->data_arr);

        //Потом переписать под генерацию событий самим desk объектом
        //и подписку на события
        //а пока:

        $inqueue   = config('fidback')['inqueue'];

        $mailto    = config('fidback')['mail_rec'];
        $username  = config('fidback')['mail_username'];
        $site_name = config('fidback')['site_name'];

        try {

            if($inqueue)
            {
                Mail::queue('back/mail',
                    ['message'=>$message],
                    function($message) use ($username, $mailto, $site_name)
                    {
                        $message->from($username, 'Site');
                        $message->to($mailto, 'Admin')->subject('Сообщение из сайта '.$site_name);
                        Bus::dispatch(new UpdateGroupItemCommand($message->id_field, ['bools'=>['mailed'=>true]]));

                    },'mailqueue');
            }else{
                Mail::send('back/mail',
                    ['message'=>$message],
                    function($message) use ($username, $mailto, $site_name)
                    {
                        $message->from($username, 'Site');
                        $message->to($mailto, 'Admin')->subject('Сообщение из сайта '.$site_name);

                        Bus::dispatch(new UpdateGroupItemCommand($message->id_field, ['bools'=>['mailed'=>true]]));

                    });
            }
        } catch(\Exception $exception) {
        }

    }

}
