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
  $Header: /cvsroot/phpresolver/PHPResolver-1.0/fetchdns/fetchdns.c,v 1.2 2002/08/14 14:50:29 amghura Exp $
*/

#include <stdio.h>
#include <stdlib.h>
#include <windows.h>
#include <iphlpapi.h>

#ifdef __BORLANDC__
#pragma argsused 
#endif

int main( int argc, char **argv )
{
	FIXED_INFO *pfi = NULL, *pnewfi;
	ULONG pfisize = sizeof(pfi);
	DWORD result;
	IP_ADDR_STRING *ipaddr;
	int retval = 0;

	do {
		if( ( pnewfi = realloc( pfi, pfisize ) ) == NULL ) {
			retval = -1;
			goto out;
		}
		pfi = pnewfi;

		result = GetNetworkParams( pfi, &pfisize );
	} while( result == ERROR_BUFFER_OVERFLOW );

	if( result != ERROR_SUCCESS ) {
		retval = 1;
		goto out;
	}

	printf( "DNS Servers: %s\n", pfi->DnsServerList.IpAddress.String );

	ipaddr = pfi->DnsServerList.Next;

	while( ipaddr ) {
		printf( "             %s\n", ipaddr->IpAddress.String );
		ipaddr = ipaddr->Next;
	}
out:
	if( pfi ) free( pfi ); 
	return retval;
}
