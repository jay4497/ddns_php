<?php
require 'config.php';
require 'Ddns.php';

define('ROOT', __DIR__);
$ddns = new Ddns($config['login_token'], $config['domain'], $config['sub_domain'], $config['lang'], 'json', $config['ip_type']);
$ddns->set_record();
