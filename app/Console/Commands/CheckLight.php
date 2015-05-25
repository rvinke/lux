<?php namespace App\Console\Commands;

use App\Models\Lux;
use App\Models\Message;
use App\Models\State;
use Illuminate\Console\Command;

class CheckLight extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'checklight';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Controleert of de lampen aan of uit geschakeld moeten worden.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {
        //binnenlampen
        //alleen tussen 6:45 en 23:40
        $start = \DateTime::createFromFormat("H:i", "6:45");
        $eind = \DateTime::createFromFormat("H:i", "23:45");
        $nu = new \DateTime();

        if($nu > $start && $nu < $eind) {
            $lux = Lux::orderBy('id', 'desc')->first();
            $this->info($lux->lux);

            $state = State::orderBy('id', 'desc')->first();
            $this->info($state->state ? 'Nu: Aan' : 'Nu: Uit');


            if (strtotime($state->created_at) < strtotime("-1 minutes")) { //maximaal 1x per kwartier schakelen
                if ($lux->lux < 900 && !$state->state) { //state = 1 als de lampen aan staan

                    $this->schakelBinnen(1);

                    $this->inform('Lampen ingeschakeld op basis van de lichtsterkte (' . $lux->lux . ')');

                } else if ($lux->lux > 1000 && $state->state) {

                    $this->schakelBinnen(0);

                    $this->inform('Lampen uitgeschakeld op basis van de lichtsterkte (' . $lux->lux . ')');

                }
            } else {
                $this->info('Niet geschakeld ivm het maximaal aantal schakelingen');
            }
        }

        //om 23:50 altijd alles uitschakelen
        if($nu == $eind) {
            $this->schakelBinnen(0);
        }


        //buitenlamp
        $sunset = date_sunset(time(), SUNFUNCS_RET_TIMESTAMP, 52.022231, 5.582037);
        $sunrise = date_sunrise(time(), SUNFUNCS_RET_TIMESTAMP, 52.022231, 5.582037, 97); //astronomical twilight

        if((time() + 1800) > $sunset
            AND time() + 1740 < $sunset ){

            $this->schakelBuiten(1);
        }

        if(time() > $sunrise
            AND time() - 100 < $sunrise){

            $this->schakelBuiten(0);
        }

    }

    private function sendMessage($message)
    {

        $request = new \cURL\Request("https://api.pushover.net/1/messages.json");
        $request->getOptions()
            ->set(CURLOPT_POSTFIELDS, array(
                "token" => "aHUQTNQVoQpPfq55NL7Ms4gU1TeQjD",
                "user" => "uqakevvKzw75btP6PoLnjkkqoqFdsw",
                "message" => $message,
            ))
            ->set(CURLOPT_RETURNTRANSFER, true);

        $response = $request->send();

        $messageDB = new Message();
        $messageDB->message = $message;
        $messageDB->save();
    }


    private function schakelBinnen($stateValue)
    {
        if($stateValue) {
            $request = new \cURL\Request('http://thuis.ronaldvinke.nl:8080/on.php');
        }else{
            $request = new \cURL\Request('http://thuis.ronaldvinke.nl:8080/off.php');
        }

        $request->getOptions()
            ->set(CURLOPT_TIMEOUT, 5)
            ->set(CURLOPT_RETURNTRANSFER, true);
        $response = $request->send();

        $this->state($stateValue);
    }

    private function schakelBuiten($stateValue)
    {
        if($stateValue) {
            $request = new \cURL\Request('http://thuis.ronaldvinke.nl:8080/buitenlampOn.php');
        }else{
            $request = new \cURL\Request('http://thuis.ronaldvinke.nl:8080/buitenlampOff.php');
        }

        $request->getOptions()
            ->set(CURLOPT_TIMEOUT, 5)
            ->set(CURLOPT_RETURNTRANSFER, true);
        $response = $request->send();

    }

    private function state($stateValue)
    {
        $state = new State();
        $state->state = $stateValue;
        $state->save();
    }

    private function inform($message)
    {
        $this->info($message);
        $this->sendMessage($message);
    }

}