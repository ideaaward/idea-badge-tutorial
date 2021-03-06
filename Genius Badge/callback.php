<?php

require 'vendor/autoload.php';

session_start();

$state = $_GET['state'];
$code = $_GET['code'];
$error = $_GET['error'];

if (isset($error)) {
  if ($error === 'login_required' || $error === 'consent_required' || $error === 'interaction_required') {
    header("Location: https://idea.org.uk/");
    exit();
  }
}

if (!isset($code)) {
   exit('Failed to get an authorization code');
}

if (isset($state) && $state !== $_SESSION['oauth2_state']) {
  session_destroy();
  exit('OAuth2 invalid state!');
}


$client = new \GuzzleHttp\Client();

$res = $client->request('POST', 'https://idea.eu.auth0.com/oauth/token', [
     'form_params' => [
          'client_id' => getenv('GENIUS_BADGE_CLIENT_ID'),
          'client_secret' => getenv('GENIUS_BADGE_CLIENT_SECRET'),
          'redirect_uri' => getenv('GENIUS_BADGE_REDIRECT_URI'),
          'code' => $code,
          'grant_type' => 'authorization_code'
     ]
]);

$json = json_decode($res->getBody());

$_SESSION['oauth2_access_token'] = $json->access_token;
$_SESSION['oauth2_id_token'] = $json->id_token;

if(isset($_SESSION['progress']))
{
  if( $_SESSION['progress'] == 2 )
  {
    header('Location: badge-x.php'); // A made up page that you would redirect them to based on the progress value
  }
}
else
{
  header('Location: badge.php'); // Start from the initial page
}