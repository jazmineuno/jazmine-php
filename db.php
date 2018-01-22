<?php

$wallet_port = 0;
$server_port = 0;
$pwd = '';

if (@is_array($_SESSION) && (array_key_exists('pwd',$_SESSION)))
{
	$pwd = $_SESSION['pwd'];
}

if (@is_array($_SESSION) && (array_key_exists('wp',$_SESSION)))
{
	$wallet_port = intval($_SESSION['wp']);
}

if (@is_array($_SESSION) && (array_key_exists('sp',$_SESSION)))
{
	$server_port = intval($_SESSION['sp']);
}

if (@is_array($_SESSION) && (array_key_exists('pwds',$_SESSION)) && (@is_array($_SESSION['pwds'])))
{
	$pwds = $_SESSION['pwds'];
}

$layout = file_get_contents('layout.html');
function output($content,$layout)
{
	$layout = str_replace('<!--Content-->',$content,$layout);
	return ($layout);
}

unlink('chatter.sqlite3');

if (!file_exists('chatter.sqlite3'))
{
	$db = new SQLite3('chatter.sqlite3');
	$sql = "CREATE TABLE chatter (mdate TEXT,mfrom TEXT,msg TEXT)";
	$db->query($sql);
	$sql = "CREATE TABLE pubkeys (user TEXT,pubkey TEXT)";
	$db->query($sql);
	$sql = "CREATE TABLE privkeys (user TEXT,privkey TEXT)";
	$db->query($sql);
	$sql = "CREATE TABLE stubs (user TEXT, address TEXT, salt TEXT)";
	$db->query($sql);
	$db->close();
}

$db = new SQLite3('chatter.sqlite3');
$pubkeys = array();
$sql = "SELECT * FROM pubkeys";
$res = $db->query($sql);
while ($row = $res->fetchArray())
{
	$pubkeys[$row['user']]=$row['pubkey'];
}

function get_info($sp)
{
	$host = '127.0.0.1:'.$sp;
	$uri = '/getinfo';
	
	$req=array();
	$req['jsonrpc']='2.0';
	$d=json_encode($req);
	$ch = curl_init($host.$uri);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");                                                                     
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$res = curl_exec($ch);
	return ($res);
}

function save_wallet($wp)
{
	if ($wp<1) return;
	$host = '127.0.0.1:'.$wp;
	$uri = '/json_rpc';
	$req=array();
	$req['method']='save';
	$req['jsonrpc']='2.0';
	$d=json_encode($req);
	$ch = curl_init($host.$uri);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");                                                                     
	curl_setopt($ch, CURLOPT_POSTFIELDS, $d);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Content-Length: ' . strlen($d)));
	$res = curl_exec($ch);
	return ($res);
}

function get_address($wp)
{
	if ($wp<1) return;
	$host = '127.0.0.1:'.$wp;
	$uri = '/json_rpc';
	$req=array();
	$req['method']='getAddresses';
	$req['jsonrpc']='2.0';
	$d=json_encode($req);
	$ch = curl_init($host.$uri);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");                                                                     
	curl_setopt($ch, CURLOPT_POSTFIELDS, $d);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Content-Length: ' . strlen($d)));
	$res = curl_exec($ch);
	return ($res);
}

function get_balance($wp,$address)
{
	if ($wp<1) return;
	$host = '127.0.0.1:'.$wp;
	$uri = '/json_rpc';
	$req=array();
	$req['method']='getBalance';
	$req['jsonrpc']='2.0';
	$params=array();
    $params['address']=$address;
    $req['params'] = $params;
	$d=json_encode($req);
	$ch = curl_init($host.$uri);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");                                                                     
	curl_setopt($ch, CURLOPT_POSTFIELDS, $d);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Content-Length: ' . strlen($d)));
	$res = curl_exec($ch);
	return ($res);
}

