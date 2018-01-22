<?php
session_start();
include('db.php');

$from = $_POST['from'];
$to = $_POST['to'];
//$payment_id = $_POST['payment_id'];
$amount = $_POST['amount'];

$result = send_transfer($wallet_port,$from,$to,$amount);
$a_result = json_decode($result,true);

$content = '<h2>Transfer Details</h2>
';

if (array_key_exists('result',$a_result))
{
foreach ($a_result['result'] as $v)
{
$content .= '<p>Transaction Hash: '.$v.'</p>
';
}
} else {
	$content = '<h1>Error.</h1><p>Your transaction was not processed. Check the amount and the receiving address.</p>';
}
$content .='
<p><a href="/trxn.php?address='.htmlentities($from).'&amp;t='.time().'">Return To Transactions</a> &middot; <a href="/home.php?t='.time().'">Account Summary</a></p>
';

$nothing = save_wallet($wallet_port);

echo output($content,$layout);


