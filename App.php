<?php
require 'config.php';
require 'Ddns.php';

$ddns = new Ddns($config['login_token'], $config['domain'], $config['sub_domain'], $config['lang']);
$ddns->set_record();