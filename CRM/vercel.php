<?php
/**
 * Vercel Entry Point for RISE CRM
 * This file handles routing for Vercel deployment
 */

// Set environment
$_ENV['CI_ENVIRONMENT'] = 'production';
$_ENV['APP_ENV'] = 'production';

// Handle Vercel routing
$request_uri = $_SERVER['REQUEST_URI'] ?? '/';
$request_method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// Route specific paths
if (strpos($request_uri, '/install') === 0) {
    // Handle installer routes
    $install_path = __DIR__ . '/install' . substr($request_uri, 8);
    if (file_exists($install_path)) {
        include $install_path;
        exit;
    }
}

if (strpos($request_uri, '/assets') === 0) {
    // Handle static assets
    $asset_path = __DIR__ . $request_uri;
    if (file_exists($asset_path)) {
        $extension = pathinfo($asset_path, PATHINFO_EXTENSION);
        $mime_types = [
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
        
        if (isset($mime_types[$extension])) {
            header('Content-Type: ' . $mime_types[$extension]);
        }
        
        readfile($asset_path);
        exit;
    }
}

if (strpos($request_uri, '/files') === 0) {
    // Handle file uploads
    $file_path = __DIR__ . $request_uri;
    if (file_exists($file_path)) {
        $extension = pathinfo($file_path, PATHINFO_EXTENSION);
        $mime_types = [
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif'
        ];
        
        if (isset($mime_types[$extension])) {
            header('Content-Type: ' . $mime_types[$extension]);
        }
        
        readfile($file_path);
        exit;
    }
}

// Include the main CodeIgniter application
require_once __DIR__ . '/index.php';
