<?php include("simple_html_dom.php"); ?>
<?php
		$user = 'xxx'; // xxx 為工號
        $password = 'xxx'; // password

    function getinput($url){
		//$url = 'http://qcicore01/QDSM/Program/QDSM_Device_Maintain.aspx?SERNUM=710&DEVTYP=51&ATPATH=710_AUCD00.jpg&Dept=SIT3';//注意：是要获取信息的页面地址，不是登录页的地址。
        echo $url;
		global $user,$password,$VIEWSTATE,$EVENTVALIDATION,$txtDEVNAM,$txtVENDER,$txtMODEL1,$devurl,$checkos,$txtREMARK,$cs1,$cs2;
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_USERAGENT, "User-Agent: Mozilla/5.0 (Windows NT 6.1; WOW64; rv:31.0) Gecko/20100101 Firefox/31.0");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true); //加上这可以获取cookies,就是输出的$result的前面有header信息
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_NTLM);
        curl_setopt($ch, CURLOPT_USERPWD, $user.':'.$password);
        
		$result = curl_exec($ch);
		//$result = file_get_contents("./test.txt"); //本地測試
        //echo $result;
		
		preg_match('/id="__VIEWSTATE" value="(.*)"/U', $result, $matches);
		$VIEWSTATE=$matches[1];
		preg_match('/<input type="hidden" name="__EVENTVALIDATION" id="__EVENTVALIDATION" value="(.+?)"/', $result, $matches);
		$EVENTVALIDATION=$matches[1];
		preg_match('/txtDEVNAM" type="text" value="(.+?)"/', $result, $matches);
		$txtDEVNAM=$matches[1];
		preg_match('/txtVENDER" type="text" value="(.+?)"/', $result, $matches);
		$txtVENDER=$matches[1];
		preg_match('/txtMODEL1" type="text" value="(.+?)"/', $result, $matches);
		$txtMODEL1=$matches[1];
		
		preg_match('/<form name="aspnetForm" method="post" action="(.+?)"/', $result, $matches);
		$devurl='http://qcicore01/QDSM/Program/'.$matches[1];
		//preg_match_all ('/^Set-Cookie: (.*?);/m',$result,$m); //获取cookies
        //var_dump($m);
		//preg_match('/QueuePopulateIMNRC\(\'(.+?)\'/', $result, $m);
		//preg_match ('/^QueuePopulateIMNRC (.*?);/',$result,$m);
		//$loginmm=$m[1];
		
		$xxx = new simple_html_dom();
		$xxx->load($result);
		//os check
		foreach($xxx->find('input[checked=checked]') as $element){
			$checkos[$element->name] = $element->checked;
		}
		/*remark
		preg_match('~<textarea .*?>(.*?)</textarea>~', $result, $matches);
		$txtREMARK=$matches[1];		
		*/
		foreach($xxx->find('textarea') as $element){
			$txtREMARK[$element->name] = $element->innertext;
			//print_r($txtREMARK);
		}
		//class
		foreach($xxx->find('select[id=ctl00_ContentPlaceHolder1_ddlCS1SEL] option[selected]') as $element){
			$cs1['ctl00$ContentPlaceHolder1$ddlCS1SEL'] =  $element->value;
			//print_r($cs1);
		}
		//subclass
		foreach($xxx->find('select[id=ctl00_ContentPlaceHolder1_ddlCS2SEL] option[selected]') as $element){
			$cs2['ctl00$ContentPlaceHolder1$ddlCS2SEL'] =  $element->value;
			//print_r($cs2);
		}
		/*
		foreach($xxx->find('form') as $element) 
		echo $element->action . '<br><br>';
		foreach($xxx->find('input') as $element) 
		echo $element->name . '=' . $element->value . '<br><br>';
		*/
		echo 'getinput_done';
		//print_r ($checkos);
		}
		
	function postformwithinput($pic){
		global $user,$password,$VIEWSTATE,$EVENTVALIDATION,$txtDEVNAM,$txtVENDER,$txtMODEL1,$devurl,$checkos,$txtREMARK,$cs1,$cs2;
		$toURL = htmlspecialchars_decode($devurl);
		
		$cfile = curl_file_create(getcwd().'/'.$pic.'.jpg','image/jpeg',$pic.'.jpg');
		print_r ($cfile);
		
		$data1 = array(
		"__VIEWSTATE"=>$VIEWSTATE,
		"__EVENTVALIDATION"=>$EVENTVALIDATION,
		"__EVENTTARGET"=>"",
		"__EVENTARGUMENT"=>"",
		'__VIEWSTATEENCRYPTED'=>'',
		"ctl00\$ContentPlaceHolder1\$txtMODEL1"=>$txtMODEL1,
		"ctl00\$ContentPlaceHolder1\$txtVENDER"=>$txtVENDER,
		"ctl00\$ContentPlaceHolder1\$txtDEVNAM"=>$txtDEVNAM,
		"ctl00\$ContentPlaceHolder1\$btnSave"=>"Save",
		//"ctl00\$ContentPlaceHolder1\$ddlCS1SEL"=>"13", 2
		//"ctl00\$ContentPlaceHolder1\$txtREMARK"=>'1.Component:- AUDIO CDx1- Lyrics',
		//"ctl00\$ContentPlaceHolder1\$chkOSSYSM\$7"=>'checked',
		//"ctl00\$ContentPlaceHolder1\$chkOSSYSM\$6"=>'checked',
		//"ctl00\$ContentPlaceHolder1\$chkOSSYSM\$0"=>'checked',		
		//檔案若和程式在同一目錄或相對目錄, 可以用getcwd(), 如:
		//"userfile"=>"@".getcwd()."/oxox.doc",
		//"ctl00\$ContentPlaceHolder1\$htmPHOTO1"=>"@".getcwd()."\AUCD00.jpg",
		//"ctl00\$ContentPlaceHolder1\$ddlCS2SEL"=>"111", 54
		//另外還可以在檔名後面加上分號指定mimetype(較新版的PHP才能使用)
		//(預設的 mimetype 為application/octet-stream)
		//"userfile"=>"@".getcwd()."\\somePic.png;type=image/png"
		);
		
		if (!empty($txtREMARK['ctl00$ContentPlaceHolder1$txtREMARK'])) {
		$data1=array_merge($data1,$txtREMARK);
		}
		if (!empty($checkos)) {		
		$data1=array_merge($data1,$checkos);
		}
		if (!empty($cs1)) {		
		$data1=array_merge($data1,$cs1);
		}
		if (!empty($cs2)) {		
		$data1=array_merge($data1,$cs2);
		}
		$data=$data1;
		//print_r($data);
		
		$cfile = array("ctl00\$ContentPlaceHolder1\$htmPHOTO1" => $cfile);
		$post=array_merge($data,$cfile);
		
		$ch = curl_init();
		$options = array(
		CURLOPT_URL=>$toURL,
		CURLOPT_USERAGENT=>"User-Agent: Mozilla/5.0 (Windows NT 6.1; WOW64; rv:31.0) Gecko/20100101 Firefox/31.0",
		CURLOPT_RETURNTRANSFER=>1,
		CURLOPT_HEADER=>1,
		CURLOPT_FOLLOWLOCATION=>1,
		CURLOPT_AUTOREFERER=>1,
		CURLOPT_COOKIEJAR=>'CURLCOOKIE',
		CURLOPT_HTTPHEADER=>array('Content-Type: multipart/form-data'),
		CURLOPT_HTTP_VERSION=>CURL_HTTP_VERSION_1_1,
		CURLOPT_HTTPAUTH=>CURLAUTH_NTLM,
		CURLOPT_USERPWD=>$user.':'.$password,
		CURLOPT_POST=>1,
		CURLOPT_VERBOSE=>1,
		CURLOPT_POSTFIELDS=>$post,// 直接給array 
		);
		/*不需要的option們
		CURLOPT_HTTPHEADER=>array("Content-Type: multipart/form-data; boundary=$boundary"),
		CURLOPT_SSL_VERIFYPEER=>false,		
		CURLOPT_INFILESIZE=>filesize($post),
		CURLOPT_SAFE_UPLOAD=>1,
		CURLOPT_REFERER=>$toURL,
		CURLOPT_INFILESIZE=>filesize($file),
		*/
		curl_setopt_array($ch, $options);
		//print_r ($post);
		//print_r ($options);

		$rr=curl_exec($ch); //本地不跑
		//$rr='testalert(xx)'; //測試用
		
		//print_r (curl_getinfo($ch));
		if(!curl_errno($ch)){echo 'ok';}
		if(curl_errno($ch)){echo 'Curl error: ' . curl_error($ch);}
		curl_close($ch);

		if (preg_match('/Update Success!!/i', $rr)){
		date_default_timezone_set('UTC');		
		echo date("H:i:s").','.'postdone,';
		} else {
		preg_match('/alert\((.*)\)/U', $rr, $matches);
		$ale=$matches[1];
		echo $pic.$ale.'--uploadfail--,' ;
			file_put_contents($pic.".txt",$rr);
		}
		//echo $rr;
	}
	
