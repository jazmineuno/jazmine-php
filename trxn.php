<?php
session_start();
include('db.php');


$info = get_info($server_port);
$a_info = json_decode($info,true);
$height = $a_info['height'];

$address = $_REQUEST['address'];
if (!array_key_exists('show',$_REQUEST)) $_REQUEST['show']='';

$content = '<h1>Jazmine Blockchain</h1>

<h2>Account</h2>
<p style="background-color:#f5f5f5;padding:20px;text-align:center;border-radius:10px;">Your Public Address<br><br><strong>'.$address.'</strong></p>

<div style="background-color:#f5f5f5;padding:20px;border-radius:10px;">
<p><strong>Send $JZM</strong> <em>addresses always begin with &quot;jaz&quot;</em></p>
<form method="post" action="makesend.php">
<input type="hidden" name="from" value="'.htmlentities($address).'">
<table border="0" cellspacing="0" cellpadding="3">
<tr><td>Send To Address</em></td><td><input type="text" name="to" value="" size="104"></td></tr>
<tr><td>$JZM Amount to Send</td><td><input type="text" name="amount" size="10" value="0"> <em>$JZM does not have fractional units, ie no decimal point</em></td></tr>
<!--<tr><td>Payment Id</td><td><input type="text" name="payment_id" value="" size="24"> (optional)</td></tr>-->
<tr><td> </td><td><button type="submit">Send Tokens</button></td></tr>
</table>
</form>
</div>


<p><a href="home.php?t='.time().'">Return To Account Summary</a> &middot; <a href="/trxn.php?address='.urlencode($address).'&amp;t='.time().'&amp;show='.$_REQUEST['show'].'">Refresh This Page</a></p>
';

if ($_REQUEST['show']=='T')
{
	$content .= '
<p><em>Note: if transactions below are displayed incorrectly (ie, zero when it should not be) you must wait for the blockchain to synchronize.</em></p>
<table border="1" cellspacing="0" cellpadding="3" width="100%">
';

if ($address!='')
{
	$trnx = get_transactions($wallet_port,$address,$height);
	$a_trnx = json_decode($trnx,true);
	$trans = $a_trnx['result']['items'];
	foreach ($trans as $k=>$v)
	{
		if (count($v['transactions'])>0)
		{

foreach ($v['transactions'] as $nk=>$tx)
{
$content .= '
<tr class="bgr"><td>block</td><td colspan="7">'.$tx['blockIndex'].'</td></tr>
<tr class="bgr"><td>extra</td><td colspan="7">'.$tx['extra'].'</td></tr>
<tr class="bgr"><td>hash</td><td colspan="7">'.$tx['transactionHash'].'</td></tr>
<tr class="bgt">
<td align="right">amt</td>
<td> </td>
<td align="center">fee</td>
<td align="center">type</td>
<td align="center">pid</td>
<td align="center">state</td>
<td align="center">timestamp</td>
<td align="center">unlock</td>
</tr>
<tr class="bgt">
<td align="right">'.$tx['amount'].'</td>
<td> </td>
<td align="center">'.$tx['fee'].'</td>
<td align="center">'.$tx['isBase'].'</td>
<td align="center">'.$tx['paymentId'].'</td>
<td align="center">'.$tx['state'].'</td>
<td align="center">'.gmdate('Ymd',$tx['timestamp']).'T'.gmdate('His',$tx['timestamp']).'Z</td>
<td align="center">'.$tx['unlockTime'].'</td>
</tr>
';

foreach ($tx['transfers'] as $l=>$m)
{
	$content .= '
<tr>
<td> </td>
<td align="right">'.$m['amount'].'</td>
<td> </td>
<td align="center">'.$m['type'].'</td>
<td colspan="4">'.$m['address'].'</td>
</tr>
';
}
}


		}
	}
}
$content .= '
</table>
';
} else {
	$content .= '<p><a href="/trxn.php?address='.urlencode($address).'&amp;t='.time().'&amp;show=T">Display Transactions</a></p>';
}

echo output($content,$layout);
