<?php
/* ------------------------------------------------------------------------

   PHPresolver - PHP DNS resolver library
                 Version 1.1b

   Copyright (c) 2001, 2002 Moriyoshi Koizumi <koizumi@ave.sytes.net>
   All Rights Reserved.

   This library is free software; you can redistribute it and/or modify it
   under the terms of the GNU Lesser General Public License as published
   by the Free Software Foundation; either version 2.1 of the License, or any
   later version.

   This library is distributed in the hope that it will be useful, but
   WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
   or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU Lesser General Public
   License for more details.

   You should have received a copy of the GNU Lesser General Public License
   along with this library; if not,
   write to the Free Software Foundation, Inc.,
   59 Temple Place, Suite 330, Boston, MA 02111-1307  USA

  ------------------------------------------------------------------------
*/

/***************************************************************************
 Description

  $_NAMESERVERS[]

    The array that contains IP addresses or domain names of name servers
    used for DNS resolution.
    If nothing is set before require()'ing this library and the script is
    not running under safe mode, the values will be prepared automatically.
    (under safe mode you have to initialize this array with correct values.)

  bool _getmxrr( string $hostname, arrayref $mxhosts, arrayref $weight );

    This function works in the same way as getmxrr(), however the
    third parameter cannot be omitted. If you need no MX preference
    information, please do like:

           _getmxrr( 'example.com', $mxhosts, ${''} );

  bool _checkdnsrr( string $hostname, string $type );

    This function works just in the same way as checkdnsrr();

 -------------------------------------------------------------------------
 Synopsis

  $_NAMESERVERS[] = '127.0.0.1';
  require_once( 'rrcompat.php' );

  if( _checkdnsrr( 'example.com' ) ) {
    print( 'MX records found' );
  } else {
    print( 'No MX records found' );
  }

 -------------------------------------------------------------------------
 Configuration

  If you are working in win32 environment and don't set $_NAMESERVERS
  manually, make sure that ipconfig.exe is within the PATH.
  ipconfig.exe is generally distributed with any Microsoft(R) Windows
  distributions except for Windows 95.
  if you have a trouble with the ipconfig.exe, please consider using
  the alternative fetchdns.exe bundled with the package.

 ***************************************************************************/

	require_once( 'DNS.php' );

	/* rewrite this path to the same as the box's configuration
	   if you run scripts on *NIX platforms */
	define( 'RESOLV_CONF_PATH', '/etc/resolv.conf' );

	/* replace with the path to fetchdns.exe if you have unexpected
	   result with ipconfig. */

	define( 'IPCONFIG_EXECUTABLE', 'ipconfig' );

	if( !isset( $_NAMESERVERS ) || !is_array( $_NAMESERVERS ) ) {
		unset( $_NAMESERVERS );
		$_NAMESERVERS = array();
		if( strncmp( PHP_OS, "WIN", 3 ) == 0 ) {
			unset( $res );
			exec( IPCONFIG_EXECUTABLE.' /all', $res );
			$cnt = count( $res );
			for( $i = 0; $i < $cnt; ++$i ) {
				if( strpos( $res[$i], 'DNS Servers' ) !== false ) {
					$_NAMESERVERS[] = trim( substr( $res[$i], strpos( $res[$i], ': ' ) + 2 ) );
					break;
				}
			}
			while( $i < $cnt && strpos( $res[++$i], ':' ) === false ) {
				$_NAMESERVERS[] = trim( $res[$i] );
			}
		} elseif( file_exists( RESOLV_CONF_PATH ) ) {
			$lines = file( RESOLV_CONF_PATH );
			for( $i = 0; $i < count( $lines ); ++$i ) {
				list( $dr, $val ) = split( '[ \t]', $lines[$i] );
				if( $dr == 'nameserver' ) {
					$_NAMESERVERS[] = rtrim( $val );
				}
			}
			unset( $lines );
		}
	}

	if( count( $_NAMESERVERS ) > 0 ) {
		$__PHPRESOLVER_RS = new DNSResolver( $_NAMESERVERS[0] );
	} else {
		$__PHPRESOLVER_RS = false;
	}

	function _getmxrr( $hostname, &$mxhosts, &$weight )
	{
		global $__PHPRESOLVER_RS;

		if( $__PHPRESOLVER_RS === false ) return false;

		$mxhosts = array(); /* added 2002/07/20 */

		$dnsname = & DNSName::newFromString( $hostname );
		$answer = & $__PHPRESOLVER_RS->sendQuery(
		  new DNSQuery(
		    new DNSRecord( $dnsname, DNS_RECORDTYPE_MX )
		  )
		);
		if( $answer === false || $answer->rec_answer === false ) {
			return false;
		} else {
			if( ( $i = count( $answer->rec_answer ) ) == 0 ) {
				return false;
			}
			while( --$i >= 0 ) {
				if( $answer->rec_answer[$i]->type == DNS_RECORDTYPE_MX ) {
					$rec = &$answer->rec_answer[$i]->specific_fields;
					$mxhosts[] = substr( $rec['exchange']->getCanonicalName(), 0, -1 );
					$weight[] = $rec['preference'];
				}
			}
		}
		return true;
	}
	function _checkdnsrr( $hostname, $type = 'MX' )
	{
		global $__PHPRESOLVER_RS;
		static $typemap = array(
			'A' => DNS_RECORDTYPE_A,
			'MX' => DNS_RECORDTYPE_MX,
			'NS' => DNS_RECORDTYPE_NS,
			'SOA' => DNS_RECORDTYPE_SOA,
			'PTR' => DNS_RECORDTYPE_PTR,
			'CNAME' => DNS_RECORDTYPE_CNAME,
			'ANY' => DNS_RECORDTYPE_ANY,
			'AAAA' => DNS_RECORDTYPE_AAAA
		);
		if( $__PHPRESOLVER_RS === false ) return false;

		$dnsname = & DNSName::newFromString( $hostname );
		if( !isset( $typemap[$type] ) ) {
			trigger_error( sprintf( "Type '%s' is not supported", $type ), E_USER_WARNING );
			return false;
		}

		$rt = $typemap[$type];
		$answer = & $__PHPRESOLVER_RS->sendQuery(
		  new DNSQuery( new DNSRecord( $dnsname, $rt ) )
		);
		if( $answer === false || empty( $answer->rec_answer ) ) {
			return false;
		}
		return true;
	}
?>

