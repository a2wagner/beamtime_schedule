<?php

use \Exception;

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
	}

	/**
	 * Check if the given username exists as uid entry on the LDAP server
	 *
	 * @param string $username
	 * @return boolean
	 */
	public function user_exists($user)
	{
		$search = null;
		try {
			$search = ldap_search($this->ldap_conn, $this->config['base_dn'], $this->config['uid'] . '=' . $user);
		} catch (ErrorException $e) {
			return false;
		}
		$result = ldap_get_entries($this->ldap_conn, $search);
		if ($result['count'] == 1)
			return true;
		else
			return false;
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
		return @ldap_bind($this->ldap_conn, $this->config['uid'] . '=' . $user . ',' . $this->config['base_dn'], $pw);
	}

	/**
	 * Search for an user (uid) on the LDAP server and return the resulting array
	 *
	 * @param string $username
	 * @return array user query result
	 */
	public function search_user($user)
	{
		$search = null;
		try {
			$search = ldap_search($this->ldap_conn, $this->config['base_dn'], $this->config['uid'] . '=' . $user);
		} catch (ErrorException $e) {
			echo ldap_error($this->ldap_conn);
			return null;
		}

		$result = ldap_get_entries($this->ldap_conn, $search);
		if (count($result['count']) == 1)
			return $result[0];
		else if (!$result['count'])
			return null;  // $user not found
		else
			throw new Exception("More than one user '" . $user . "' found! This should not happen...");
	}
}
