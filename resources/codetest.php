
<?php
#!/usr/bin/php 

define('TAB', "\t");
define('BR', "\n");

$data = array();
getCachedData();

$codes = array();
$codeslist = array();

foreach($data as $d)
{
    $c = $d['CODE'];
    if (isset($codes[$c])) $codes[$c]++;
    else $codes[$c] = 1;
    
    if (isset($codeslist[$c])) $codeslist[$c][] = $d['HREF'];
    else $codeslist[$c] = array($d['HREF']);
    
    if ($c == '0')
    {
        $newd[] = $d;
    }
}

arsort($codes);

$ok_codes = array(200,301,302,303,203,204);
$OK = 0;
$NOT = 0;
foreach($codes as $k=>$v)
{

    if (in_array($k,$ok_codes))
    {
        $OK += $v;
    } else {
        $NOT += $v;
    }
}
echo $OK.'<br />';
echo $NOT.'<br />';

echo '<pre>';
foreach($codes as $k=>$v)
{
    if ($k) echo $v.TAB;
}
echo '</pre>';

// var_dump($codeslist);

$data = $newd;
// checkHttpCode();

// foreach($codeslist[0] as $c)
// {
//     var_dump($c);
//     // echo '<li><div class="keysquare"></div>'.$code.'  &mdash; <strong>'.count($c).'</strong> bookmarks</li>'."\n";
// }

// $r1 = '';
// $r2 = '';
// foreach($codes as $k=>$v)
// {
//     $r1 .= $k.TAB;
//     $r2 .= $v.TAB;
// }

// echo '<pre>';
// echo $r1."\n";
// echo $r2."\n";
// echo '</pre>';

function getCachedData()
{
    global $data;
    $cache = 'data2.ser';
    $ser = join('',file($cache));
    $data = unserialize($ser);
}

function checkHttpCode()
{
    global $data;
    
    $c=0;
    foreach($data as $k=>$d)
    {        
        // if (isset($d['CODE'])) { echo 'ignore'."\n"; continue; }
        
        $u = $d['HREF'];
        
        echo $c;
        echo ' - '.$u;
        
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $u);
        curl_setopt($curl, CURLOPT_NOBODY, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 2);
        // curl_setopt($curl, CURLOPT_CONNECTTIMEOUT_MS, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        $header = curl_exec($curl);
        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
    
        echo ' - '.$code."\n";
    
        $data[$k]['CODE'] = $code;
        $c++; // if ($c == 10) break;
    }    
}

function writeDataToFile()
{
    global $data;
    $filename = 'data2.ser';
    $somecontent = serialize($data);

    // Let's make sure the file exists and is writable first.
    if (is_writable($filename)) {

        // In our example we're opening $filename in append mode.
        // The file pointer is at the bottom of the file hence
        // that's where $somecontent will go when we fwrite() it.
        if (!$handle = fopen($filename, 'w')) {
             echo "Cannot open file ($filename)";
             exit;
        }

        // Write $somecontent to our opened file.
        if (fwrite($handle, $somecontent) === FALSE) {
            echo "Cannot write to file ($filename)";
            exit;
        }

        echo "Success, wrote (\$data) to file ($filename)\n";

        fclose($handle);

    } else {
        echo "The file $filename is not writable";
    }    
}


?>