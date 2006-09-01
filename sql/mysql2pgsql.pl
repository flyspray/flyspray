#!/usr/bin/perl
# Usage: mysql2pgsql.pl < flyspray.mysql > flyspray.pgsql
# 
# WARNING: this migration script was tested with flyspray-0.9.7.mysql ONLY!
# It IS NOT meant to be a general mysql2pgsql converter (but may be in the
# future ;)
# 
use strict;
my $file;

while (<>) {
    if (($_ !~ m/^\s*$/) && ($_ !~ m/;\s*$/)) {
	chomp;
	$_ .= ' ';
    }
    $file .= $_;
}

my @lines = split /\n/, $file;

foreach my $line (@lines) {
    $line =~ s/`//g;

    $line = createTable($line) if ($line =~ m/create\s+table\s+/i);
    $line = alterTableAddColumn($line) 
    if ($line =~ m/alter\s+table\s+\w+\s+add/i);
    $line = alterTableChangeColumn($line) 
    if ($line =~ m/alter\s+table\s+\w+\s+change/i);
    $line = update($line)
    if ($line =~ m/update\s+\w+\s+set/i);
    $line = deleteFrom($line)
    if ($line =~ m/delete\s+from\s+\w+/i);

    $line = beautify($line);
    
    print "$line\n";
}

sub beautify {
    local $_ = shift;
    s/;;/;/g;
    s/ +;/;/;
    s/\( +/(/g;
    s/ +\)/)/g;
    s/  / /g;
    s/ , /, /g;
    return $_;
}


sub commonTypeConversion {
    my @cols = @_;
    
    # .*int() => numeric
    @cols = map {s/\s\w+int\s*\(/ NUMERIC\(/i if /\s\w+int/i; $_} @cols;
    # varchar => text
    @cols = map {s/\svarchar\s*\(.*?\)/ TEXT/i if /\svarchar/i; $_} @cols;
    # longtext => text
    @cols = map {s/\slongtext/ TEXT/i if /\slongtext/i; $_} @cols;
    
    return @cols;
}

# SQL: DELETE FROM ...
sub deleteFrom {
    my $def = shift;

    $def =~ s/limit\s+\d+//i;
    
    return $def;
}

# SQL: UPDATE ... SET ... = ..., ... = ...
sub update {
    my $def = shift;
    
    $def =~ s/limit\s+\d+//i;
    $def =~ s/,/,\n\t/g;
    
    return $def;
}

# SQL: ALTER TABLE ... CHANGE ... ...
sub alterTableChangeColumn {
    my $def = shift;

    if ($def =~ m/alter\s+table\s+(\w+)\s+change\s+(\w+)\s+(\w+)\s+(.*)/i) {
	my ($table, $oldcol, $newcol, $rest) = ($1, $2, $3, $4);
	$def = '';
	$def .= alterTableAddColumn("ALTER TABLE $table ADD $newcol $rest;");
	$def .= "\nUPDATE $table SET $newcol = $oldcol;";
	$def .= "\nALTER TABLE $table DROP COLUMN $oldcol;";
    }
    
    return $def;
}

# SQL: ALTER TABLE ... ADD ... 
sub alterTableAddColumn {
    my $def = (commonTypeConversion(shift))[0];
    my $notnull;
    my $default;
   
    if ($def =~ m/ALTER\s+TABLE\s+(\w+)\s+ADD\s+(\w+)/i) {
	my ($table, $column) = ($1, $2);
	$def =~ s/AFTER\s+"?\w+"?//i;

	if ($def =~ s/DEFAULT\s+('.*?')//i) {
	    $def .= "\nALTER TABLE $table ALTER $column SET DEFAULT $1;";
	    $def .= "\nUPDATE $table SET $column = $1 WHERE $column IS NULL;";
	}
	
	$def .= "\nALTER TABLE $table ALTER $column SET NOT NULL;" 
	if ($def =~ s/not null//i);

    }
    
    return $def; 
}

# SQL: CREATE TABLE ... ( ... )
sub createTable {
    my $def = shift;

    if ($def =~ m/(create\s+table\s+)(if\s+not\s+exists\s+)?(\w+)\s+\((.*)\)\s*type=\w+\s+(comment=.*\s+)?auto_increment=(\d+)/i) {
	my ($pre, $dummy1, $table_name, $cols, $dummy2, $inc) = ($1, $2, $3, $4, $5, $6);
	my $autoincrement_column;
	my @cols = split /\s*,\s*/, $cols;
	# spaces before and after
	@cols = map {s/(^\s+|\s+$)//g;$_} @cols;
	
	@cols = commonTypeConversion(@cols);
	
	@cols = map {
	    if (/(\w+)\s+([\w\d\(\)]+)(.*)\s+auto_increment/) {
		my ($col, $type, $rest) = ($1, $2, $3);
		$autoincrement_column = $col;
		s/(\w+)\s+([\w\d()]+)(.*)\s+auto_increment/$1 INT8 $3 DEFAULT nextval('"${table_name}_${autoincrement_column}_seq"'::text)/;
	    }
	    $_;
       	} @cols;
	
	$def = "$pre $table_name (\n\t".join(",\n\t", @cols)."\n);";
	if (defined $autoincrement_column) {
	    $def = "CREATE SEQUENCE \"${table_name}_${autoincrement_column}_seq\" START WITH $inc;\n$def";
	}
    }
    
    return $def;
}
