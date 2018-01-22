<?php
session_start();
include('db.php');

$content = '

<p>email <a href="mailto:support@tradetal.com">support@tradetal.com</a></p>

<p>
You can reach us by phone<br>
+1 809 908 4172<br>
+1 829 946 1616<br> 
+52 352 132 37 72 (whatsapp)<br>
+1 650 621 0423 (whatsapp<br>
</p>

<p>irc: DALnet #jazmine</p>

<p><strong><a href="https://jazmine.io">More Support Options Online</a></strong></p>

<p><a href="/home.php">Return To Account Summary</a></p>
';

echo output($content,$layout);

