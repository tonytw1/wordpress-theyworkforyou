<?php 
/*  Copyright 2009  Philip John Ltd  (email : talkto@philipjohn.co.uk)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the Affero General Public License as published
    by the Affero Inc; either version 2 of the License, or (at your
    option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the Affero General Public License
    along with this program; if not, see http://www.affero.org/oagpl.html
*/


/*
 Provides functions for talking to the TheyForForYou api.
 Caches calls for one hour. Depends on the md5 hash function.
*/


// A valid TWfY API key is a 24 character sequence of upper and lowercase letters only.
function validateApiKeyFormat($api_key) {
	if ($api_key) {
		if (preg_match("/^[a-z|A-Z]{24}$/", $api_key)) {
			return true;
		}
	}
	return false;
}


// Load the MPs XML and use it to generate a sorted list of MPs.
function getMpsList($api_key) {
	$xml = getCachedApiCall(getMpsListApiUrl($api_key));
    $MPs = array();
    foreach ($xml->match as $MP){
        $MPid = (string )$MP->person_id;
        $MPname = (string )$MP->name;
        $MPs[$MPid] = $MPname;
    }
    asort($MPs); // actually sort it.
    return $MPs;		
}

// Load an XML object representing this persons activity
function getActivityXmlForPerson($person_id, $api_key) {
	return getCachedApiCall(getPersonsActivityApiUrl($person_id, $api_key));
}


function getMpsListApiUrl($api_key) {
	return 'http://theyworkforyou.com/api/getMPs?key='.$api_key.'&output=xml';
}

function getPersonsActivityApiUrl($person_id, $api_key) {
	return 'http://www.theyworkforyou.com/api/getHansard?key='.$api_key.'&output=xml&person='.$person_id;
}


// Return API XML for a given url from a local file cache if possible;
// make a direct call of no cached copy is available
function getCachedApiCall($api_url) {
	$cache_ttl_in_seconds = 3600;	// Cache API calls for 1 hour
	$cached_file_name = getCacheFileName($api_url);
	
	if (file_exists($cached_file_name)) { 
		$cached_file_age = time() - filemtime($cached_file_name);
		if ($cached_file_age < $cache_ttl_in_seconds) {
			// A recent cached copy of this call exists; use it instead of calling TWFY
			return simplexml_load_file($cached_file_name);
		}
	}
	
	$xml = getUnCachedApiCall($api_url);
	file_put_contents($cached_file_name, $xml->asXML());
	return $xml;
}

// Make a direct XML call to TWFY
function getUnCachedApiCall($api_url) {	
	return simplexml_load_file($api_url); // Load XML directly from TWFY	
}

// Calculate the file path to cache a given api url on under the PHP tmp path
function getCacheFileName($api_url) {
	return $temp_file = sys_get_temp_dir().'/twft_'.md5($api_url).'.xml';	
}
?>