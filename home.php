<?php

session_start();

if (is_array($_REQUEST)&&(array_key_exists('pwd',$_REQUEST)))
{
	$loadpwd = $_REQUEST['pwd'];
	if ($loadpwd!='')
	{
		$_SESSION['pwd']=$loadpwd;
	}
}

if (is_array($_REQUEST)&&(array_key_exists('wp',$_REQUEST)))
{
	$load_wallet_port = intval($_REQUEST['wp']);
	if ($load_wallet_port>0)
	{
		$_SESSION['wp']=$load_wallet_port;
	}
}

if (is_array($_REQUEST)&&(array_key_exists('sp',$_REQUEST)))
{
	$load_server_port = intval($_REQUEST['sp']);
	if ($load_server_port>0)
	{
		$_SESSION['sp']=$load_server_port;
	}
}

include('db.php');

$content = '
<h1>Jazmine Blockchain</h1>
<p><a href="home.php?time='.time().'">Reload Information</a> '.gmdate('Y-m-d').'T'.gmdate('His').'Z</p>

<div style="float:right;width:200px;text-align:right;"><form method="post" action="/newaddress.php"><button type="submit">Create New Address</button></form></div>
<div style="float:left;"><h2>Accounts</h2></div>

<div class="tt" style="clear:both;">
<div class="tr"><div class="td">Address</div><div class="td r">Amount</div><div class="td r">Pending</div></div>
';

$addresses = get_address($wallet_port);
$a_addresses = json_decode($addresses,true);


foreach ($a_addresses['result']['addresses'] as $k=>$v)
{

	$balance = get_balance($wallet_port,$v);
	$a_balance = json_decode($balance,true);
	$content .= '<div class="tr"><div class="td"><a href="/trxn.php?address='.urlencode($v).'">'.short_address($v).'</a></div><div class="td r">'.$a_balance['result']['availableBalance'].'</div><div class="td r">'.$a_balance['result']['lockedAmount'].'</div></div>
';	
	
}

$content .= '
<p><em>Note: if your balance is displayed incorrectly (ie, zero) you must wait for the blockchain to synchronize.</em></p>
<h2>Unconfirmed Transactions</h2>
<div class="tt">
';

$unconfirmed = get_unconfirmed($wallet_port);
$a_unconfirmed = json_decode($unconfirmed,true);
foreach ($a_unconfirmed['result']['transactionHashes'] as $k=>$v)
{
		$content .= '<div class="tr"><div class="td"><a href="/block.php?block='.$v.'">'.$v.'</a></div></div>
		';
}
$content .= '</div>
';


$info = get_info($server_port);

$content .= '
<h2>Jazmine Blockchain Info</h2>
<div class="tt">';
$a_info = json_decode($info,true);
if (is_array($a_info))
{
foreach ($a_info as $k=>$v)
{
		$content .= '<div class="tr"><div class="td bl">'.str_replace('_',' ',$k).'</div><div class="td bl">'.$v.'</div></div>
';
}
}
$content .= '</div>
';


echo output($content,$layout);

