<?php


/* 
 * Base58 Encode / Decode functions based on 
 * code by Stephen Hill (modified)
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


function base58_encode($string)
{

$alphabet = '123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz';
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

$alphabet = '123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz';
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



$box_kp = sodium_crypto_box_keypair();
$box_sp = sodium_crypto_sign_keypair();

$box_pub = sodium_crypto_box_publickey($box_kp);
$box_sec = sodium_crypto_box_secretkey($box_kp);


$t1 = base58_encode($box_pub);
$t2 = base58_decode($t1);

echo base58_encode($box_pub);

if ($t2==$box_pub)
{
	echo '<br>'.strlen($t2).' '.strlen($box_pub).'<br>Equal<br>';
} else {
	echo '<br>'.strlen($t2).' '.strlen($box_pub).'<br>Not Equal<br>';
}


echo '<pre>';
$t=dns_get_record('vc.jzm.io',DNS_A);
print_r($t);
echo '</pre>';


