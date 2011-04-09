<?php
/*
 * Copyright (c) 2011 Andrew E. Bruno <aeb@qnot.org> 
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

require_once('lib/config.php');
require_once('lib/export-docbook.php');

// Include WP API 
include_once(Config::$WP_PATH.'/wp-config.php');
include_once(Config::$WP_PATH.'/wp-load.php');
include_once(Config::$WP_PATH.'/wp-includes/wp-db.php');

$opts = getopt("habo:");

if(array_key_exists('h', $opts) || 
   (!array_key_exists('a', $opts) &&
    !array_key_exists('b', $opts))) {
    echo "usage: php wp2docbook.php \n";
    echo "   -h print help message\n";
    echo "   -b output using DocBook Book (<chapter/><section/> DEFAULT)\n";
    echo "   -a output using DocBook Articles (<articles/>)\n";
    echo "   -o output file (defaults to STDOUT)\n";
    exit(1);
}

$outfile = array_key_exists('o', $opts) ? $opts['o'] : 'php://output';

if(array_key_exists('a', $opts)) {
    ExportDocbook::outputArticles($wpdb, $outfile);
} else {
    ExportDocbook::outputBook($wpdb, $outfile);
}

?>
