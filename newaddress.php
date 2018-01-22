<?php

session_start();
include('db.php');

$content = '
<h1>Jazmine Blockchain</h1>
';

$address = create_address($wallet_port);
$a_address = json_decode($address,true);
$nothing = save_wallet($wallet_port);

$content .= '<p><br></p><p>New Address Created.</p>

<div style="padding:10px;background-color:#eee;border-radius:10px;text-align:center;">
<p><strong>'.$a_address['result']['address'].'</strong></p></div>
<p><br></p>
<p><a href="home.php?t='.time().'">Return to Summary</a></p>
';

echo output($content,$layout);
