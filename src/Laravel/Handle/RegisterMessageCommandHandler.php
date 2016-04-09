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
    public function __construct(Desk $desc, QueryAgent $queryAgent)
    {
        $this->desc = $desc;
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
        $message = $this->desc->registerMessage($command->type_name, $command->data_arr);

        //Потом переписать под генерацию событий самим desc объектом
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

        try
        {
            $message_id = $message->id_field;

            if($inqueue)
            {
                Mail::queue('back/mail/'.$command->template,
                    ['item'=>$message],
                    function($message) use ($username, $mailto, $site_name, $message_id)
                    {
                        $message->from($username, 'Site');
                        $message->to($mailto, 'Admin')->subject('Сообщение из сайта '.$site_name);
                        Bus::dispatch(new UpdateGroupItemCommand($message_id, ['bools'=>['mailed'=>true]]));

                    },'mailqueue');
            }else{
                Mail::send('back/mail/'.$command->template,
                    ['item'=>$message],
                    function($message) use ($username, $mailto, $site_name, $message_id)
                    {
                        $message->from($username, 'Site');
                        $message->to($mailto, 'Admin')->subject('Сообщение из сайта '.$site_name);

                        Bus::dispatch(new UpdateGroupItemCommand($message_id, ['bools'=>['mailed'=>true]]));

                    });
            }
        }catch(\Exception $Exc)
        {
            Log::info($Exc->getMessage());
        }

    }

}
