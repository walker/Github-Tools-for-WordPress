<?php
/*
* Github API helper class
* 
* Uses the github api to retrieve public gists, repos or commits for a specific user
*/
class WP_Github_Tools_API{

	static function get_token($code){
		$options = get_option(WP_Github_Tools_Options::GENERAL);
		if(is_array($options)){
			$client_id = $options['client-id'];
			$client_secret = $options['client-secret'];
			$args = array(
				'body' => array('client_id' => $client_id, 'client_secret' => $client_secret, 'code' => $code),
				'sslverify' => false
	    );
			$response = wp_remote_post('https://github.com/login/oauth/access_token', $args);
	    if(is_wp_error($response) || $response['response']['code'] != 200) {
				// todo handle error
				die('Error'.json_encode($response));
			} else {
				parse_str($response['body']);
				if(empty($access_token)){
					die(json_encode($response));
				} else {
					$options['access-token'] = $access_token;
					update_option(WP_Github_Tools_Options::GENERAL, $options);
				}
				
			}
		}			
	}

	static function get_data($url, $access_token){
		if(empty($access_token)) return array(); // do not proceed without access token

		$base = "https://api.github.com/";
		$response = wp_remote_get($base . $url. '?access_token='.$access_token, array( 'sslverify' => false ));
		$response = json_decode($response['body'], true);
		return $response;
	}

	static function get_repos($access_token) {
		return self::get_data("user/repos", $access_token);
	}

	static function get_user($access_token){
		return self::get_data("user", $access_token);
	}

	static function get_commits($user, $repo, $access_token){
		return self::get_data("repos/$user/$repo/commits", $access_token);
	}

	static function get_gists($access_token){
		return self::get_data("gists", $access_token);
	}
}
?>