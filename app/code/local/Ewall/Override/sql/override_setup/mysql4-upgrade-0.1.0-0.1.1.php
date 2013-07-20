<?php
$installer = $this;
$installer->startSetup();
$sql=<<<SQLTEXT
create table ewall_deliverymethods(delivery_id int not null auto_increment, title varchar(500) not null, price DECIMAL(13,4)unsigned not null,status int not null,methods int not null,primary key(delivery_id));
SQLTEXT;

$installer->run($sql);

$installer->endSetup();
	 
