<?php

define('TAB', "\t");
define('BR', "\n");

$data = array();
$tagscore = array();
$tagurls = array();
$tagnumbs = array();
$pop_urls = array();
$pop_url_store = array();

// first month-year I had bookmarks (hardcoded as it'll never change).
$first_my = '2005-02';
// turn that into a timet number.
$first_time = strtotime($first_my);
// check that timet number is correct.
$first_my = date('Y-M',$first_time); 

// Array for all the month-years we'll be charting.
$all_m_y = array();
while($first_time < time())
{
    $first_time += 60*60*24*30;
    $first_my = date('Y-M',$first_time);    
    $all_m_y[] = $first_my;
}

getCachedData();    // creates $data
makeTagData();      // creates $tagscore & $tagurls
createTagNumbers(); // creates $tagnumbs
createPopUrls();    // creates $pop_urls & $pop_url_store


$NUMBER_OF_BOOKMARKS = count($data);
echo '<p>I have '.$NUMBER_OF_BOOKMARKS.' bookmarks.</p>'.BR;    

    
$total = 0; $used = 0;
foreach($tagnumbs as $k=>$taglist)
{
    foreach($taglist as $t)
    {
        $total++;
        $used+=$k;
    }
}

$INDIVIDUAL_TAGS = $total;
$TAGS_USED = $used;
echo '<p>I have used '.$INDIVIDUAL_TAGS.' different tags and used them '.$TAGS_USED.' times.</p>'.BR;

// echo '<ol>'.BR;
// $c=0;
// foreach($tagscore as $k=>$p)
// {
//     // PreDump(array($k,$p));
//     echo '<li>'.$k.' <sup>'.$p.'</sup></li>'.BR;
//     $c++; if ($c == 10) break;
// }
// echo '</ol>'.BR;
// 
// echo '<p>The top twenty domains I\'ve bookmarked from are:</p>'.BR;
// echo '<ol>'.BR;

$c=0;
foreach($pop_urls as $k=>$p)
{
    // PreDump(array($k,$p));
    echo '<li>'.$k.' <sup>'.$p.'</sup></li>'.BR;
    $c++; if ($c == 20) break;
}
echo '</ol>'.BR;

echo '<p>I piped all the text I\'d written in descriptions into wordle and got the following:</p>'.BR; 
echo '<img src="description-wordle.png" width="600"/>'.BR;

// ksort($tagnumbs);

// $r1 = '';
// $r2 = '';
// echo '<pre>';
// foreach($tagscore as $k=>$t)
// {
//     echo $t.TAB;
// }
// echo '</pre>';

// foreach($tagnumbs as $k=>$t)
// {
//     
//     $r1 .= $k.TAB; 
//     $r2 .= count($t).TAB; 
// }

// echo "<pre>";
// echo $r1;
// echo BR;
// echo $r2;
// echo "</pre>";

// PreDump($tagnumbs);

// checkHttpCode();

// $ser = serialize($data);
// echo $ser;

// arsort($pop_urls);
// PreDump($pop_urls);

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - 
// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - 

// 
// -- FUNCTIONS --
//

//
//  Check the HTTP Codes 
//
function checkHttpCode()
{
    global $data;
    
    $c=0;
    foreach($data as $k=>$d)
    {        
        if (isset($d['CODE'])) continue;
        
        $u = $d['HREF'];
        
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $u);
        curl_setopt($curl, CURLOPT_NOBODY, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 0);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT_MS, 0);
        $header = curl_exec($curl);
        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
    
        $data[$k]['CODE'] = $code;
        $c++; if ($c == 10) break;
    }    
}

function createPopUrls()
{
    global $data, $pop_urls, $pop_url_store;    
    foreach($data as $k=>$d)
    {
        $url = parse_url($d['HREF']);
        
        if (isset($pop_urls[$url['host']])) $pop_urls[$url['host']]++;
        else $pop_urls[$url['host']] = 1;
        
        if (isset($pop_url_store[$url['host']])) $pop_url_store[$url['host']][] = $d;
        else $pop_url_store[$url['host']] = array($d);        
    }
    arsort($pop_urls);
}

function getRawData()
{
    global $data, $tagscore, $tagurls;
    $filename = 'delicious.html';
    $file = file($filename);
    $c = -1;
    // each line of the HTML file.
    foreach($file as $line)
    {
        // if the line is a usable bookmark.
        if (preg_match('/^<DT>/', $line))
        {
            $c++;

            // extracting the attributes from the HTML tag.
            preg_match_all('/(\\w{1,})="([^"]*)"/',$line, $matches);
            for($i=0; $i<count($matches[0]); $i++)
            {
                $data[$c][$matches[1][$i]] = $matches[2][$i];
            }
            continue;
        }

        // if the line is a description.
        if (preg_match('/^<DD>/', $line))
        {
            $data[$c]['COMMENT'] = substr($line,4);
            continue;

        }
    }
}

