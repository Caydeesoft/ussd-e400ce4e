<?php

namespace App\Http\Controllers;

use App\Session;
use App\Subscriber;
use App\Subscription;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Log;

class UssdController extends Controller
{
    public function Request(Request $request)
    {
        try {
            $tel = $_GET['MSISDN'];
            $serviceCode = $_GET['SERVICE_CODE'];
            $ussdString = str_replace('*', '', substr($_GET['USSD_STRING'], 2));
            $sessionId = $_GET['SESSION_ID'];
            $contsess = Session::where('session_id', $sessionId)->first();
            if ($contsess) {
                //continuing
                $subs = Subscriber::where('telephone', $tel)->first();
                //get user input
                $currentlevel = $contsess->level + 1;
                $len = strlen($contsess->previoususerinput);
                $userinput = substr($ussdString, $len);
                $previoususerinput = substr($contsess->previoususerinput, $len - 1);
                $contsess->update(['level' => $currentlevel, 'userinput' => $userinput, 'previoususerinput' => $ussdString]);

                switch ($currentlevel) {
                    case 1:
                        if ($userinput != '1' && $userinput != '2') {
                            $contsess->update(['level' => 0]);
                            return response($this->conussd($this->topmenu()), 200)
                                ->header('Content-Type', 'text/plain');
                        }
                        $subs->update(['language_id' => (int)$userinput]);
                        $this->updateinput($sessionId, '1', '5');
                        return response($this->conussd($this->mainmenu((int) $userinput)), 200)
                            ->header('Content-Type', 'text/plain');
                        break;
                    case 2:
                        //answers to main menu
                        if ($userinput != '1' && $userinput != '2' && $userinput != '3' && $userinput != '4' && $userinput != '5') {
                            return response($this->conussd($this->invalid($subs->language_id, $sessionId)), 200)
                                ->header('Content-Type', 'text/plain');
                        }
                        if ($userinput == '1') {
                            //findjobs
                            $this->updateinput($sessionId, '1', '7');

                            return response($this->conussd($this->findjobs($subs->language_id)), 200)
                                ->header('Content-Type', 'text/plain');
                        }
                        if ($userinput == '2') {
                            $this->updateinput($sessionId, '1', '2');
                            return response($this->conussd($this->healtyliving($subs->language_id)), 200)
                                ->header('Content-Type', 'text/plain');
                        }
                        if ($userinput == '3') {
                            $this->updateinput($sessionId, '1', '5');
                            return response($this->conussd($this->datingtips($subs->language_id)), 200)
                                ->header('Content-Type', 'text/plain');
                        }
                        if ($userinput == '4') {
                            $this->updateinput($sessionId, '1', '3');
                            return response($this->conussd($this->farmingtips($subs->language_id)), 200)
                                ->header('Content-Type', 'text/plain');
                        }
                        if ($userinput == '5') {
                            $this->updateinput($sessionId, '1', '2');
                            return response($this->conussd($this->videos($subs->language_id)), 200)
                                ->header('Content-Type', 'text/plain');
                        }
                        if ($userinput == '6') {
                            if ($subs->language_id == 2) {
                                return response('END Asante kwa kupendezwa na kutazama sehemu yetu ya huduma ya Thamani. Piga * 207 # ili ujiandikishe na upate vidokezo juu ya anuwai.', 200)
                                    ->header('Content-Type', 'text/plain');
                            }
                            return response('END Thank you for taking interest in viewing our Value Added Service products section. Dial *207# to subscribe and get tips on the various categories.', 200)
                                ->header('Content-Type', 'text/plain');
                        }
                        break;
                    case 3:
                        switch ((int) $previoususerinput) {
                            case 1:
                                if ($userinput != '1' && $userinput != '2' && $userinput != '3' && $userinput != '4' && $userinput != '5' && $userinput != '6' && $userinput != '7') {
                                    return response($this->conussd($this->invalid($subs->language_id, $sessionId)), 200)
                                        ->header('Content-Type', 'text/plain');
                                }
                                $this->updateinput($sessionId, '1', '7');
                                if ($userinput == '1') {
                                    if ($subs->language_id == 2) {
                                        return response('CON Jiandikishe kwa vidokezo vya kazi ya Karani kwa Ksh.50 kwa mwezi.' . PHP_EOL . '1. Sawa' . PHP_EOL . '2. Ghairi', 200)
                                            ->header('Content-Type', 'text/plain');
                                    }
                                    return response('CON Subscribe to Clerical Job tips at Ksh.50 per month.' . PHP_EOL . '1. OK' . PHP_EOL . '2. Cancel', 200)
                                        ->header('Content-Type', 'text/plain');
                                }
                                if ($userinput == '2') {
                                    if ($subs->language_id == 2) {
                                        return response('CON Jiandikishe kwa vidokezo vya kazi ya Uuzaji kwa Ksh.50 kwa mwezi.' . PHP_EOL . '1. Sawa' . PHP_EOL . '2. Ghairi', 200)
                                            ->header('Content-Type', 'text/plain');
                                    }
                                    return response('CON Subscribe to Sales & Marketing Job tips at Ksh.50 per month.' . PHP_EOL . '1. OK' . PHP_EOL . '2. Cancel', 200)
                                        ->header('Content-Type', 'text/plain');
                                }
                                if ($userinput == '3') {
                                    if ($subs->language_id == 2) {
                                        return response('CON Jiandikishe kwa vidokezo vya kazi ya Huduma ya Wateja kwa Ksh.50 kwa mwezi.' . PHP_EOL . '1. Sawa' . PHP_EOL . '2. Ghairi', 200)
                                            ->header('Content-Type', 'text/plain');
                                    }
                                    return response('CON Subscribe to Customer Care Job tips at Ksh.50 per month.' . PHP_EOL . '1. OK' . PHP_EOL . '2. Cancel', 200)
                                        ->header('Content-Type', 'text/plain');
                                }
                                if ($userinput == '4') {
                                    if ($subs->language_id == 2) {
                                        return response('CON Jiandikishe kwa vidokezo vya kazi ya Usimamizi kwa Ksh.50 kwa mwezi.' . PHP_EOL . '1. Sawa' . PHP_EOL . '2. Ghairi', 200)
                                            ->header('Content-Type', 'text/plain');
                                    }
                                    return response('CON Subscribe to Admin Job tips at Ksh.50 per month.' . PHP_EOL . '1. OK' . PHP_EOL . '2. Cancel', 200)
                                        ->header('Content-Type', 'text/plain');
                                }
                                if ($userinput == '5') {
                                    if ($subs->language_id == 2) {
                                        return response('CON Jiandikishe kwa vidokezo vya kazi za NGO kwa Ksh.50 kwa mwezi.' . PHP_EOL . '1. Sawa' . PHP_EOL . '2. Ghairi', 200)
                                            ->header('Content-Type', 'text/plain');
                                    }
                                    return response('CON Subscribe to NGO Job tips at Ksh.50 per month.' . PHP_EOL . '1. OK' . PHP_EOL . '2. Cancel', 200)
                                        ->header('Content-Type', 'text/plain');
                                }
                                if ($userinput == '6') {
                                    if ($subs->language_id == 2) {
                                        return response('CON Jiandikishe kwa vidokezo vya kazi za hoteli kwa Ksh.50 kwa mwezi.' . PHP_EOL . '1. Sawa' . PHP_EOL . '2. Ghairi', 200)
                                            ->header('Content-Type', 'text/plain');
                                    }
                                    return response('CON Subscribe to Hotel Job tips at Ksh.50 per month.' . PHP_EOL . '1. OK' . PHP_EOL . '2. Cancel', 200)
                                        ->header('Content-Type', 'text/plain');
                                }
                                if ($userinput == '7') {
                                    if ($subs->language_id == 2) {
                                        return response('CON Jiandikishe kwa vidokezo vya kazi za Kuendeleza Biashara kwa Ksh.50 kwa mwezi.' . PHP_EOL . '1. Sawa' . PHP_EOL . '2. Ghairi', 200)
                                            ->header('Content-Type', 'text/plain');
                                    }
                                    return response('CON Subscribe to Business Development Job tips at Ksh.50 per month.' . PHP_EOL . '1. OK' . PHP_EOL . '2. Cancel', 200)
                                        ->header('Content-Type', 'text/plain');
                                }
                                break;
                            case 2:
                                if ($userinput != '1' && $userinput != '2') {
                                    return response($this->conussd($this->invalid($subs->language_id, $sessionId)), 200)
                                        ->header('Content-Type', 'text/plain');
                                }
                                // $contsess->update(['correctinput' => $contsess->correctinput . $userinput]);
                                if ($userinput == '1') {
                                    if ($subs->language_id == 2) {
                                        return response('CON Jiandikishe kwa vidokezo vya Kuzuia Saratani kwa Ksh.50 kwa mwezi.' . PHP_EOL . '1. Sawa' . PHP_EOL . '2. Ghairi', 200)
                                            ->header('Content-Type', 'text/plain');
                                    }
                                    return response('CON Subscribe to Tips to Prevent Cancer at Ksh.50 per month.' . PHP_EOL . '1. OK' . PHP_EOL . '2. Cancel', 200)
                                        ->header('Content-Type', 'text/plain');
                                }
                                if ($userinput == '2') {
                                    if ($subs->language_id == 2) {
                                        return response('CON Jiandikishe kwa vidokezo salama vya Mimba kwa Ksh.50 kwa mwezi.' . PHP_EOL . '1. Sawa' . PHP_EOL . '2. Ghairi', 200)
                                            ->header('Content-Type', 'text/plain');
                                    }
                                    return response('CON Subscribe to Safe Pregnancy Tips at Ksh.50 per month.' . PHP_EOL . '1. OK' . PHP_EOL . '2. Cancel', 200)
                                        ->header('Content-Type', 'text/plain');
                                }
                                break;
                            case 3:
                                if ($userinput != '1' && $userinput != '2' && $userinput != '3' && $userinput != '4' && $userinput != '5') {
                                    return response($this->conussd($this->invalid($subs->language_id, $sessionId)), 200)
                                        ->header('Content-Type', 'text/plain');
                                }

                                if ($userinput == '1') {
                                    if ($subs->language_id == 2) {
                                        return response('CON Jiandikishe kwa vidokezo Vidokezo vya Wanawake kwa Ksh.50 kwa mwezi.' . PHP_EOL . '1. Sawa' . PHP_EOL . '2. Ghairi', 200)
                                            ->header('Content-Type', 'text/plain');
                                    }
                                    return response('CON Subscribe to Women Tips at Ksh.50 per month.' . PHP_EOL . '1. OK' . PHP_EOL . '2. Cancel', 200)
                                        ->header('Content-Type', 'text/plain');
                                }
                                if ($userinput == '2') {
                                    if ($subs->language_id == 2) {
                                        return response('CON Jiandikishe kwa vidokezo Vidokezo vya Wanaume kwa Ksh.50 kwa mwezi.' . PHP_EOL . '1. Sawa' . PHP_EOL . '2. Ghairi', 200)
                                            ->header('Content-Type', 'text/plain');
                                    }
                                    return response('CON Subscribe to Men Tips at Ksh.50 per month.' . PHP_EOL . '1. OK' . PHP_EOL . '2. Cancel', 200)
                                        ->header('Content-Type', 'text/plain');
                                }
                                if ($userinput == '3') {
                                    if ($subs->language_id == 2) {
                                        return response('CON Jiandikishe kwa Vidokezo vya Ndoa kwa Ksh.50 kwa mwezi.' . PHP_EOL . '1. Sawa' . PHP_EOL . '2. Ghairi', 200)
                                            ->header('Content-Type', 'text/plain');
                                    }
                                    return response('CON Subscribe to Marriage Tips at Ksh.50 per month.' . PHP_EOL . '1. OK' . PHP_EOL . '2. Cancel', 200)
                                        ->header('Content-Type', 'text/plain');
                                }
                                if ($userinput == '4') {
                                    if ($subs->language_id == 2) {
                                        return response('CON Jiandikishe kwa Nukuu za Upendo kwa Ksh.50 kwa mwezi.' . PHP_EOL . '1. Sawa' . PHP_EOL . '2. Ghairi', 200)
                                            ->header('Content-Type', 'text/plain');
                                    }
                                    return response('CON Subscribe to Love Quotes at Ksh.50 per month.' . PHP_EOL . '1. OK' . PHP_EOL . '2. Cancel', 200)
                                        ->header('Content-Type', 'text/plain');
                                }
                                if ($userinput == '5') {
                                    if ($subs->language_id == 2) {
                                        return response('CON Jiandikishe Vidokezo vya Upendo kwa Ksh.50 kwa mwezi.' . PHP_EOL . '1. Sawa' . PHP_EOL . '2. Ghairi', 200)
                                            ->header('Content-Type', 'text/plain');
                                    }
                                    return response('CON Subscribe to Crazy Loving Tips at Ksh.50 per month.' . PHP_EOL . '1. OK' . PHP_EOL . '2. Cancel', 200)
                                        ->header('Content-Type', 'text/plain');
                                }
                                break;
                            case 4:
                                if ($userinput != '1' && $userinput != '2' && $userinput != '3') {
                                    return response($this->conussd($this->invalid($subs->language_id, $sessionId)), 200)
                                        ->header('Content-Type', 'text/plain');
                                }

                                if ($userinput == '1') {
                                    if ($subs->language_id == 2) {
                                        return response('CON Jiandikishe kwa Vidokezo vya hali ya hewa kwa Ksh.50 kwa mwezi.' . PHP_EOL . '1. Sawa' . PHP_EOL . '2. Ghairi', 200)
                                            ->header('Content-Type', 'text/plain');
                                    }
                                    return response('CON Subscribe to Weather Tips at Ksh.50 per month.' . PHP_EOL . '1. OK' . PHP_EOL . '2. Cancel', 200)
                                        ->header('Content-Type', 'text/plain');
                                }
                                if ($userinput == '2') {
                                    if ($subs->language_id == 2) {
                                        return response('CON Jiandikishe kwa Aina za mchanga & Mazao kwa Ksh.50 kwa mwezi.' . PHP_EOL . '1. Sawa' . PHP_EOL . '2. Ghairi', 200)
                                            ->header('Content-Type', 'text/plain');
                                    }
                                    return response('CON Subscribe to Soil Types & Crops at Ksh.50 per month.' . PHP_EOL . '1. OK' . PHP_EOL . '2. Cancel', 200)
                                        ->header('Content-Type', 'text/plain');
                                }
                                if ($userinput == '3') {
                                    if ($subs->language_id == 2) {
                                        return response('CON Jiandikishe kwa Vidokezo vya kilimo kwa Ksh.50 kwa mwezi.' . PHP_EOL . '1. Sawa' . PHP_EOL . '2. Ghairi', 200)
                                            ->header('Content-Type', 'text/plain');
                                    }
                                    return response('CON Subscribe to Agricultural Tips at Ksh.50 per month.' . PHP_EOL . '1. OK' . PHP_EOL . '2. Cancel', 200)
                                        ->header('Content-Type', 'text/plain');
                                }
                                break;
                                case 4:
                                    if ($userinput != '1' && $userinput != '2') {
                                        return response($this->conussd($this->invalid($subs->language_id, $sessionId)), 200)
                                            ->header('Content-Type', 'text/plain');
                                    }
    
                                    if ($userinput == '1') {
                                        if ($subs->language_id == 2) {
                                            return response('CON Jiandikishe kwa Video za Michezo.' . PHP_EOL . '1. Sawa' . PHP_EOL . '2. Ghairi', 200)
                                                ->header('Content-Type', 'text/plain');
                                        }
                                        return response('CON Subscribe to Sports Videos.' . PHP_EOL . '1. OK' . PHP_EOL . '2. Cancel', 200)
                                            ->header('Content-Type', 'text/plain');
                                    }
                                    if ($userinput == '2') {
                                        if ($subs->language_id == 2) {
                                            return response('CON Jiandikishe kwa Video za Muziki.' . PHP_EOL . '1. Sawa' . PHP_EOL . '2. Ghairi', 200)
                                                ->header('Content-Type', 'text/plain');
                                        }
                                        return response('CON Subscribe Music Videos.' . PHP_EOL . '1. OK' . PHP_EOL . '2. Cancel', 200)
                                            ->header('Content-Type', 'text/plain');
                                    }
                                break;
                            default:
                                if ($subs->language_id == 2) {
                                    return response('END Asante kwa kupendezwa na kutazama sehemu yetu ya huduma ya Thamani. Piga * 207 # ili ujiandikishe na upate vidokezo juu ya anuwai.', 200)
                                        ->header('Content-Type', 'text/plain');
                                }
                                return response('END Thank you for taking interest in viewing our Value Added Service products section. Dial *207# to subscribe and get tips on the various categories.', 200)
                                    ->header('Content-Type', 'text/plain');
                        }
                        break;
                    case 4:
                        //last - write to db
                        if ($userinput != 1 && $userinput != 2) {
                            return response($this->conussd($this->invalid($subs->language_id, $sessionId)), 200)
                                ->header('Content-Type', 'text/plain');
                        }
                        //write DB
                        $issubscribed=Subscription::where([['msisdn','=',$tel],['ussdresult','=',$ussdString]])->first();
                        if(!$issubscribed){
                            Subscription::insert([
                                'language_id'=>$subs->language_id,
                                'msisdn'=>$tel,
                                'ussdresult'=>$ussdString
                            ]);
                        }
                        if ($subs->language_id == 2) {
                            return response('END Asante kwa kujiandikisha katika sehemu yetu ya huduma ya Thamani. Utakuwa ukipokea SMS kwa shilingi 50 kwa mwezi.', 200)
                                ->header('Content-Type', 'text/plain');
                        }
                        return response('END Thank you for registering on our Value Added Service products section. You shall be receiving SMS from the system at Kshs 50 per month.', 200)
                            ->header('Content-Type', 'text/plain');
                        break;
                    default:
                        return response($this->conussd($this->invalid($subs->language_id, $sessionId)), 200)
                            ->header('Content-Type', 'text/plain');
                }
            } else {
                $subs = Subscriber::where('telephone', $tel)->first();
                //new session
                Session::insert([
                    'session_id' => $sessionId,
                    'telephone' => $tel,
                    'service_code' => $serviceCode,
                    'userinput' => '',
                    'previoususerinput' => '',
                    'level' => $subs ? 1 : 0
                ]);
                if ($subs) {
                    $this->updateinput($sessionId, '1', '5');
                    return response($this->conussd($this->mainmenu($subs->language_id)), 200)
                        ->header('Content-Type', 'text/plain');
                } else {
                    Subscriber::insert(['telephone' => $tel]);
                    return response($this->conussd($this->topmenu()), 200)
                        ->header('Content-Type', 'text/plain');
                }
            }
        } catch (Exception $e) {
            return response('END ' . $e->getMessage(), 200)
                ->header('Content-Type', 'text/plain');
        }
    }
    function conussd($str)
    {
        return 'CON ' . $str;
    }
    function endussd($str)
    {
        return 'END ' . $str;
    }
    function invalid($lang, $session)
    {
        Session::where('session_id', $session)->update(['level' => 1]);
        return $lang == 1 ? 'Sorry. Wrong Selection ' . $this->mainmenu($lang) : 'Samahani. Chaguo lako haliruhusiwi ' . $this->mainmenu($lang);
    }
    function updateinput($sessionId, $min, $max)
    {
        Session::where('session_id', $sessionId)->update(['mininput' => $min, 'maxinput' => $max]);
    }
    function topmenu()
    {
        $menu = 'Welcome to Standard Group VAS' . PHP_EOL;
        $menu .= '1. English' . PHP_EOL;
        $menu .= '2. Kiswahili';
        return $menu;
    }
    function mainmenu($lang)
    {
        $menu = 'Welcome to Standard Group VAS' . PHP_EOL;
        $menu .= '1. Find Jobs' . PHP_EOL;
        $menu .= '2. Healthy Living' . PHP_EOL;
        $menu .= '3. Dating Tips' . PHP_EOL;
        $menu .= '4. Farming Tips' . PHP_EOL;
        $menu .= '5. Videos' . PHP_EOL;
        $menu .= '6. Exit';
        if ($lang == 2) {
            $menu = 'Karibu Standard Group VAS' . PHP_EOL;
            $menu .= '1. Kutafuta Kazi' . PHP_EOL;
            $menu .= '2. Kuishi kwa Afya' . PHP_EOL;
            $menu .= '3. Vidokezo vya Kuchumbiana' . PHP_EOL;
            $menu .= '4. Vidokezo vya Ukulima' . PHP_EOL;
            $menu .= '5. Video' . PHP_EOL;
            $menu .= '6. Kutoka';
        }
        return $menu;
    }
    function findjobs($lang)
    {
        $menu = 'Standard Group VAS: Find Jobs' . PHP_EOL;
        $menu .= '1. Clerk Jobs' . PHP_EOL;
        $menu .= '2. Sales & Marketing Jobs' . PHP_EOL;
        $menu .= '3. Customer Care Jobs' . PHP_EOL;
        $menu .= '4. Admin Jobs' . PHP_EOL;
        $menu .= '5. NGO Jobs' . PHP_EOL;
        $menu .= '6. Hotel Jobs' . PHP_EOL;
        $menu .= '7. Business Development Jobs';
        if ($lang == 2) {
            $menu = 'Standard Group VAS: Kutafuta Kazi' . PHP_EOL;
            $menu .= '1. Kazi za Karani' . PHP_EOL;
            $menu .= '2. Kazi za Uuzaji' . PHP_EOL;
            $menu .= '3. Kazi za Huduma ya Wateja' . PHP_EOL;
            $menu .= '4. Kazi za Usimamizi' . PHP_EOL;
            $menu .= '5. Kazi za NGO' . PHP_EOL;
            $menu .= '6. Kazi za hoteli' . PHP_EOL;
            $menu .= '7. Kazi za Kuendeleza Biashara';
        }
        return $menu;
    }
    function healtyliving($lang)
    {
        $menu = 'Standard Group VAS: Healthy Living' . PHP_EOL;
        $menu .= '1. Prevent Cancer Tips' . PHP_EOL;
        $menu .= '2. Safe Pregnancy Tips';
        if ($lang == 2) {
            $menu = 'Standard Group VAS: Kuishi kwa Afya' . PHP_EOL;
            $menu .= '1. Vidokezo vya Kuzuia Saratani' . PHP_EOL;
            $menu .= '2. Vidokezo salama vya Mimba';
        }
        return $menu;
    }
    function datingtips($lang)
    {
        $menu = 'Standard Group VAS: Dating Tips' . PHP_EOL;
        $menu .= '1. Women Tips' . PHP_EOL;
        $menu .= '2. Men Tips' . PHP_EOL;
        $menu .= '3. Marriage Tips' . PHP_EOL;
        $menu .= '4. Love Quotes' . PHP_EOL;
        $menu .= '5. Crazy Loving Tips';
        if ($lang == 2) {
            $menu = 'Standard Group VAS: Vidokezo vya Kuchumbiana' . PHP_EOL;
            $menu .= '1. Vidokezo vya Wanawake' . PHP_EOL;
            $menu .= '2. Vidokezo vya Wanaume' . PHP_EOL;
            $menu .= '3. Vidokezo vya Ndoa' . PHP_EOL;
            $menu .= '4. Nukuu za Upendo' . PHP_EOL;
            $menu .= '5. Vidokezo vya Upendo';
        }
        return $menu;
    }
    function farmingtips($lang)
    {
        $menu = 'Standard Group VAS: Farming Tips' . PHP_EOL;
        $menu .= '1. Weather Tips' . PHP_EOL;
        $menu .= '2. Soil Types & Crops' . PHP_EOL;
        $menu .= '3. Agricultural Tips';
        if ($lang == 2) {
            $menu = 'Standard Group VAS: Vidokezo vya Ukulima' . PHP_EOL;
            $menu .= '1.Vidokezo vya hali ya hewa' . PHP_EOL;
            $menu .= '2. Aina za mchanga & Mazao' . PHP_EOL;
            $menu .= '3. Vidokezo vya kilimo';
        }
        return $menu;
    }
    function videos($lang)
    {
        $menu = 'Standard Group VAS: Videos' . PHP_EOL;
        $menu .= '1. Sports Videos' . PHP_EOL;
        $menu .= '2. Music Videos' . PHP_EOL;
        if ($lang == 2) {
            $menu = 'Standard Group VAS: Video' . PHP_EOL;
            $menu .= '1. Video za Michezo' . PHP_EOL;
            $menu .= '2. Video za Muziki' . PHP_EOL;
        }
        return $menu;
    }

