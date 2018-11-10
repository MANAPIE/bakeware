<?php

// Absolute URL to upload folder via HTTP.
// Will affect to client (JS) part of plugins.
// By default script is configured to automatically detect it.
// If you want to change it, do it like this:
// $config['BaseUrl'] = 'http://yoursite.com/ckeditor_or_tinymce/plugins/doksoft_uploader/userfiles/';

//// -MANAPIE-
// $config['BaseUrl'] = preg_replace('/(uploader\.php.*)/', 'userfiles/', $_SERVER['PHP_SELF']);
$config['BaseUrl'] = "/file/image";
//// END_

// Absolute or relative path to directory on the server where uploaded files will be stored.
// Used by this PHP script only.
// By default it automatically detects the directory.
// You can change it, see this example:
// $config['BaseDir'] = "/var/www/ckeditor_or_tinymce/doksoft_uploader/userfiles/";

//// -MANAPIE-
// $config['BaseDir'] = dirname(__FILE__).'/userfiles/';
$config['BasePrefix'] = '';
$config['BaseDir'] = $config['BasePrefix'].dirname(__FILE__).'/../../../../storage/app';

require_once $config['BasePrefix'].dirname(__FILE__).'/../../../../vendor/autoload.php';
$config['app']=require_once $config['BasePrefix'].dirname(__FILE__).'/../../../../bootstrap/app.php';
(new \Dotenv\Dotenv($config['BasePrefix'].dirname(__FILE__).'/../../../../'))->load();  

$config['DB_HOST']=$_ENV['DB_HOST'];
$config['DB_DATABASE']=$_ENV['DB_DATABASE'];
$config['DB_USERNAME']=$_ENV['DB_USERNAME'];
$config['DB_PASSWORD']=$_ENV['DB_PASSWORD'];
//// END_

$config['ResourceType']['Files'] = Array(
		'maxSize' => 0, 			// maxSize in bytes for uploaded files, 0 for any
		'allowedExtensions' => '*' 	// means any extensions are allowed
);

$config['ResourceType']['Images'] = Array(
		'maxSize' => 16*1024*1024, 	// maxSize in bytes for uploaded images, 0 for any
		'allowedExtensions' => 'bmp,gif,jpeg,jpg,png',
);

$config['JPEGQuality'] = 95; // Will be used for resizing JPEG images

// Some restrictions to avoid server overload / DDoS
$config['MaxImgResizeWidth'] = 2000; 
$config['MaxImgResizeHeight'] = 2000;
$config['MaxThumbResizeWidth'] = 500;
$config['MaxThumbResizeHeight'] = 500;

$config['AllowExternalWebsites'] = ''; // Crossdomain upload is disabled by default
// If you want to enable it use this code:
// $config['AllowExternalSites'] = 'http://yoursite.com';
// or to allow all websites (with caution!):
// $config['AllowExternalWebsites'] = '*';



if (substr($config['BaseUrl'], -1) !== '/')
	$config['BaseUrl'] .= '/';
if (substr($config['BaseDir'], -1) !== '/' && substr($config['BaseDir'], -1) !== '\\')
	$config['BaseDir'] .= '/';

?>