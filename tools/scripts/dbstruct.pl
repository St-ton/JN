#!/usr/bin/perl

use strict;
use warnings;

use Config::IniFiles;
use DBI;
use Env qw(HOME);
use JSON;
use Text::Trim qw(trim);

my $config = Config::IniFiles->new(-file => $HOME . "/.my.cnf")
    or die 'Could not read MySQL configuration';
my $db_host = trim($config->val('client', 'host'));
my $db_user = trim($config->val('client', 'user'));
my $db_password = trim($config->val('client', 'password'));
my $db_name = trim($ARGV[0]);

my $dbh = DBI->connect("DBI:mysql:database=$db_name;host=$db_host", $db_user, $db_password)
    or die 'Could not connect to MySQL';

my @tables = ();
my $structure = {};
my $table_query = $dbh->prepare('SHOW TABLES');
$table_query->execute or die $table_query->err_str;

while (my $table_name = $table_query->fetchrow_array()) {
    next if index($table_name, 'xplugin') != -1;
    push(@tables, $table_name);
}

foreach my $table (@tables) {
    my $column_query = $dbh->prepare("SHOW COLUMNS FROM $table");
    $column_query->execute or die $column_query->err_str;

    my @columns = ();
    while (my $column = $column_query->fetchrow_hashref()) {
        push(@columns, $column->{'Field'});
    }

    @{$structure->{$table}} = @columns;
}

print encode_json($structure);
