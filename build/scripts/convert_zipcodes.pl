#!/usr/bin/perl

use strict;
use warnings;

print 'TRUNCATE TABLE `tplz`;', "\n";
#print 'ALTER TABLE  `tplz` ADD UNIQUE `PLZ_ORT_UNIQUE` (`cPLZ`, `cOrt`)', "\n";

foreach my $i (0 .. $#ARGV) {
	my $inputFile = $ARGV[$i];
	my $country = $2 if ($inputFile ~~ /^(.*\/)?([A-Z]{2})\.tab$/);

	open(FILE, $inputFile);
	while (<FILE>) {
		next unless $. > 1;

		chomp;
		my @cols = split("\t");
		next if (!$cols[13] || ($cols[13] != 6));

		my ($city) = split(",", $cols[3]);
		next if (!$city);

		my @zip_data = split(",", $cols[7]);
		for my $zip(@zip_data) {
			print 'REPLACE INTO tplz (cPLZ, cOrt, cLandISO) VALUES ("',$zip,'","',$city,'", "',$country,'");', "\n";
		}
	}

	close(FILE);
}
