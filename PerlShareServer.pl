#!/usr/bin/perl
use strict;
use PerlShareCommon::Cfg;
use PerlShareCommon::Log;
use PerlShareCommon::WatchDirectoryTree;
use Expect;

###########################################################################################
# Initialize
###########################################################################################

my $config_file = shift @ARGV or die usage();

tie my %config, 'PerlShareCommon::Cfg', READ => $config_file;

my $storage_location = $config{locations}{storage} or die "You need to provide a storage location";
my $command_location = $config{locations}{commands} or die "You need to provide a commands directory";
my $log_location = $config{locations}{logs} or die "You need to provide a log location";
my $log_file = "$log_location/perlshare.log"; 

mkdir($command_location), if (! -d $command_location);
mkdir($storage_location), if (! -d $storage_location);
mkdir($log_location), if (! -d $log_location);

log_file($log_file);

###########################################################################################
# Main loop
###########################################################################################

my $watcher = new PerlShareCommon::WatchDirectoryTree($command_location);
my $go_on = 1;

log_info("Entering command loop");
$go_on = handle_commands($command_location, $storage_location);
while ( $go_on ) {
  my $dirs = $watcher->get_directory_changes();
  if (defined($dirs)) {
    $go_on = handle_commands($command_location, $storage_location);
  } else {
    sleep(1);
  }
}

log_info("Ending program");

exit;

###########################################################################################
# Command handling
###########################################################################################

sub handle_commands($$) {
  my $command_location = shift;
  my $storage_location = shift;
  my $go_on = 1;
  my @files = glob("$command_location/*");
  foreach my $cmd (@files) {
    unlink($cmd);
    $cmd=~s/^$command_location//;
    $cmd=~s/^\///;
    log_info("handling '$cmd'");
    if ($cmd=~/^quit$/) {
      return 0;
    } elsif ($cmd=~/^create/) {
      create_user($storage_location, $cmd);
    } elsif ($cmd=~/^drop/) {
      drop_user($storage_location, $cmd);
    } elsif ($cmd=~/^share/) {
      share_dir($storage_location, $cmd);
    } elsif ($cmd=~/^unshare/) {
      unshare_dir($storage_location, $cmd);
    } else {
      log_error("Unknown command '$cmd'");
    }
  }
  return 1;
}

sub create_user($$) {
  my $storage_location = shift;
  my $cmd = shift;
  my ($c, $email, $pass) = split(/\s+/,$cmd);
  
  my $homedir = $storage_location."/".$email;
  
  open my $fin, "useradd -d '$homedir' -g 'users' -m -s '/bin/bash' -p '$pass' -K UMASK=002 $email 2>&1 |";
  while (my $line = <$fin>) {
    $line=~s/^\s*//;
    $line=~s/\s*$//;
    log_info($line);
  }
  close($fin);
  
  open my $fout, "| chpasswd";
  print $fout "$email:$pass\n";
  close($fout);
  
  open $fout, ">>$homedir/.profile";
  print $fout "umask 002\n";
  close $fout;
  
  my $exp = Expect->new();
  $exp->raw_pty(1);
  $exp->log_stdout(0);
  $exp->spawn("su - $email -c 'ssh-keygen -t rsa'");
  $exp->send("\n");
  $exp->send("\n");
  $exp->send("\n");
  $exp->soft_close();  
}

sub drop_user($$) {
  my $storage_location = shift;
  my $cmd = shift;
  my ($c, $email) = split(/\s+/,$cmd);
  my $homedir = $storage_location."/".$email;
  
  open my $fin, "userdel -f -r $email 2>&1 |";
  while (my $line = <$fin>) {
    $line=~s/^\s*//;
    $line=~s/\s*$//;
    log_info($line);
  }
  close($fin);
}

sub share_dir($$) {
  my $storage_location = shift;
  my $cmd = shift;
  my ($c, $email_source, $email_dest, $dir) = split(/\s+/,$cmd);
  my $homedir_source = $storage_location."/".$email_source;
  my $homedir_dest = $storage_location."/".$email_dest;
  
  my $src_dir = "$homedir_source/$dir";
  my $dest_dir = "$homedir_dest/$dir";
  
  open my $fin, "ln -s '$src_dir' '$dest_dir' |";
  while (my $line = <$fin>) {
    $line=~s/^\s*//;
    $line=~s/\s*$//;
    log_info($line);
  }
  close($fin);

  open my $fin, "chown $email_dest:users $dest_dir";
  while (my $line = <$fin>) {
    $line=~s/^\s*//;
    $line=~s/\s*$//;
    log_info($line);
  }
  close($fin);
  
}

sub unshare_dir($$) {
  my $storage_location = shift;
  my $cmd = shift;
  my ($c, $email_source, $email_dest, $dir) = split(/\s+/,$cmd);
  my $homedir_source = $storage_location."/".$email_source;
  my $homedir_dest = $storage_location."/".$email_dest;
  
  my $src_dir = "$homedir_source/$dir";
  my $dest_dir = "$homedir_dest/$dir";
  
  unlink($dest_dir);
}

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

 quit
 
This will quit the server



=end