    public function getussdmenus(Request $request)
    {
        //return response()->json('Response');
        $sessionid = '62323723';
        $msisdn = '254720076063';
        $servicecode = '*167#';
        $ussdstring = '1';
        try {
            $apiurl = 'http://127.0.0.1:8000/api/ussdmenus';
            $client = new Client(['auth' => ['username', 'password']]);
            $response = $client->request('GET', $apiurl, ['query' => ['sessionid' => $sessionid, "msisdn" => $msisdn, "servicecode" => $servicecode, "ussdstring" => $ussdstring]]);
            if (!$response) {
                return null;
            } else {
                $body = $response->getBody();
                $object = json_decode($body);
                return null;
            }
        } catch (Exception $ex) {
            return null;
        }
    }
    public function ussdmenus(Request $request)
    {
        //return response()->json('This are the menu');
        $headers = apache_request_headers();
        $creds = explode(':', base64_decode(str_replace('Basic ', '', $headers['Authorization'])));
        log::info(json_encode($_SERVER['QUERY_STRING']));
        log::info(json_encode($creds));
        $menu = 'CON Main Menu \n';
        $menu .= '1. Register \n';
        $menu .= '2. Feedback \n';
        $menu .= '3. Exit';

        //Log::info($creds[0]);//username
        //Log::warning($creds[1]);//password
        return response()->json($menu);
    }
}
