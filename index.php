<?php
function http_response($url)
{
        $header=[
          'Private-Token: XyLDJHAr3-5NHKN67nTU',
          'Authorization: Bearer XyLDJHAr3-5NHKN67nTU',
        ];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        $head = curl_exec($ch);
        curl_close($ch);
        return $head;
}

$val = getopt(null, ["commit_id:","project_id:"]);
$commit_id = $val['commit_id'];
$project_id = $val['project_id'];

// $commit_url='https://gitlab.com/api/v4/projects/15766537/repository/commits/1e24b4f91e80e8647ea5ad87e24165166e0a145d';

// $commit_diff_url='https://gitlab.com/api/v4/projects/15766537/repository/commits/1e24b4f91e80e8647ea5ad87e24165166e0a145d/diff';

$commit_url='https://gitlab.com/api/v4/projects/'.$project_id.'/repository/commits';

$commit_diff_url='https://gitlab.com/api/v4/projects/'.$project_id.'/repository/commits/'.$commit_id.'/diff';

$data = http_response($commit_url);
$commit_data=json_decode($data);

$data = http_response($commit_diff_url);
$data=json_decode($data);

$files=[];
foreach ($data as $key => $value) {
  // $value->old_path;
  if(isset($value->new_path))
    if(strpos($value->new_path, 'index.html') !== false)
      $files[]=$value->new_path;
}

$messages=[];

foreach ($files as $key => $value) {
    $check_url = __DIR__."/../".$value;

    $myfile = fopen($check_url, "r") or die("Unable to open file!");
    $contents = fread($myfile,filesize($check_url));
    fclose($myfile);
    $baseURL = 'http://validator.w3.org/check';
    $post = array(
            // 'output'=> 'soap12',
            'uri'=>'',
            'output'=> 'json',
            'fragment'  => $contents,
            'ss'=>1,
            'outline'=>1,
        );

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,$baseURL);
    curl_setopt($ch, CURLOPT_POST,1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'User-Agent: W3C Validation bot', 
    )); 
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
    $output=curl_exec ($ch);
    curl_close ($ch);

    $position=stripos($output, '{');
    $output = substr($output, $position);

    if(false === $output) {
        $messages[$value][]='API call failed';
    }
    else{
        $output=str_replace('"message": ,', ' "message": 0,', $output);
        $output=json_decode($output,true);
        if (isset($output['messages'])) {
            foreach ($output['messages'] as $value1) {
                if($value1['message']!='0'){
                    $messages[$value][]=$value1;
                }
            }
        }
    }
}
// $color=[
//    PURPLE = '\033[95m'
//    CYAN = '\033[96m'
//    DARKCYAN = '\033[36m'
//    BLUE = '\033[94m'
//    GREEN = '\033[92m'
//    YELLOW = '\033[93m'
//    RED = '\033[91m'
//    BOLD = '\033[1m'
//    UNDERLINE = '\033[4m'
//    END = '\033[0m'
// ];
foreach ($messages as $key => $value) {
    if(count($value)>0){  
        echo '
        
';
        echo "\033[1m\033[32m". $key."\033[0m\033[0;0m
";
        foreach ($value as $key1 => $value1) {
            echo "\033[1m\033[91m(Line:".$value1['lastLine'].') - (POS:'.$value1['lastColumn'].")\033[0m\033[0;0m - ".$value1['message'].'
';
        }
    }
}

if(count($messages)==0){  
        echo '
        
';
        echo "\033[1m\033[32m It Had No ISSUES \033[0m\033[0;0m
";
}

