#! /usr/bin/perl

use LWP::UserAgent;
use HTTP::Request::Common 'POST';

print LWP::UserAgent
  ->new
  ->request(
            POST $ARGV[0],
            Content_Type => 'form-data',
            Content      => [
                             output => 'xml',
                             uploaded_file => [$ARGV[1]],
                            ]
           )->as_string;