function send_transfer($wp,$address_from,$address_to,$amount)
{
	if ($wp<1) return;
	$host = '127.0.0.1:'.$wp;
	$uri = '/json_rpc';
	$req=array();
	$req['method']='sendTransaction';
	$req['jsonrpc']='2.0';
	$params=array();
	$params['anonymity']=0;
	$params['fee']=1;
	$params['addresses']=array();
    $params['addresses'][]=$address_from;
    $params['transfers']=array();
    $a = array();
    $a['address'] = $address_to;
    $a['amount'] = intval($amount);
    //$a['payment_id']=$payment_id;
    $params['transfers'][]=$a;
    //$params['paymentId']=$payment_id;
    $req['params'] = $params;
  	$d=json_encode($req);
	$ch = curl_init($host.$uri);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");                                                                     
	curl_setopt($ch, CURLOPT_POSTFIELDS, $d);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Content-Length: ' . strlen($d)));
	$res = curl_exec($ch);
	return ($res);
}

function get_unconfirmed($wp)
{
	if ($wp<1) return;
	$host = '127.0.0.1:'.$wp;
	$uri = '/json_rpc';
	$req=array();
	$req['method']='getUnconfirmedTransactionHashes';
	$req['jsonrpc']='2.0';
	$d=json_encode($req);
	$ch = curl_init($host.$uri);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");                                                                     
	curl_setopt($ch, CURLOPT_POSTFIELDS, $d);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Content-Length: ' . strlen($d)));
	$res = curl_exec($ch);
	return ($res);
}

function create_address($wp)
{
	if ($wp<1) return;
	$host = '127.0.0.1:'.$wp;
	$uri = '/json_rpc';
	$req=array();
	$req['method']='createAddress';
	$req['jsonrpc']='2.0';
	$d=json_encode($req);
	$ch = curl_init($host.$uri);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");                                                                     
	curl_setopt($ch, CURLOPT_POSTFIELDS, $d);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Content-Length: ' . strlen($d)));
	$res = curl_exec($ch);
	return ($res);
}

function get_transactions($wp,$address,$height)
{
	if ($wp<1) return;
	$host = '127.0.0.1:'.$wp;
	$uri = '/json_rpc';
	$req=array();
	$req['method']='getTransactions';
	$req['jsonrpc']='2.0';
	$params=array();
	$params['addresses']=array();
	$params['addresses'][]=$address;
	$params['firstBlockIndex']=20000;
	$params['blockCount']=$height-20000;
    $req['params'] = $params;
	$d=json_encode($req);
	$ch = curl_init($host.$uri);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");                                                                     
	curl_setopt($ch, CURLOPT_POSTFIELDS, $d);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Content-Length: ' . strlen($d)));
	$res = curl_exec($ch);
	return ($res);
}

function short_address($addr)
{
	return (substr($addr,0,12).'/'.substr($addr,strlen($addr)-12));
}

function get_private($user,$password)
{
	$db = new SQLite3('chatter.sqlite3');
	$sql = "SELECT salt FROM stubs WHERE user='".SQLite3::escapeString($user)."'";
	$res = $db->query($sql);
	$row = $res->fetchArray();
	$salt = base58_decode($row['salt']);
	$key = gen_key($password,$salt);
	$sql = "SELECT privkey FROM privkeys WHERE user='".SQLite3::escapeString($user)."'";
	$res = $db->query($sql);
	$row = $res->fetchArray();
	$priv_key = sym_decrypt_text(base58_decode($row['privkey']),$key);
	return ($priv_key);
}
	
function key_set($user,$pwd)
{
	$db = new SQLite3('chatter.sqlite3');
	$salt = random_bytes(SODIUM_CRYPTO_PWHASH_SALTBYTES);
	$box_kp = sodium_crypto_box_keypair();
	$box_pub = sodium_crypto_box_publickey($box_kp);
	$box_sec = sodium_crypto_box_secretkey($box_kp);
	$sql = "INSERT INTO pubkeys (user,pubkey) VALUES ('".SQLite3::escapeString($user)."','".base58_encode($box_pub)."')";
	$db->query($sql);
	$key = gen_key($pwd,$salt);
	$sql = "INSERT INTO privkeys (user,privkey) VALUES ('".SQLite3::escapeString($user)."','".base58_encode(sym_encrypt_text($box_sec,$key))."')";
	$db->query($sql);
	$sql = "UPDATE stubs SET salt='".base58_encode($salt)."' WHERE user='".SQLite3::escapeString($user)."'";
	$db->query($sql);
	$db->close();
	$a['pub']=$box_pub;
	$a['priv']=$box_sec;
	return ($a);
}