//
// Create the tagscore and tagurls arrays
//
function makeTagData()
{
    global $data, $tagscore, $tagurls;
    
    foreach($data as $c=>$d)
    {
        // split up the tags.
        $t = split(',',$d["TAGS"]);
        foreach($t as $i)
        {
            $i = trim($i); // remove any whitespace
            $i = strtolower($i); // convert to lowercase (because the case shouldn't affect it)
            if (!$i) continue;

            if (isset($tagscore[$i])) $tagscore[$i] ++ ;
            else $tagscore[$i] = 1;

            if (isset($tagurls[$i])) array_push($tagurls[$i], $c);
            else $tagurls[$i] = array($c);
        }        
    }
    
    arsort($tagscore);
}

//
// get the data from the cache file
//
function getCachedData()
{
    global $data;
    $cache = 'data.ser';
    $ser = join('',file($cache));
    $data = unserialize($ser);
}

//
// Output the complete monthly totals (tab-delimeted)
//
function showCompleteTagsByMonth()
{
    $r1 = ''; 
    $r2 = '';
    foreach($all_m_y as $m_y)
    {
        $r1 .= $m_y.TAB;
        if (isset($taggraph[$m_y])) $r2 .= $taggraph[$m_y].TAB;        
        else $r2 .= '0'.TAB;        
    }

    echo '<pre>';
    echo $r1;
    echo "\n";
    echo $r2;
    echo '</pre>';
}

//
// show the tag graph data in a table (by month).
//
function showTagGraphTable()
{
    global $all_m_y, $taggraph;
    echo '<table cellspacing="0" cellpadding="5">';
    echo '<tr>';
    echo '<th></th>';
    foreach($all_m_y as $a)
    {
        echo '<th>'.$a.'</th>';
    }
    echo '</tr>';
    foreach($taggraph as $t=>$g)
    {
        echo '<tr>';
        echo '<td>'.$t.'</td>';
        foreach($all_m_y as $a)
        {
            echo '<td>';
            if (isset($g[$a])) echo $g[$a];
            else echo 0;
            echo '</td>';
        }
        echo '</tr>';
    }
    echo '</table>';
}

//
// show the tag graph data as tab-delimited (for C&P into Google docs).
//
function showTagGraphTabDelimeted()
{
    global $all_m_y, $taggraph;
    echo '<pre>';
    echo TAB;
    foreach($all_m_y as $a)
    {
        echo $a.TAB;
    }
    echo BR;
    foreach($taggraph as $t=>$g)
    {
        
        echo $t.TAB;
        foreach($all_m_y as $a)
        {
            if (isset($g[$a])) echo $g[$a];
            else echo 0;
            echo TAB;
        }
        echo BR;
    }
    echo '</pre>';
}

//
// creating the tag list grouped (by usage number).
// 
function createTagNumbers()
{
    global $tagscore, $tagnumbs;
    foreach($tagscore as $t=>$s)
    {
        $tagnumbs[$s][] = $t;
    }
}

//output everything in a JavaScript friendly format.
function echoAllAsJSON()
{
    global $data, $tagscore, $tagurl;
    
    $output = array(
         "data" => $data
        ,"tagscore" => $tagscore
        ,"tagurl" => $tagurls
    );

    $o = json_encode($output);
    echo $o;    
}

// echoing out all the descriptions for Wordle.
function dumpDescriptions()
{
    global $data;
    foreach($data as $d)
    {
        if (isset($d['COMMENT'])) echo strtolower(htmlentities($d['COMMENT']));
    }
}
// dumpDescriptions();

//
// Outing all the tags in groups (by usage number)
//
function outputTagNumbers()
{
    global $tagnumbs;
    foreach($tagnumbs as $k=>$n)
    {
        echo '<h1>'.$k.'</h1>';
        echo '<p>'.join(', ',$n).'</p>';
    }
}

// -- GRAPH -- 
// preparing all the data for graphing.
//
function createTopTenTagGraphData()
{
    global $data, $tagscore, $tagurls;
    $taggraph = array();
    $c=0;
    foreach($tagscore as $tag=>$score)
    {    
        $urls = $tagurls[$tag];
        foreach($urls as $u)
        {
            $d = $data[$u]['ADD_DATE'];        
            $m_y = date('Y-M',$d);

            if (isset($taggraph[$tag][$m_y])) $taggraph[$tag][$m_y]++; 
            else $taggraph[$tag][$m_y] = 1;
        }
        $c++;
        if ($c == 10) break;
    }
    return $taggraph;
}


function createCompleteTagGraphData()
{   
    global $data, $tagscore, $tagurls;
    $taggraph = array();
    foreach($data as $d)
    {
        $datet = $d['ADD_DATE']; 
        $m_y = date('Y-M',$datet);
              
        if (isset($taggraph[$m_y])) $taggraph[$m_y]++; 
        else $taggraph[$m_y] = 1;       
    }
    return $taggraph;
}

function PreDump($data)
{
    // $data = json_encode($data);
    // echo $data;
    // return;
    
    ob_start();
    var_dump($data);    
    $out = ob_get_contents();
    ob_end_clean();
    
    echo '<pre>';
    echo htmlentities($out);
    echo '</pre>';
}
?>