//main	
	$SERNUMid = file_get_contents("./resp.txt"); //yu need update items
	//$SERNUMid = file_get_contents("./resptest.txt"); //samtest
	preg_match_all('/SERNUM=(.*?)D/', $SERNUMid, $matcheSER);
	//print_r($matcheSER[1]);
	preg_match_all('/Dept=QSMC-NB3-RD2\'\);\"\>(.*?)\</', $SERNUMid, $matcheid);
	//print_r($matcheid[1]);
	$i = 0;
foreach (array_combine($matcheSER[1],$matcheid[1]) as $uu => $pp ){
	$url= 'http://qcicore01/QDSM/Program/QDSM_Device_Maintain.aspx?SERNUM='.$uu.'DEVTYP=51&ATPATH=&Dept=QSMC-NB3-RD2';
	
	getinput($url);
	postformwithinput($pp);
	
	echo $i++.','.$uu.','.$pp;
}

?>

<?php
/*
$url = 'http://www.example.com/';
$data = array(
				'foo' => '1',
				'bar' => '2'
			);

function multipart_build_query($fields, $boundary){
	$retval = '';
	foreach($fields as $key => $value){
	$retval .= "--$boundary\nContent-Disposition: form-data; name=\"$key\"\n\n$value\n";
	}
	$retval .= "--$boundary--";
	return $retval; 
	}

$boundary = '--myboundary-xxx'; 
$body = multipart_build_query($data, $boundary);

$ch = curl_init(); 
curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: multipart/form-data; boundary=$boundary")); 
curl_setopt($ch, CURLOPT_URL, $url); 
curl_setopt($ch, CURLOPT_POST, true); 
curl_setopt($ch, CURLOPT_POSTFIELDS, $body); 
$response = curl_exec($ch);
*/
?>

