<?php namespace Interpro\Fidback\Laravel\Handle;

use Illuminate\Support\Facades\Bus;
use Interpro\Fidback\Concept\Desk;
use Interpro\Fidback\Concept\Command\RegisterMessageCommand;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Interpro\QuickStorage\Concept\Command\UpdateGroupItemCommand;
use Interpro\QuickStorage\Concept\Exception\WrongBlockFieldNameException;
use Interpro\QuickStorage\Concept\QueryAgent;

class RegisterMessageCommandHandler {

    private $desc;
    private $queryAgent;

    /**
     * Update the command handler.
     *
     * @return void
     */
    public function __construct(Desk $desk, QueryAgent $queryAgent)
    {
        $this->desk = $desk;
        $this->queryAgent = $queryAgent;
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

        $block = $this->queryAgent->getBlock('fidback',[],[]);

        //------------------
        try{
            $inqueue  = $block->inqueue_field;
            if(!$inqueue)
            {
                $inqueue = config('fidback')['inqueue'];
            }
        }catch(WrongBlockFieldNameException $exc){
            $inqueue = config('fidback')['inqueue'];
        }
        //------------------
        try{
            $mailto  = $block->mail_rec_field;
            if(!$mailto)
            {
                $mailto = config('fidback')['mail_rec'];
            }
        }catch(WrongBlockFieldNameException $exc){
            $mailto = config('fidback')['mail_rec'];
        }
        //------------------
        try{
            $username  = $block->mail_username_field;
            if(!$username)
            {
                $username = config('fidback')['mail_username'];
            }
        }catch(WrongBlockFieldNameException $exc){
            $username = config('fidback')['mail_username'];
        }
        //------------------
        try{
            $site_name  = $block->site_name_field;
            if(!$site_name)
            {
                $site_name = config('fidback')['site_name'];
            }
        }catch(WrongBlockFieldNameException $exc){
            $site_name = config('fidback')['site_name'];
        }
        //------------------


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