function create_stub($user,$pwd,$address,$pk)
{
	$a=array();
	$a['user']=$user;
	$a['pwd']=$pwd;
	$a['data']=$address;
	$a['pub']=$pk;
	$d=http_build_query($a);
	
	$ch = curl_init('https://racepi.com/xmpp/newp.php');
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");                                                                     
	curl_setopt($ch, CURLOPT_POSTFIELDS, $d);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded','Content-Length: ' . strlen($d)));
	$res = curl_exec($ch);
	return ($res);
}

function gen_key($password,$salt)
{
	$len = SODIUM_CRYPTO_SECRETBOX_KEYBYTES;
	$key = sodium_crypto_pwhash($len,$password,$salt,SODIUM_CRYPTO_PWHASH_OPSLIMIT_INTERACTIVE,SODIUM_CRYPTO_PWHASH_MEMLIMIT_INTERACTIVE);
	return ($key);
}

function sym_encrypt_text($message,$key)
{
	$nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
	$ciphertext = $nonce.sodium_crypto_secretbox($message, $nonce, $key);
	return ($ciphertext);
}

function sym_decrypt_text($message,$key)
{
	$nonce=substr($message,0,SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
	$ciphertext=substr($message,SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
	$plaintext = sodium_crypto_secretbox_open($ciphertext, $nonce, $key);
	return ($plaintext);
}

function assym_encrypt_text($priv,$pub,$msg)
{
	$kp = sodium_crypto_box_keypair_from_secretkey_and_publickey($priv,$pub);
	$nonce = random_bytes(SODIUM_CRYPTO_BOX_NONCEBYTES);
	$ciphertext = sodium_crypto_box($msg,$nonce,$kp);
	return ($nonce.$ciphertext);
}

function assym_decrypt_text($priv,$pub,$msg)
{
	$kp = sodium_crypto_box_keypair_from_secretkey_and_publickey($priv,$pub);
	$nonce = substr($msg,0,SODIUM_CRYPTO_BOX_NONCEBYTES);
	$ciphertext = substr($msg,SODIUM_CRYPTO_BOX_NONCEBYTES);
	$plaintext = sodium_crypto_box_open($ciphertext,$nonce,$kp);
	return ($plaintext);
}

/* 
 * Base58 Encode / Decode functions based on 
 * code by Stephen Hill (modified by Waitman Gobble 2018)
 * 
 * The MIT License (MIT)
 * 
 * Copyright (c) 2014 Stephen Hill <stephen@gatekiller.co.uk>
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 * 
 */

$alphabet = '123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz';

function base58_encode($string)
{

global $alphabet;

$base = strlen($alphabet);

$bytes = array_values(unpack('C*', $string));
$decimal = $bytes[0];
for ($i = 1, $l = count($bytes); $i < $l; $i++) {
	$decimal = bcmul($decimal, 256);
    $decimal = bcadd($decimal, $bytes[$i]);
}
$output = '';
while ($decimal >= $base) {
	$div = bcdiv($decimal, $base, 0);
	$mod = bcmod($decimal, $base);
	$output .= $alphabet[$mod];
	$decimal = $div;
}
if ($decimal > 0) {
	$output .= $alphabet[$decimal];
}
$output = strrev($output);
foreach ($bytes as $byte) {
	if ($byte === 0) {
		$output = $alphabet[0] . $output;
        continue;
	}
    break;
}
return $output;

}


function base58_decode($string)
{

global $alphabet;

$base = strlen($alphabet);

$indexes = array_flip(str_split($alphabet));
$chars = str_split($string);
$decimal = $indexes[$chars[0]];
for ($i = 1, $l = count($chars); $i < $l; $i++) {
$decimal = bcmul($decimal, $base);
$decimal = bcadd($decimal, $indexes[$chars[$i]]);
}
$output = '';
while ($decimal > 0) {
$byte = bcmod($decimal, 256);
$output = pack('C', $byte) . $output;
$decimal = bcdiv($decimal, 256, 0);
}
foreach ($chars as $char) {
	if ($indexes[$char] === 0) {
		$output = "\x00" . $output;
		continue;
	}
    break;
}
return $output;
}

