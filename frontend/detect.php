<?php
/*
Dudko Web Panel v2.2.2
https://github.com/siarheidudko/dwpanel
(c) 2017-2018 by Siarhei Dudko.
https://github.com/siarheidudko/dwpanel/LICENSE
*/

function is_mobile() {
	        if ( empty($_SERVER['HTTP_USER_AGENT']) ) {
	                $is_mobile = false;
	        } elseif ( strpos($_SERVER['HTTP_USER_AGENT'], 'Mobile') !== false // many mobile devices (all iPhone, iPad, etc.)
	                || strpos($_SERVER['HTTP_USER_AGENT'], 'Android') !== false
	                || strpos($_SERVER['HTTP_USER_AGENT'], 'Silk/') !== false
	                || strpos($_SERVER['HTTP_USER_AGENT'], 'Kindle') !== false
	                || strpos($_SERVER['HTTP_USER_AGENT'], 'BlackBerry') !== false
	                || strpos($_SERVER['HTTP_USER_AGENT'], 'Opera Mini') !== false
	                || strpos($_SERVER['HTTP_USER_AGENT'], 'Opera Mobi') !== false ) {
	                        $is_mobile = true;
	        } else {
	                $is_mobile = false;
	        }
	
	        return $is_mobile;
	}
?>