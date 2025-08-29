<?php
/**
 * Vercel Serverless PHP Entry for RISE CRM
 * Location: api/index.php (required by Vercel)
 */

// Environment
$_ENV['CI_ENVIRONMENT'] = 'production';
$_ENV['APP_ENV'] = 'production';

$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

$root = dirname(__DIR__);
$crmRoot = $root . '/CRM';

// Serve installer assets directly
if (strpos($requestUri, '/install') === 0) {
	$path = $crmRoot . $requestUri;
	if (is_file($path)) {
		readfile($path);
		exit;
	}
}

// Serve static assets
if (strpos($requestUri, '/assets') === 0) {
	$path = $crmRoot . $requestUri;
	if (is_file($path)) {
		$ext = pathinfo($path, PATHINFO_EXTENSION);
		$mime = [
			'css' => 'text/css',
			'js' => 'application/javascript',
			'png' => 'image/png',
			'jpg' => 'image/jpeg',
			'jpeg' => 'image/jpeg',
			'gif' => 'image/gif',
			'svg' => 'image/svg+xml',
			'woff' => 'font/woff',
			'woff2' => 'font/woff2',
			'ttf' => 'font/ttf',
			'eot' => 'application/vnd.ms-fontobject'
		];
		if (isset($mime[$ext])) header('Content-Type: ' . $mime[$ext]);
		readfile($path);
		exit;
	}
}

// Serve uploaded files (best with external storage in production)
if (strpos($requestUri, '/files') === 0) {
	$path = $crmRoot . $requestUri;
	if (is_file($path)) {
		readfile($path);
		exit;
	}
}

// Fallback to main CodeIgniter front controller
require $crmRoot . '/index.php';
