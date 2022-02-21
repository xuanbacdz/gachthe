<?php
/** 
 * @package spins
 * @author BaoPro
 */
require_once(__DIR__ . "/cURL.class.php");
class Spins extends Class_Curl
{
    public $session;
    public $username;
    public $password;
    public $csrf;
    public $queue_type;
    public $queue_links;
    public $queue_spins;
    public $invite_code;
    public $facebook_token;

    function loadSession()
    {
        $this->url = "https://spin-cm.com/signin.php";
        $output = $this->curl_cookies();
        preg_match("#CMPROJECTFE_SESSION=(.+?) path=/#is", $output, $result);
        $cookies = $result[0];
        preg_match("#csrf_token=(.+?);#is", $output, $result);
        $csrf = $result[0];
        return ['cookies' => $csrf." ".$cookies, 'csrf' => $result[1]];
    }

    function getSession()
    {
        $this->url = "https://spin-cm.com/signin.php";

        $this->header_curl = [
            "Cookie: ".$this->session['cookies']
        ];

        $this->data_curl = 'username='.$this->username.'&password='.$this->password.'&csrf_token='.$this->session['csrf'];
        $push = $this->cUrl();

        return $push;
    }

    function view()
    {
        $this->url = "https://spin-cm.com/agent.php";

        $this->header_curl = [
            "Cookie: ".$this->session['cookies']
        ];
        
        $push = $this->curl_cookies();

        if(preg_match("#id=\"profile_username\" value=\"(.+?)\" disabled>#is", $push, $result) == 0) exit(json_encode(['error' => true, 'message' => 'login failed.']));
        $username = $result[1];
        preg_match("#<strong class=\"remaining_credit\">(.+?)</strong>#is", $push, $result);
        $credits = $result[1];
        preg_match("#<strong class=\"remaining_spins\">(.+?)</strong>#is", $push, $result);
        $spins = $result[1];
        preg_match("#<strong class=\"remaining_links\">(.+?)</strong>#is", $push, $result);
        $links = $result[1];
        preg_match("#csrf_token=(.+?);#is", $push, $result);
        $csrf = $result[1];
        return json_encode(["credits" => $credits, "spins" => $spins, "links" => $links, "username" => $username, "csrf" => $csrf]);
    }


    function create()
    {
        $this->url = "https://spin-cm.com/ajax.php?module=queues&action=create";
        
        preg_match("#csrf_token=(.+?);#is", $this->session['cookies'], $result);
        
        $this->header_curl = [
            "content-type: application/x-www-form-urlencoded; charset=UTF-8",
            "Accept: application/json, text/javascript, */*; q=0.01",
            "X-Csrf-Token: ".$result[1],
            "X-Requested-With: XMLHttpRequest",
            "Cookie: ".$this->session['cookies']
        ];

        $this->data_curl = "queue_type=".$this->queue_type."&queue_links=".$this->queue_links."&queue_spins=&invite_code=".$this->invite_code;

        $push = $this->cUrl();

        return $push;
    }

    function create_new()
    {
        $this->url = "https://spin-cm.com/ajax.php?module=queues&action=create_facebook_chain";
        
        preg_match("#csrf_token=(.+?);#is", $this->session['cookies'], $result);
        
        $this->header_curl = [
            "content-type: application/x-www-form-urlencoded; charset=UTF-8",
            "Accept: application/json, text/javascript, */*; q=0.01",
            "X-Csrf-Token: ".$result[1],
            "X-Requested-With: XMLHttpRequest",
            "Cookie: ".$this->session['cookies']
        ];

        $this->data_curl = "device_id=".$this->device_id."&user_id=".$this->user_id."&session_token=".$this->session_token."&queue_links=".$this->queue_links;
        //exit($this->data_curl);
        $push = $this->cUrl();

        return $push;
    }

    function facebook_info()
    {
        $this->url = "https://spin-cm.com/ajax.php?module=facebook&action=query";
        
        preg_match("#csrf_token=(.+?);#is", $this->session['cookies'], $result);
        
        $this->header_curl = [
            "content-type: application/x-www-form-urlencoded; charset=UTF-8",
            "Accept: application/json, text/javascript, */*; q=0.01",
            "X-Csrf-Token: ".$result[1],
            "X-Requested-With: XMLHttpRequest",
            "Cookie: ".$this->session['cookies']
        ];

        $this->data_curl = "facebook_token=".$this->facebook_token."&queue_links=".$this->queue_links;

        $push = $this->cUrl();

        return $push;
    }

