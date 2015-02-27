<?php

use \Exception;
//use \adLDAP\adLDAP;

class LDAP
{
	/**
	 * Contains the LDAP configuration
	 *
	 * @var array
	 */
	protected $config;

	/**
	 * LDAP connection
	 *
	 * @var ldap link
	 */
	protected $ldap_conn;

	/**
	 * Create an instance of the LDAP class which is used to access the LDAP server
	 *
	 * @param
	 * @return void
	 */
	public function __construct()
	{
		if (!extension_loaded('ldap'))
			throw new Exception("PHP LDAP extension not loaded!");

		if (!is_array($this->config = Config::get('ldap')))
			throw new Exception("Config is not an array! Please check your app/config/ldap.php");

		//$this->ldap_conn = ldap_connect($ldap['host']) or die("Could not connect to LDAP server.");
		$prtcl = '';//'ldap://';
		if ($this->config['use_ssl'])
			$prtcl = 'ldaps://';
		if (!$this->ldap_conn = ldap_connect($prtcl . $this->config['host'], $this->config['port']))
			throw new Exception("Could not connect to LDAP host " . $this->config['host'] . ": " . ldap_error($this->ldap_conn));

		ldap_set_option($this->ldap_conn, LDAP_OPT_PROTOCOL_VERSION, $this->config['version']);
		ldap_set_option($this->ldap_conn, LDAP_OPT_REFERRALS, 0);
	}

	/**
	 * Destructor for an instance of this LDAP class, close the LDAP connection
	 *
	 * @param
	 * @return void
	 */
	public function __destruct()
	{
		ldap_close($this->ldap_conn);
	}

	/**
	 * Check if it is possible to properly connect to the LDAP server as specified in the config file
	 *
	 * @return boolean
	 */
	public function test_connection()
	{
		if ($this->config['use_tls']) {
			if (@ldap_start_tls($this->ldap_conn))
				return @ldap_bind($this->ldap_conn, $this->config['admin_username'], $this->config['admin_password']);
			else
				return false;
		} else
			return @ldap_bind($this->ldap_conn, $this->config['admin_username'], $this->config['admin_password']);
		/*try {
			ldap_bind($this->ldap_conn, $this->config['admin_username'], $this->config['admin_password']);
		} catch (ErrorException $e) {
			print ldap_error($this->ldap_conn);
			print $e;
			return false;
		}*/
	}

	/**
	 * Check if the given username exists as uid entry on the LDAP server
	 *
	 * @param string $username
	 * @return boolean
	 */
	public function user_exists($user)
	{
		//TODO
	}

	/**
	 * Try to authenticate the given credentials against the LDAP server
	 *
	 * @param string $username
	 * @param string $password
	 * @return boolean
	 */
	public function authenticate($user, $pw)
	{
		//TODO
	}

	/**
	 * Search for an user (uid) on the LDAP server and return the resulting array
	 *
	 * @param string $username
	 * @return array user query result
	 */
	public function search_user($user)
	{
		//TODO
	}

	//TODO more functions, get properties, ...?

}
