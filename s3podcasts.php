<?php
/*
Plugin Name: S3 Podcasts 
Description: A simple plugin to upload podcasts to your Amazon S3 instance and use in your Wordpress blog
Author: Bruno Leite 
Author URI: http:/bruno-leite.net
Version: 0.1
*/
session_start();

add_action('admin_menu', 's3podcasts_setup_menu');

add_action('plugins_loaded', 'get_user_info');

function get_user_info(){
	$current_user = wp_get_current_user(); 

	if (!($current_user instanceof WP_User)) { 
		return;
	} 

	$_SESSION['user_login'] = $current_user->user_login;
	
	return true;
}

function s3podcasts_setup_menu(){
	add_menu_page('S3 Podcasts', 'S3 Podcasts', 'manage_options', 's3podcasts-list', 'listPodcasts', 'http://www.myiconfinder.com/uploads/iconsets/20-20-5c3ca6ab08d98e197e0778a5c2e17599-microphone.png');
	add_submenu_page('s3podcasts-list', 'S3 Podcasts', 'S3 Podcasts', 'manage_options', 's3podcasts-list', 'listPodcasts' );
	add_submenu_page('s3podcasts-list', 'Adicionar podcast', 'Adicionar podcast', 'manage_options', 's3podcasts-add', 'addPodcasts' );
}

function listPodcasts(){
	include_once 'list.php';
}

function addPodcasts(){
	include_once 'add.php';
}

require_once('aws/aws-autoloader.php');
use Aws\S3\S3Client;

define(ACCESS_KEY, 'PUT YOUR ACCESS KEY HERE');
define(SECRET_KEY, 'PUT YOUR SECRET KEY HERE');

/**
 * Function to handle post from add.php form
 *
 * @return bool
 */
function addPodcast() {
	
	// First check if the file appears on the _FILES array
	if (empty($_FILES['podcast']['name']) || empty($_POST['title'])) {
		$_SESSION['s3podcastsmsg'] = "Erro ao fazer o upload do podcast: favor preencher os campos obrigatórios do formulário!";
		return false;
	}
	
	// file
	$fileName = $_FILES['podcast']['name'];
	$fileSource = $_FILES['podcast']['tmp_name'];
	
	// podcast data array
	$podcastData = array( 
		'title' => strlen($_POST['title']) < 3 ? 'Meu podcast sem título' : $_POST['title'], 
		'user' => $_SESSION['user_login'],
		'filename' => $_FILES['podcast']['name'],
		'date' => date('Y-m-d H:i:s') 
	);    
	
	if (!empty($_POST['description'])) {
		$podcastData['description'] = $_POST['description'];	
	}
	
	// Instantiate an Amazon S3 client.
	$s3 = S3Client::factory([
		'version' => 'latest',
		'region' => 'sa-east-1',
		'credentials' => [
			'key' => ACCESS_KEY,
			'secret' => SECRET_KEY
		]
	]);
	
	// 5 minutes
	set_time_limit(300);

	try {
		// put object to s3 bucket
		$response = $s3->putObject(array(
			'Bucket' => 'podcasts-aic',
			'Key' => $fileName,
			'SourceFile' => $fileSource
		));
		
		// store object url
		$podcastData['url'] = $response['ObjectURL'];
		
	} catch (Aws\Exception\S3Exception $e) {
		$_SESSION['s3podcastsmsg'] = "Erro ao fazer o upload do podcast: " . $e->getMessage();
		return false;
	}
	
	try {
		global $wpdb;
		// insert podcast data to database
		$wpdb->insert('wp_s3podcasts', $podcastData);
		
	} catch (Exception $e) {
		$_SESSION['s3podcastsmsg'] = "Erro ao fazer o upload do podcast: " . $e->getMessage();
		return false;
	}
	
	$_SESSION['s3podcastsmsg'] = "Podcast postado com sucesso, endereco <a href='{$response['ObjectURL']}'>{$response['ObjectURL']}</a>";
	return true; 
}

/** 
 * Function to remove podcast
 * 
 * @return bool
 */
function deletePodcast($id) {
	
	session_start();
	global $wpdb;
		   
	// check if the record exist    
	$podcast = $wpdb->get_row("SELECT id, filename FROM wp_s3podcasts WHERE id = $id");
	
	try {
		// delete object if exist
		if (!empty($podcast)) {
			$wpdb->delete('wp_s3podcasts', array('id' => $id));	
		}
	} catch (Exception $e) {
		$_SESSION['s3podcastsmsg'] = "Erro ao excluir o podcast: " . $e->getMessage();
		return false;
	} 
	
	// Instantiate an Amazon S3 client.
	$s3 = S3Client::factory([
		'version' => 'latest',
		'region' => 'sa-east-1',
		'credentials' => [
			'key' => ACCESS_KEY,
			'secret' => SECRET_KEY
		]
	]);
	
	// curl_setopt($ch, CURLOPT_TIMEOUT, 30);
	set_time_limit(300);
	
	try {
		// put object to s3 bucket
		$response = $s3->deleteObject(array(
			'Bucket' => 'podcasts-aic',
			'Key' => $podcast->filename
		));
		
	} catch (Aws\Exception\S3Exception $e) {
		$_SESSION['s3podcastsmsg'] = "Erro ao excluir o podcast: " . $e->getMessage();
		return false;
	} 
	
	$_SESSION['s3podcastsmsg'] = "O podcast foi exluído com sucesso!";
	return true;   
}

/**
 * Function to init the plugin
 *
 * @return bool
 */
function initPlugin() {
	
	global $wpdb;

	// check if wp_s3podcasts table exist    
	$result = $wpdb->get_results("SHOW TABLES LIKE 'wp_s3podcasts'");
	
	// if wp_s3podcasts table does not exist, them create it
	if (empty($result)) {
		$result = $wpdb->get_results("
			CREATE TABLE `wp_s3podcasts` (
				`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
				`title` VARCHAR(100) NOT NULL,
				`description` TEXT NULL,
				`url` VARCHAR(255) NOT NULL,
				`filename` VARCHAR(200) NOT NULL,
				`user` VARCHAR(60) NOT NULL,
				`date` DATETIME NOT NULL,
				PRIMARY KEY (`id`)
			)
			COLLATE='utf8_general_ci'
			ENGINE=MyISAM;
		");		
	}
	
	return true;
}

// init the plugin
initPlugin();

// handle form post from add.php file
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	addPodcast();
	header('Location: ' . $_SERVER['HTTP_REFERER']);
}

if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['action'])) {
	
	// single delete
	if (!empty($_GET['id'])) {
		deletePodcast($_GET['id']);
	}
	
	// mass delete
	if (!empty($_GET['media'])) {
		
		// get ids
		$ids = $_GET['media'];
		
		// delete selected podcasts
		foreach ($ids as $id) {
			deletePodcast($id);    
		}	
	}
	header('Location: ' . $_SERVER['HTTP_REFERER']);
}
