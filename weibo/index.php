<?php
session_start();
if( isset($_REQUEST['code']) ) {
	include 'config.php';
	include 'saetv2.ex.class.php';

	$o = new SaeTOAuthV2( WB_AKEY , WB_SKEY );

	$keys = array();
	$keys['code'] = $_REQUEST['code'];
	$keys['redirect_uri'] = WB_CALLBACK_URL;
	try {
		$token = $o->getAccessToken( 'code', $keys ) ;
	} catch (OAuthException $e) {
	}
	if ($token) {
//        $_COOKIE['access_token'] = $_COOKIE['access_token'];
        $expire=time()+60*60*24*30;
        setcookie("access_token", $token['access_token'], $expire);
		$c = new SaeTClientV2( WB_AKEY , WB_SKEY , $token['access_token'] );
		$uid_get = $c->get_uid();
//		var_dump($c);
		echo json_encode($uid_get);
//		echo 'Sina_weibo_Access_token = ['. $token['access_token'] . "]<p/>Sina_weibo_Uid = [" . $uid_get['uid'] . ']';
	} else {
//        echo $_SESSION['access_token'];
        $c = new SaeTClientV2( WB_AKEY , WB_SKEY , $_COOKIE['access_token'] );
        $info = $c->rate_limit_status();
        echo json_encode($info);

    }

	if ($token) {
	    setcookie('accsess_token', $token['access_token']);
		$c = new SaeTClientV2( WB_AKEY , WB_SKEY , $token['access_token'] );
		$uid_get = $c->get_uid();
      		var_dump($uid_get);

		echo 'Sina_weibo_Access_token = ['. $token['access_token'] . "]<p/>Sina_weibo_Uid = [" . $uid_get['uid'] . ']';
	}
}
exit;
