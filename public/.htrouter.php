<?php
/**
 *    Copyright (C) 2018 Deciso B.V.
 *
 *    All rights reserved.
 *
 *    Redistribution and use in source and binary forms, with or without
 *    modification, are permitted provided that the following conditions are met:
 *
 *    1. Redistributions of source code must retain the above copyright notice,
 *       this list of conditions and the following disclaimer.
 *
 *    2. Redistributions in binary form must reproduce the above copyright
 *       notice, this list of conditions and the following disclaimer in the
 *       documentation and/or other materials provided with the distribution.
 *
 *    THIS SOFTWARE IS PROVIDED ``AS IS'' AND ANY EXPRESS OR IMPLIED WARRANTIES,
 *    INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY
 *    AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 *    AUTHOR BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY,
 *    OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 *    SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 *    INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 *    CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 *    ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 *    POSSIBILITY OF SUCH DAMAGE.
 *
 */
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// handle local hosted files (js, css, etc)
$hosted_local_patterns = array();
$hosted_local_patterns[] = '/^\/ui\/css\/.*/';
$hosted_local_patterns[] = '/^\/ui\/fonts\/.*/';
$hosted_local_patterns[] = '/^\/ui\/js\/.*/';
$hosted_local_patterns[] = '/^\/ui\/img\/.*/';
$hosted_local_patterns[] = '/^\/ui\/themes\/.*/';
$hosted_local_patterns[] = '/^\favicon.*/';
foreach ($hosted_local_patterns as $pattern) {
    if (preg_match($pattern, $uri)) {
        if (strpos($uri, '/ui/') === 0) {
            $path = __DIR__ . substr($uri, 3);
            if (is_file($path)) {
                $tmp_ext = explode('.', strtolower($path));
                $mimeTypes = [
                    'css' => 'text/css',
                    'js'  => 'application/javascript',
                    'jpg' => 'image/jpg',
                    'png' => 'image/png',
                    'map' => 'application/json'
                ];
                if (isset($mimeTypes[$tmp_ext[count($tmp_ext)-1]])) {
                    header("Content-Type: {$mimeTypes[$tmp_ext[count($tmp_ext)-1]]}");
                }
                readfile($path);
                return true;
            }
            return false;
        }
        return false;
    }
}

// setup environment
global $DEV_WORKDIR;
$DEV_WORKDIR = getenv("DEV_WORKDIR"); // passed through from run_server

// set user to root for local testing
session_start();
$_SESSION["Username"]="root";
session_write_close();

if (preg_match("/^\/ui\/.*/", $uri)) {
    $_GET['_url'] = substr($_SERVER['REQUEST_URI'], 3);
    require_once "{$DEV_WORKDIR}/stubs/index.php";
} elseif (preg_match("/^\/api\/.*/", $uri)) {
    $_GET['_url'] = substr($_SERVER['REQUEST_URI'], 4);
    require_once "{$DEV_WORKDIR}/stubs/api.php";
} else {
    return false;
}
