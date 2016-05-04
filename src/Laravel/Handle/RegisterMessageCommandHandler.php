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
            $mailto_copy1  = $block->mail_rec_copy1_field;
            if(!$mailto_copy1)
            {
                $mailto_copy1 = config('fidback')['mail_rec_copy1'];
            }
        }catch(WrongBlockFieldNameException $exc){
            $mailto_copy1 = config('fidback')['mail_rec_copy1'];
        }
        //------------------
        try{
            $mailto_copy2  = $block->mail_rec_copy2_field;
            if(!$mailto_copy2)
            {
                $mailto_copy2 = config('fidback')['mail_rec_copy2'];
            }
        }catch(WrongBlockFieldNameException $exc){
            $mailto_copy2 = config('fidback')['mail_rec_copy2'];
        }
        //------------------
        try{
            $mailto_copy3  = $block->mail_rec_copy3_field;
            if(!$mailto_copy3)
            {
                $mailto_copy3 = config('fidback')['mail_rec_copy3'];
            }
        }catch(WrongBlockFieldNameException $exc){
            $mailto_copy3 = config('fidback')['mail_rec_copy3'];
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
                    function($message) use ($username, $mailto, $mailto_copy1, $mailto_copy2, $mailto_copy3, $site_name, $message_id)
                    {
                        $message->from($username, $site_name);
                        $message->to($mailto);
                        if($mailto_copy1){
                            $message->cc($mailto_copy1);
                        }
                        if($mailto_copy2){
                            $message->cc($mailto_copy2);
                        }
                        if($mailto_copy3){
                            $message->cc($mailto_copy3);
                        }
                        $message->subject('Сообщение из сайта '.$site_name);

                        Bus::dispatch(new UpdateGroupItemCommand($message_id, ['bools'=>['mailed'=>true]]));

                    },'mailqueue');
            }else{
                Mail::send('back/mail/'.$command->template,
                    ['item'=>$message],
                    function($message) use ($username, $mailto, $mailto_copy1, $mailto_copy2, $mailto_copy3, $site_name, $message_id)
                    {
                        $message->from($username, $site_name);
                        $message->to($mailto);
                        if($mailto_copy1){
                            $message->cc($mailto_copy1);
                        }
                        if($mailto_copy2){
                            $message->cc($mailto_copy2);
                        }
                        if($mailto_copy3){
                            $message->cc($mailto_copy3);
                        }
                        $message->subject('Сообщение из сайта '.$site_name);

                        Bus::dispatch(new UpdateGroupItemCommand($message_id, ['bools'=>['mailed'=>true]]));

                    });
            }
        }catch(\Exception $Exc)
        {
            Log::info($Exc->getMessage());
        }

    }

}