    public function order($id)
    {
        $this->url = "https://spin-cm.com/ajax.php?module=queues&action=agent_query";
        
        preg_match("#csrf_token=(.+?);#is", $this->session['cookies'], $result);

        $this->header_curl = [
            "content-type: application/x-www-form-urlencoded; charset=UTF-8",
            "Accept: application/json, text/javascript, */*; q=0.01",
            "X-Csrf-Token: ".$result[1],
            "X-Requested-With: XMLHttpRequest",
            "Cookie: ".$this->session['cookies']
        ];

        $this->data_curl = "draw=16&columns%5B0%5D%5Bdata%5D=id&columns%5B0%5D%5Bname%5D=&columns%5B0%5D%5Bsearchable%5D=true&columns%5B0%5D%5Borderable%5D=true&columns%5B0%5D%5Bsearch%5D%5Bvalue%5D=&columns%5B0%5D%5Bsearch%5D%5Bregex%5D=false&columns%5B1%5D%5Bdata%5D=invite_code&columns%5B1%5D%5Bname%5D=&columns%5B1%5D%5Bsearchable%5D=true&columns%5B1%5D%5Borderable%5D=true&columns%5B1%5D%5Bsearch%5D%5Bvalue%5D=&columns%5B1%5D%5Bsearch%5D%5Bregex%5D=false&columns%5B2%5D%5Bdata%5D=name&columns%5B2%5D%5Bname%5D=&columns%5B2%5D%5Bsearchable%5D=true&columns%5B2%5D%5Borderable%5D=true&columns%5B2%5D%5Bsearch%5D%5Bvalue%5D=&columns%5B2%5D%5Bsearch%5D%5Bregex%5D=false&columns%5B3%5D%5Bdata%5D=links&columns%5B3%5D%5Bname%5D=&columns%5B3%5D%5Bsearchable%5D=true&columns%5B3%5D%5Borderable%5D=true&columns%5B3%5D%5Bsearch%5D%5Bvalue%5D=&columns%5B3%5D%5Bsearch%5D%5Bregex%5D=false&columns%5B4%5D%5Bdata%5D=processed_links&columns%5B4%5D%5Bname%5D=&columns%5B4%5D%5Bsearchable%5D=true&columns%5B4%5D%5Borderable%5D=true&columns%5B4%5D%5Bsearch%5D%5Bvalue%5D=&columns%5B4%5D%5Bsearch%5D%5Bregex%5D=false&columns%5B5%5D%5Bdata%5D=added_date&columns%5B5%5D%5Bname%5D=&columns%5B5%5D%5Bsearchable%5D=true&columns%5B5%5D%5Borderable%5D=true&columns%5B5%5D%5Bsearch%5D%5Bvalue%5D=&columns%5B5%5D%5Bsearch%5D%5Bregex%5D=false&columns%5B6%5D%5Bdata%5D=accepted_date&columns%5B6%5D%5Bname%5D=&columns%5B6%5D%5Bsearchable%5D=true&columns%5B6%5D%5Borderable%5D=true&columns%5B6%5D%5Bsearch%5D%5Bvalue%5D=&columns%5B6%5D%5Bsearch%5D%5Bregex%5D=false&columns%5B7%5D%5Bdata%5D=started_date&columns%5B7%5D%5Bname%5D=&columns%5B7%5D%5Bsearchable%5D=true&columns%5B7%5D%5Borderable%5D=true&columns%5B7%5D%5Bsearch%5D%5Bvalue%5D=&columns%5B7%5D%5Bsearch%5D%5Bregex%5D=false&columns%5B8%5D%5Bdata%5D=finished_date&columns%5B8%5D%5Bname%5D=&columns%5B8%5D%5Bsearchable%5D=true&columns%5B8%5D%5Borderable%5D=true&columns%5B8%5D%5Bsearch%5D%5Bvalue%5D=&columns%5B8%5D%5Bsearch%5D%5Bregex%5D=false&columns%5B9%5D%5Bdata%5D=total_time&columns%5B9%5D%5Bname%5D=&columns%5B9%5D%5Bsearchable%5D=true&columns%5B9%5D%5Borderable%5D=true&columns%5B9%5D%5Bsearch%5D%5Bvalue%5D=&columns%5B9%5D%5Bsearch%5D%5Bregex%5D=false&columns%5B10%5D%5Bdata%5D=status&columns%5B10%5D%5Bname%5D=&columns%5B10%5D%5Bsearchable%5D=true&columns%5B10%5D%5Borderable%5D=true&columns%5B10%5D%5Bsearch%5D%5Bvalue%5D=&columns%5B10%5D%5Bsearch%5D%5Bregex%5D=false&order%5B0%5D%5Bcolumn%5D=0&order%5B0%5D%5Bdir%5D=desc&start=0&length=25&search%5Bvalue%5D=".$id."&search%5Bregex%5D=false";
        
       $push = $this->cUrl();

        return $push;

    }
}