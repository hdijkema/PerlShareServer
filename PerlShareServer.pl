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
{
  chmod 0770, $command_location;
  my ($login,$pass,$uid,$gid) = getpwnam("www-data");
  my ($rlogin,$rpass,$ruid,$rgid) = getpwnam("root");
  chown $ruid, $gid, $command_location;
}
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

log_info("Killing watcher");
$watcher->kill_watcher();

log_info("Ending program");

exit 0;

###########################################################################################
# Command handling
###########################################################################################

sub handle_commands($$) {
  my $command_location = shift;
  my $storage_location = shift;
  my $go_on = 1;
  my @files = glob("$command_location/*");
  foreach my $cmd (@files) {
    
    my $contents = "";
    if (-z $cmd) {
      # do nothing
    } else {
      open my $fh, "<$cmd";
      while (my $line = <$fh>) {
        $contents .= $line;
      }
      close($fh);
    }
    
    my $cmd_file = $cmd;
    
    $cmd=~s/^$command_location//;
    $cmd=~s/^\///;
    log_info("handling '$cmd'");
    
    if ($cmd=~/^result/) {
      # do nothing
      log_info("Doing nothing: $cmd_file");
    } else {
      log_info("unlinking cmd file: $cmd_file");
      unlink($cmd_file);
    }
    
    if ($cmd=~/^result/) {
      log_info("result file found '$cmd'");
    } elsif ($cmd=~/^quit$/) {
      return 0;
    } elsif ($cmd=~/^create/) {
      create_user($storage_location, $cmd);
    } elsif ($cmd=~/^drop/) {
      drop_user($storage_location, $cmd);
    } elsif ($cmd=~/^share/) {
      share_dir($storage_location, $cmd);
    } elsif ($cmd=~/^unshare/) {
      unshare_dir($storage_location, $cmd);
    } elsif ($cmd=~/^public_key/) {
      push_key($storage_location, $command_location, $cmd, $contents);
    } elsif ($cmd=~/^exists/) {
      if (exists_user($storage_location, $cmd)) {
        open my $fh, ">$command_location/result exists ok";
        close $fh;
      } else {
        open my $fh, ">$command_location/result exists nok";
        close $fh;
      }
    } else {
      log_error("Unknown command '$cmd'");
    }
  }
  return 1;
}

sub exists_user($$) {
  my $storage_location = shift;
  my $cmd = shift;
  my ($c, $email) = split(/\s+/,$cmd);
  
  my $homedir = $storage_location."/".$email;
  my $sshdir = $homedir."/.ssh";
  if (-d $sshdir) {
    return 1;
  } else {
    return 0;
  }
}

sub create_user($$) {
  my $storage_location = shift;
  my $cmd = shift;
  my ($c, $email, $pass) = split(/\s+/,$cmd);
  
  my $homedir = $storage_location."/".$email;
  
  open my $fin, "useradd -d '$homedir' -g 'perlshare' -m -s '/bin/bash' -K UMASK=007 $email 2>&1 |";
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
  print $fout "umask 007\n";
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
  
  unlink($dest_dir);
  open my $fin, "ln -f -s '$src_dir' '$dest_dir' |";
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
  
  open my $fin, "<$src_dir/.shared_with";
  my @sharees;
  while (my $line = <$fin>) {
    $line=~s/^\s*//;
    $line=~s/\s*$//;
    push @sharees, $line;
  }
  close($fin);
  my $make = 1;
  foreach my $email (@sharees) {
    if ($email eq $email_dest) {
      $make = 0;
      last;
    }
  }
  if ($make) {
    open my $fout, ">>$src_dir/.shared_with";
    print $fout "$email_dest\n";
    close($fout);
  }
  
}

sub unshare_dir($$) {
  my $storage_location = shift;
  my $cmd = shift;
  my ($c, $email_source, $email_dest, $dir) = split(/\s+/,$cmd);
  my $homedir_source = $storage_location."/".$email_source;
  my $homedir_dest = $storage_location."/".$email_dest;
  
  my $src_dir = "$homedir_source/$dir";
  my $dest_dir = "$homedir_dest/$dir";
  
  open my $fin, "<$src_dir/.shared_with";
  my @sharees;
  while (my $line = <$fin>) {
    $line=~s/^\s*//;
    $line=~s/\s*$//;
    if ($line eq "$email_dest") { next; }
    push @sharees, $line;
  }
  close($fin);
  open my $fout, ">$src_dir/.shared_with";
  foreach my $email (@sharees) {
    print $fout "$email\n";
  }
  close($fout);
  
  unlink($dest_dir);
}

sub push_key($$$$) {
  my $storage_location = shift;
  my $command_location = shift;
  my $cmd = shift;
  my $key = shift;
  my ($c, $email, $share) = split(/\s+/, $cmd);
  my $homedir = $storage_location."/".$email;
  
  log_info("email  : $email");
  log_info("key    : $key");
  log_info("homedir: $homedir");
  log_info("share  : $share");
  log_info("keyfile: $command_location/$cmd");
  
  my ($login,$pass,$uid,$gid) = getpwnam($email);
  if (not(-d "$homedir/.ssh")) {
    mkdir("$homedir/.ssh");
    chmod(0700, "$homedir/.ssh");
    chown $uid, $gid, "$homedir/.ssh";
  }
  
  open my $fout, ">>$homedir/.ssh/authorized_keys2";
  print $fout $key;
  close($fout);
  chmod(0644, "$homedir/.ssh/authorized_keys2");
  chown $uid, $gid, "$homedir/.ssh/authorized_keys2";

  # create .count stuff
  my $sharedir = "$homedir/$share";
  if (not(-d $sharedir)) {
    mkdir($sharedir);
    chmod(0775, $sharedir);
    chown $uid, $gid, $sharedir;
  }
  if (not(-r "$sharedir/.count")) {
    open my $fh, ">$sharedir/.count";
    print $fh "-10\n";
    close($fh);
  }
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