<?php
/*
	require 'pg.php';
	require 'shd.php';
	$b = new PGBrowser();
	
	$b->setopt(CURLOPT_RETURNTRANSFER, true);
	$b->setopt(CURLOPT_HEADER, true);
	$b->setopt(CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
	$b->setopt(CURLOPT_HTTPAUTH, CURLAUTH_NTLM);
	$b->setopt(CURLOPT_USERPWD, $user.':'.$password);

	$page = $b->get('http://qcicore01/QDSM/Program/DEVICE_Query.aspx');
	
	$form = $page->form();
	$form->set('q', 'foo');
	//$page = $form->submit();
	//echo $page->title;
*/
?>
<?php
//另外的範例
		/* 單一表
		function multipart_build_query($fields, $boundary){
		$retval = '';
		foreach($fields as $key => $value){
		$retval .= "--$boundary\nContent-Disposition: form-data; name=\"$key\"\n\n$value\n";
		}
		$retval .= "--$boundary--";
		return $retval; 
		}
		*/
		/*表格加圖片		
		function multipart_build_query($fields, $boundary){
		global $file;
		$retval = '';
		$eol = "\r\n";
			foreach($fields as $key => $value){

			if (substr($value, 0, 1)=='@') {
				$path = pathinfo($value);
				$retval .= '--' . $boundary . $eol;
				$retval .= 'Content-Disposition: form-data; name="'.$key.'"; filename="'.$path['basename'].'"' . $eol;
				$retval .= 'Content-Type: image/jpeg' . $eol;
				//$retval .= 'Content-Transfer-Encoding: binary'.$eol;
				//$retval .= 'Content-Transfer-Encoding: base64' . $eol . $eol;
				//$retval .= chunk_split(base64_encode(file_get_contents(substr($value, 1)))) . $eol;
				
				$file=substr($value, 1);
				$retval .= 'Content-Length: '.file_get_contents(substr($value, 1)) . $eol;
			}
			else {
				$retval .= "--$boundary\nContent-Disposition: form-data; name=\"$key\"\n\n$value\n";
			}
			}
			$retval .= "--$boundary--";
			//echo $retval;
		return $retval;
		}		
		
		$boundary = '--myboundary-xxx'; 
		//$post = multipart_build_query($data, $boundary);		
		*/
?>