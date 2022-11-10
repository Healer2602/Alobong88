<?php

/*
 * CKFinder Configuration File
 *
 * For the official documentation visit https://ckeditor.com/docs/ckfinder/ckfinder3-php/
 */

/*============================ PHP Error Reporting ====================================*/
// https://ckeditor.com/docs/ckfinder/ckfinder3-php/debugging.html

// Production
error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
ini_set('display_errors', 0);

// Development
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

$params = array_merge(
	require(__DIR__ . '/../../../common/config/params.php'),
	require(__DIR__ . '/../../../common/config/params-local.php')
);

/*============================ General Settings =======================================*/
// https://ckeditor.com/docs/ckfinder/ckfinder3-php/configuration.html

$config = [];

/*============================ Enable PHP Connector HERE ==============================*/
// https://ckeditor.com/docs/ckfinder/ckfinder3-php/configuration.html#configuration_options_authentication

$config['authentication'] = function (){
	return TRUE;
};

/*============================ License Key ============================================*/
// https://ckeditor.com/docs/ckfinder/ckfinder3-php/configuration.html#configuration_options_licenseKey

$config['licenseName'] = $_SERVER['SERVER_NAME'] ?? '';
$config['licenseKey']  = $params['media.license'] ?? '';

/*============================ CKFinder Internal Directory ============================*/
// https://ckeditor.com/docs/ckfinder/ckfinder3-php/configuration.html#configuration_options_privateDir

$config['privateDir'] = [
	'backend' => 'default',
	'tags'    => '.ckfinder/tags',
	'logs'    => '.ckfinder/logs',
	'cache'   => '.ckfinder/cache',
	'thumbs'  => '.ckfinder/cache/thumbs',
];

/*============================ Images and Thumbnails ==================================*/
// https://ckeditor.com/docs/ckfinder/ckfinder3-php/configuration.html#configuration_options_images

$config['images'] = [
	'maxWidth'  => 1600,
	'maxHeight' => 1200,
	'quality'   => 80,
	'sizes'     => [
		'small'  => ['width' => 480, 'height' => 320, 'quality' => 80],
		'medium' => ['width' => 600, 'height' => 480, 'quality' => 80],
		'large'  => ['width' => 800, 'height' => 600, 'quality' => 80]
	]
];

/*=================================== Backends ========================================*/
// https://ckeditor.com/docs/ckfinder/ckfinder3-php/configuration.html#configuration_options_backends

$config['backends'][] = [
	'name'               => 'default',
	'adapter'            => 'local',
	'baseUrl'            => $params['file.public_url'],
	'root'               => $params['file.path'],
	'chmodFiles'         => 0777,
	'chmodFolders'       => 0755,
	'filesystemEncoding' => 'UTF-8',
];

/*================================ Resource Types =====================================*/
// https://ckeditor.com/docs/ckfinder/ckfinder3-php/configuration.html#configuration_options_resourceTypes

$config['defaultResourceTypes'] = 'Files,Images,Videos';

$config['resourceTypes'][] = [
	'name'              => 'Files', // Single quotes not allowed.
	'directory'         => 'files',
	'maxSize'           => 0,
	'allowedExtensions' => '7z,aiff,asf,avi,bmp,csv,doc,docx,fla,flv,gif,gz,gzip,jpeg,jpg,mid,mov,mp3,mp4,mpc,mpeg,mpg,ods,odt,pdf,png,ppt,pptx,qt,ram,rar,rm,rmi,rmvb,rtf,sdc,swf,sxc,sxw,tar,tgz,tif,tiff,txt,vsd,wav,wma,wmv,xls,xlsx,zip',
	'deniedExtensions'  => '',
	'backend'           => 'default'
];

$config['resourceTypes'][] = [
	'name'              => 'Images',
	'directory'         => 'images',
	'maxSize'           => 0,
	'allowedExtensions' => 'bmp,gif,jpeg,jpg,png',
	'deniedExtensions'  => '',
	'backend'           => 'default'
];

$config['resourceTypes'][] = [
	'name'              => 'Videos',
	'directory'         => 'videos',
	'maxSize'           => 0,
	'allowedExtensions' => 'mp4,webm,mov',
	'deniedExtensions'  => '',
	'backend'           => 'default'
];

/*================================ Access Control =====================================*/
// https://ckeditor.com/docs/ckfinder/ckfinder3-php/configuration.html#configuration_options_roleSessionVar

$config['roleSessionVar'] = 'CKFinder_UserRole';

// https://ckeditor.com/docs/ckfinder/ckfinder3-php/configuration.html#configuration_options_accessControl
$config['accessControl'][] = [
	'role'         => '*',
	'resourceType' => '*',
	'folder'       => '/',

	'FOLDER_VIEW'   => TRUE,
	'FOLDER_CREATE' => TRUE,
	'FOLDER_RENAME' => TRUE,
	'FOLDER_DELETE' => TRUE,

	'FILE_VIEW'   => TRUE,
	'FILE_CREATE' => TRUE,
	'FILE_RENAME' => TRUE,
	'FILE_DELETE' => TRUE,

	'IMAGE_RESIZE'        => TRUE,
	'IMAGE_RESIZE_CUSTOM' => TRUE
];


/*================================ Other Settings =====================================*/
// https://ckeditor.com/docs/ckfinder/ckfinder3-php/configuration.html

$config['overwriteOnUpload']        = FALSE;
$config['checkDoubleExtension']     = TRUE;
$config['disallowUnsafeCharacters'] = FALSE;
$config['secureImageUploads']       = TRUE;
$config['checkSizeAfterScaling']    = TRUE;
$config['htmlExtensions']           = ['html', 'htm', 'xml', 'js'];
$config['hideFolders']              = ['.*', 'CVS', '__thumbs'];
$config['hideFiles']                = ['.*'];
$config['forceAscii']               = FALSE;
$config['xSendfile']                = FALSE;

// https://ckeditor.com/docs/ckfinder/ckfinder3-php/configuration.html#configuration_options_debug
$config['debug'] = FALSE;

/*==================================== Plugins ========================================*/
// https://ckeditor.com/docs/ckfinder/ckfinder3-php/configuration.html#configuration_options_plugins

$config['pluginsDirectory'] = __DIR__ . '/plugins';
$config['plugins']          = [];

/*================================ Cache settings =====================================*/
// https://ckeditor.com/docs/ckfinder/ckfinder3-php/configuration.html#configuration_options_cache

$config['cache'] = [
	'imagePreview' => 24 * 3600,
	'thumbnails'   => 24 * 3600 * 365,
	'proxyCommand' => 0
];

/*============================ Temp Directory settings ================================*/
// https://ckeditor.com/docs/ckfinder/ckfinder3-php/configuration.html#configuration_options_tempDirectory

$config['tempDirectory'] = sys_get_temp_dir();

/*============================ Session Cause Performance Issues =======================*/
// https://ckeditor.com/docs/ckfinder/ckfinder3-php/configuration.html#configuration_options_sessionWriteClose

$config['sessionWriteClose'] = TRUE;

/*================================= CSRF protection ===================================*/
// https://ckeditor.com/docs/ckfinder/ckfinder3-php/configuration.html#configuration_options_csrfProtection

$config['csrfProtection'] = TRUE;

/*===================================== Headers =======================================*/
// https://ckeditor.com/docs/ckfinder/ckfinder3-php/configuration.html#configuration_options_headers

$config['headers'] = [];

/*============================== End of Configuration =================================*/

// Config must be returned - do not change it.
return $config;
