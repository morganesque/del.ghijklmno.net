#!/usr/bin/php
<?php

    // $command = 'curl -I https://github.com/tobie/ua-parser';
    // $command = 'curl -I http://bbc.co.uk/';
    // exec($command);
    // var_dump($output);
    
    $u = "https://github.com/tobie/ua-parser";
    
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $u);
    curl_setopt($curl, CURLOPT_NOBODY, true);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $header = curl_exec($curl);
    $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
    
    echo $code;
    
?>