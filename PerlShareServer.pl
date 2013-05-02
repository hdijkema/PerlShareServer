#!/usr/bin/perl
use strict;
use PerlShareCommon::Cfg;
use PerlShareCommon::Log;

###########################################################################################
# Initialize
###########################################################################################

my $config_file = shift @ARGV or die usage();

###########################################################################################
# Supporting functions
###########################################################################################

sub usage() {
  print "usage: $0 <config file>\n";
  exit 1;
}


=pod

=head1 Introduction

PerlShareServer manages users and accounts. It is started with a configuration file, e.g.:

  PerlShareServer.pl config.ini
  
In this configuration file, the storage location is configured and a command directory
is configured. PerlShareServer.pl watches this command directory for new commands, which
it executes. 

Commands are fead be a PHP script on a webserver. PerlShareServer understands the following
commands:

 create [email] [password]
 
This will create a user directory at [storage location]/[email]. It will be setup with rights 
0770.

 create-dir [email] [dir]
 
Will create a unison directory at [storage location]/[email]/[dir]. It can be synchronized
with unison.

 share [email1] [email2] [dir]
 
This will share [dir] from [email1] to [email2].

 unshare [email1] [email2] [dir]
 
This will unshare [dir] from [email1] to [email2]. It will revoke a shared directory.
However old contents will not be deleted on the local directories of [email2].

 drop-dir [email] [dir]
 
This will drop a unison synchronized directory. 

 drop [email]
 
This will remove an email acoount.





=end

