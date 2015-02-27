<?php

return array(
	//'account_suffix' => "@domain.tld",
	//'domain_controllers' => array("ldap.domain.tld"),  // An array of domains may be provided for load balancing.
	'host' => 'ldap.domain.tld',
	'port' => 389,  // plain: 389, ssl: 636
	'version'  => '3',  // LDAP protocol version (2 or 3)
	'base_dn' => "ou=User,dc=domain,dc=tld",
	'uid' => 'uid',  // the attribute name containg the uid number
	'admin_username' => null,
	'admin_password' => null,
	'real_primary_group' => true,  // returns the primary group (an educated guess).
	'use_ssl' => false,  // if TLS is true this MUST be false.
	'use_tls' => false,  // if SSL is true this MUST be false.
	'recursive_groups' => true,
);
