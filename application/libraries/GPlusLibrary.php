<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 
/* 
*	Google Plus Library 
*/
require_once 'google-api-php-client/src/Google_Client.php';
require_once 'google-api-php-client/src/contrib/Google_PlusService.php';

class GPlusLibrary { 
	
	private $client_id; 
	private $client_secret; 
	private $developer_key; 
	private $redirect_uri; 
	private $scopes;
	
	private $Google_Client; 
	private $plus;
	
	/*
	*	Constructor
	*/
	function __construct(){
		// Load Default Google Plus API Configuration
		$CI =& get_instance();
		$CI->load->config('google_config');
		
		$this->client_id = $CI->config->item('client_id');
		$this->client_secret = $CI->config->item('client_secret'); 
		$this->developer_key = $CI->config->item('developer_key');
		$this->redirect_uri = $CI->config->item('redirect_uri');
		$this->scopes = $CI->config->item('scopes');
		 $this->client = new Google_Client();
                $this->client->setApplicationName($CI->config->item('application_name', 'googleplus'));
                $this->client->setClientId($CI->config->item('client_id', 'googleplus'));
                $this->client->setClientSecret($CI->config->item('client_secret', 'googleplus'));
                $this->client->setRedirectUri($CI->config->item('redirect_uri', 'googleplus'));
                $this->client->setDeveloperKey($CI->config->item('api_key', 'googleplus'));
                
                $this->plus = new Google_PlusService($this->client);

	}
	
	/*
	*	Set Client Id 
	*
	*	@access	public
	*	@param	string	$client_id	your application client id
	*/
	function set_client_id($client_id = ''){
		if(empty($client_id) === FALSE){ 
			$this->client_id = $client_id;
		}
	}
	
	/*
	*	Set Client Secret
	*	@access	public
	*	@param	$client_secret your application client secret
	*/
	function set_client_secret($client_secret = ''){
		if(empty($client_secret) === FALSE){
			$this->client_secret = $client_secret;
		}
	}
	
	/*
	*	Set Developer Key
	*	@access	public
	*	@param	string	$developer_key	your google developer key
	*/
	function set_developer_key($developer_key = ''){
		if(empty($developer_key) === FALSE){
			$this->developer_key = $developer_key;
		}
	}
	
	/*
	*	Set Redirect URI
	*	@access public
	*	@param string $redirect_uri	your application redirect uri to callback page
	*/
	function set_redirect_uri($redirect_uri = ''){ 
		if(empty($redirect_uri) === FALSE){
			$this->redirect_uri = $redirect_uri;
		}
	}
	
	/*
	*	Set Permisssion Scopes
	*	@access public
	*	@param string $scopes scopes permission for your application
	*/
	function set_scopes($scopes = ''){
		if(empty($scopes) === FALSE){ 
			$this->scopes = $scopes;
		}
	}
	
	/*
	*	Generate URL for Authentication 
	*	@access public
	*	@return string authentication url
	*/
	function get_auth_login_url(){
		return $this->client->createAuthUrl();
	}
	
	/*
	*	Request Access Token
	*	access public 
	*/
	function request_access_token(){
		$this->client->authenticate();
	 	$CI =& get_instance();
		$CI->load->library('session');
		$CI->session->set_userdata('access_token', $this->client->getAccessToken()); 
	}
	
	/*
	*	Set Access Token
	* 	@access public
	*	@return string access token
	*/
	function get_access_token(){
		return $this->client->getAccessToken();
	}
	
	/*
	*	Set Token 
	*	@access public
	*	@param null
	*/
	function set_token(){
		$CI =& get_instance();
		$CI->load->library('session'); 
		$this->client->setAccessToken($CI->session->userdata('access_token'));
	}
	

	
	/*
	* 	Check is user has been authenticated
	*	@access public
	*	@return boolean 
	*/
	function is_auth(){ 
		$CI =& get_instance();
		$CI->load->library('session');
		if($CI->session->userdata('access_token')){ 
			return TRUE;
		}else{
			return FALSE;
		}
	}
	
	/*
	*	Goto Authentication URL
	*	@access public 
	*	@param null
	*/
	function auth(){
		$CI =& get_instance(); 
		$CI->load->helper('url');
		redirect($this->client->createAuthUrl());
	}
	
	/*
	*	Get User Profile 
	* 	@access public
	*	@param string $user_id Google Plus User ID
	*/
	function get_user_profile($user_id = ''){
		$CI =& get_instance();
		$CI->load->library('session');
		
		if($CI->session->userdata('access_token')){
			$this->set_token();
			$CI->session->set_userdata('access_token', $this->get_access_token());
			
			if($user_id === ''){ 
			
				return $this->plus->people->get('me');
			}else{
				return $this->plus->people->get($user_id);
			}
		}else{
			return NULL;
		}
	}
	
	/*
	*	Get List All of Activites 
	*	@access public 
	*	@param 
	*			string $user_id The ID of The user to get Activities For 
	*			string $max_result maximum number of activities to includes in the response, used for paging 
									default 20, acceptable values 1 to 100
	*	@return array
	*/
	function get_list_activities($user_id = '', $max_result = '20'){
		$CI =& get_instance();
		$CI->load->library('session');
		
		if($CI->session->userdata('access_token')){
			$this->set_token();
			$CI->session->set_userdata('access_token', $this->get_access_token());
			
			$opt_max_results = array('maxResults' => $max_result);
			
			if($user_id === ''){
				return $this->plus->activities->listActivities('me', 'public', $opt_max_results);
			}else{
				return $this->plus->activities->listActivities($user_id, 'public', $opt_max_results);
			}
			
		}else{
			return NULL;
		}
	}
	
	/*
	*	Get an Activity
	*	@access public
	*	@param	string $activity_id The ID of Activity to get
	*	@return array
	*/
	function get_activity($activity_id){
		$CI =& get_instance();
		$CI->load->library('session');
		
		if($CI->session->userdata('access_token')){
			$this->set_token();
			$CI->session->set_userdata('access_token', $this->get_access_token());
			
			if($activity_id === ''){
				return 'Invalid Activity ID';
			}else{
				return $this->plus->activities->get($activity_id);
			}
			
		}else{
			return NULL;
		}
	}
}
/*	End of GPlusLibrary.php */
/*	Location: .application/libraries/GPlusLibrary.php */