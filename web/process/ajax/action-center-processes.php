<?php
/*
Marketocracy Inc. |
Action Center

Supporting files:
	AJAX		- THIS DOCUMENT process/ajax/action-center-processes.php
	JAVASCRIPT	- includes/scripts/action-center.js.php
	HTML		- includes/pages/action-center.php
*/
error_reporting(E_ALL);
//Start Session
session_start();

// Load debug & error logging functions
require_once("../../includes/system-debug-functions.php");

//Connect to DB
require_once("../../../secure/dbconnect.php");

// Load System Wide Functions
require_once("../../includes/system-functions.php");
require_once("../../includes/system-classes.php");

//Get Process from URL
$process = $_REQUEST['process'];


switch($process){
	
	case 'fix-urls':
		
		$query = "
			SELECT article_link, article_id
			FROM members_profile_articles
		";
		try {
			$rsLink = $mLink->prepare($query);
			$aValues = array(
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		//die($preparedQuery);
			$rsLink->execute($aValues);
		}
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		while($links = $rsLink->fetch(PDO::FETCH_ASSOC)){
			
			$articleLink 	= $links['article_link'];
			$articleID		= $links['article_id'];
			
			if(strpos($articleLink, 'https') !== false){
				$https = true;	
			}else{
				$https = false;	
			}
			
			$articleLink	= filter_var($articleLink, FILTER_SANITIZE_URL);
			
			$articleLink = str_replace('https://', '', $articleLink);
			$articleLink = str_replace('http://', '', $articleLink);
			
			if($https == true){
				$articleLink = 'https://'.$articleLink;
			}else{
				$articleLink = 'http://'.$articleLink;
			}
			
			$query = "
				UPDATE members_profile_articles
				SET article_link=:article_link
				WHERE article_id=:article_id
			";
			try {
			$rsUpdate = $mLink->prepare($query);
				$aValues = array(
					':article_link'	=> $articleLink,
					':article_id'	=> $articleID
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			//die($preparedQuery);
				$rsUpdate->execute($aValues);
			}
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
			
		}
		
		
		
	break;
	
	case 'save-domain':
	
		$subdomain	= $_REQUEST['domain'];
		
		$query = "
			SELECT *
			FROM ".$_SESSION['mtr_subdomain_table']."
			WHERE member_id=:member_id
		";
		try {
			$rsSubdomain = $mLink->prepare($query);
			$aValues = array(
				':member_id'	=> $_SESSION['member_id']
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		//die($preparedQuery);
			$rsSubdomain->execute($aValues);
		}
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		$rowCnt = $rsSubdomain->fetch(PDO::FETCH_ASSOC);
		
		if($rowCnt > 0){
			$updateType = 'update';	
		}else{
			$updateType = 'insert';	
		}
		
		//echo $updateType;
		
		switch($updateType){
			case 'insert':
				$query 		= "
					INSERT INTO ".$_SESSION['mtr_subdomain_table']." (
						member_id,
						subdomain,
						timestamp
					)VALUES(
						:member_id,
						:subdomain,
						UNIX_TIMESTAMP()
					)
				"; 	
			break;
			
			case 'update':
				$query = "
					UPDATE ".$_SESSION['mtr_subdomain_table']."
					SET subdomain=:subdomain, timestamp=UNIX_TIMESTAMP()
					WHERE member_id=:member_id
				";
			break;	
		}
		
		try{
			$rsUpdate = $mLink->prepare($query);
			$aValues = array(
				':member_id'	=> $_SESSION['member_id'],
				':subdomain'	=> $subdomain
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsUpdate->execute($aValues);
		}
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}		
		
		$event = "Subdomain Change";
		$detail	= $_SESSION['username'].' has changed their mytrackrecord.com subdomain.';
		
		addEventRecord($mLink, $_SESSION['member_id'], $event, $detail);
		
		echo '<div class="alert alert-success">You have successfully updated your subdomain to: <strong>'.$subdomain.'</strong>.mytrackrecord.com.</div>';
		
	break;
	
	//+-----------------------------------------------------------------------------------------------------------------------+
	//												Update Fund Display On/Off
	//+-----------------------------------------------------------------------------------------------------------------------+
	case 'check-subdomain':
		
		$subdomain		= $_REQUEST['subdomain'];
		
		
		$checkSubdomain	= mtr_subdomain($mLink, $_SESSION['member_id'], $subdomain, 'check-subdomain');
		
		//echo '<div class="alert alert-danger">'.$checkSubdomain.'.mytrackrecord.com</div>';
		echo $checkSubdomain;
		
	break;
	
	//+-----------------------------------------------------------------------------------------------------------------------+
	//												Update Fund Display On/Off
	//+-----------------------------------------------------------------------------------------------------------------------+
	case 'updateDisplay':
		
		$displayFund 	= $_REQUEST['public'];
		$fundID			= $_REQUEST['fundID'];
		
		if($displayFund == 'on'){
			$public = 1;	
		}else{
			$public = 0;
		}
		
		$query = "
			UPDATE ".$_SESSION['fund_table']."
			SET public=:public
			WHERE fund_id=:fund_id
		";
		try{
			$rsUpdate = $mLink->prepare($query);
			$aValues = array(
				':fund_id'	=> $fundID,
				':public'	=> $public
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsUpdate->execute($aValues);
		}
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}			
		
		if($error == ''){
			
			if($public == 1){
				echo '
					<div class="note note-success">
						<p>Fund is visible on MyTrackRecord.com.</p>
					</div>
				';	
			}else{
				echo '
					<div class="note note-warning">
						<p>Fund is no longer visible on MyTrackRecord.com.</p>
					</div>
				';	
			}
		}
		
	break;
	
	//+-----------------------------------------------------------------------------------------------------------------------+
	//														Save Fund Strategy
	//+-----------------------------------------------------------------------------------------------------------------------+
	case 'save-fund-strat':
		
		$fundID		= $_REQUEST['fundID'];
		$fundStrat	= $_REQUEST['fundStrat'];
			
		$query = "
			UPDATE ".$_SESSION['fund_table']."
			SET description=:desc
			WHERE fund_id=:fund_id
		";
		try{
			$rsUpdate = $mLink->prepare($query);
			$aValues = array(
				':fund_id'	=> $fundID,
				':desc'		=> $fundStrat
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsUpdate->execute($aValues);
		}
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		
		if($error == ''){
			echo '
				<div class="note note-success">
					<p>Fund Strategy has been updated.</p>
				</div>
			';	
		}
		
	break;
	
	//+-----------------------------------------------------------------------------------------------------------------------+
	//														Edit Article
	//+-----------------------------------------------------------------------------------------------------------------------+
	case 'edit-article':
	
		$articleID		= $_REQUEST['edit_article_id'];
		$articleType	= $_REQUEST['article_type'];
		$articleTitle	= $_REQUEST['article_title'];
		$articleLink	= $_REQUEST['article_link'];
		$articleText	= $_REQUEST['article_text'];
		$articleStocks	= strtoupper($_REQUEST['article_stocks']);
		$articleTags	= $_REQUEST['article_tags'];
		$publishYear	= $_REQUEST['year'];
		$publishMonth	= $_REQUEST['month'];
		$publishDay		= $_REQUEST['day'];
		$publishDate	= mktime(5,0,0,$publishMonth,$publishDay,$publishYear);
		$genCampaign	= $_REQUEST['gen_camp'];
		$campList		= $_REQUEST['camp_list'];
		
		$aErrors 		= array();
		
		//$aErrors[] = $articleType;
		
		if($articleType == 'xx'){
			$aErrors[] 	= 'Please select an article type.';	
		}else{
		
			if(empty($articleTitle)){
				$aErrors[]	= 'Please provide an article title.';	
			}
			if(empty($articleLink)){
				$aErrors[]	= 'Please provide a article link.';	
			}else{
				
				if(strpos($articleLink, 'https') !== false){
				$https = true;	
			}else{
				$https = false;	
			}
				
				$articleLink	= filter_var($articleLink, FILTER_SANITIZE_URL);
				
				$articleLink = str_replace('https://', '', $articleLink);
				$articleLink = str_replace('http://', '', $articleLink);
				
				if($https == true){
					$articleLink = 'https://'.$articleLink;
				}else{
					$articleLink = 'http://'.$articleLink;
				}
				
				if (!preg_match("/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i",$articleLink)) {
					$aErrors[]	= 'The link you provided is invalid.';
				}
				
			}
			if(empty($articleText)){
				$aErrors[]	= 'Please provide preview text for the article.';	
			}else{
				
				//$articleText = tokenTruncate($articleText,200);
					
			}
			
			if($genCampaign == 1){
				if($campList == 'xx'){
					$aErrors[]	= 'Please select an email list.';	
				}
			}//end if gen button = 1
			
		}
		
		//$aErrors[] 	= 'test';
		
		if(empty($aErrors)){
			
			//$aErrors[] = $articleType;
			
			$query = "
				UPDATE ".$_SESSION['members_articles_table']."
				SET article_type=:article_type, 
					article_link=:link, 
					article_title=:title, 
					publish_date=:date, 
					article_text=:text,
					article_stock=:stocks,
					article_tags=:tags, 
					timestamp=UNIX_TIMESTAMP()
				WHERE article_id=:article_id
			";
			try{
				$rsUpdate = $mLink->prepare($query);
				$aValues = array(
					':article_id'		=> $articleID,
					':article_type'		=> $articleType,
					':title'			=> $articleTitle,
					':text'				=> $articleText,
					':stocks'			=> $articleStocks,
					':tags'				=> $articleTags,
					':link'				=> $articleLink,
					':date'				=> $publishDate
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsUpdate->execute($aValues);
			}
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}	
			
			if($genCampaign == 1){
				
				$aCamp = array(
					'camp_list'		=> $campList,
					'camp_type'		=> 'standard_campaign',
					'temp_id'		=> '23',
					'camp_name'		=> $articleStocks,
					'article_title'	=> $articleTitle,
					'article_text'	=> $articleText,
					'aritcle_link'	=> $articleLink
				);
				
				generateArticleCampaign($mLink, $aCamp);	
			}
			//$aErrors[] = $preparedQuery;
			//$aErrors[] = $error;
			
			/*echo '<div class="note note-danger"><ul>';
			foreach($aErrors as $key=>$error){
				echo '<li>'.$error.'</li>';
			}
			echo '</ul></div>';*/
			
		}else{
			
			echo '<div class="note note-danger"><ul>';
			foreach($aErrors as $key=>$error){
				echo '<li>'.$error.'</li>';
			}
			echo '</ul></div>';
				
		}
		
	break;
	
	//+-----------------------------------------------------------------------------------------------------------------------+
	//														Delete Article
	//+-----------------------------------------------------------------------------------------------------------------------+
	case 'delete-article':
	
		$action		= $_REQUEST['action'];
		$articleID	= $_REQUEST['articleID'];
		
		switch($action){
		
			
			case 'delete':
				
				$query = "
					UPDATE ".$_SESSION['members_articles_table']."
					SET deleted='1'
					WHERE article_id=:article_id
				";
				try{
					$rsDelete = $mLink->prepare($query);
					
					$aValues = array(
						':article_id'	=> $articleID
					);
					$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
					$rsDelete->execute($aValues);
				}
				catch(PDOException $error){
					// Log any error
					file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
				}	
				//echo $error;
				
			break;	
		}
		
		
		
	break;
	
	//+-----------------------------------------------------------------------------------------------------------------------+
	//														Add Article
	//+-----------------------------------------------------------------------------------------------------------------------+
	case 'add-article':
		
		$aErrors		= array();
		$articleType	= $_REQUEST['article_type'];
		$articleTitle	= $_REQUEST['article_title'];
		$articleLink	= $_REQUEST['article_link'];
		$articleText	= $_REQUEST['article_text'];
		$articleStocks	= strtoupper($_REQUEST['article_stocks']);
		$articleTags	= $_REQUEST['article_tags'];
		$publishDay		= $_REQUEST['day'];
		$publishMonth	= $_REQUEST['month'];
		$publishYear	= $_REQUEST['year'];
		$genCampaign	= $_REQUEST['gen_camp'];
		$campList		= $_REQUEST['camp_list'];
		
		$publishDate	= mktime(5,0,0,$publishMonth,$publishDay,$publishYear);
		
		//$aErrors[]		= 'test';
		if($articleType == 'xx'){
			$aErrors[] 	= 'Please select an article type.';	
		}else{
		
			if(empty($articleTitle)){
				$aErrors[]	= 'Please provide an article title.';	
			}
			if(empty($articleLink)){
				$aErrors[]	= 'Please provide a article link.';	
			}else{
				
				
				if(strpos($articleLink, 'https') !== false){
					$https = true;	
				}else{
					$https = false;	
				}
				
				$articleLink	= filter_var($articleLink, FILTER_SANITIZE_URL);
				
				$articleLink = str_replace('https://', '', $articleLink);
				$articleLink = str_replace('http://', '', $articleLink);
				
				if($https == true){
					$articleLink = 'https://'.$articleLink;
				}else{
					$articleLink = 'http://'.$articleLink;
				}
				
				if (!preg_match("/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i",$articleLink)) {
					$aErrors[]	= 'The link you provided is invalid.';
				}
				
			}
			if(empty($articleText)){
				$aErrors[]	= 'Please provide preview text for the article.';	
			}else{
				
				//$articleText = tokenTruncate($articleText,200);
					
			}
			
			if($genCampaign == 1){
				if($campList == 'xx'){
					$aErrors[]	= 'Please select an email list.';	
				}
			}//end if gen button = 1
			
		}
		
		if(empty($aErrors)){
			
			echo '<pre>';
			echo $articleText;
			print_r($_REQUEST);
			echo '</pre>';
			
			$query = "
				INSERT INTO ".$_SESSION['members_articles_table']." (
					article_type,
					member_id,
					article_link,
					article_title,
					article_text,
					article_stock,
					article_tags,
					publish_date,
					timestamp
				) VALUES (
					:article_type,
					:member_id,
					:article_link,
					:article_title,
					:article_text,
					:article_stocks,
					:article_tags,
					:publish_date,
					UNIX_TIMESTAMP()
				)
			";
			try{
			$rsInsert = $mLink->prepare($query);
				$aValues = array(
					':member_id'		=> $_SESSION['member_id'],
					':article_type'		=> $articleType,
					':article_title'	=> $articleTitle,
					':article_text'		=> $articleText,
					':article_stocks'	=> $articleStocks,
					':article_tags'		=> $articleTags,
					':article_link'		=> $articleLink,
					':publish_date'		=> $publishDate
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsInsert->execute($aValues);
			}
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}	
			
			if($genCampaign == 1){
				
				$aCamp = array(
					'camp_list'		=> $campList,
					'camp_type'		=> 'standard_campaign',
					'temp_id'		=> '23',
					'camp_name'		=> $articleStocks,
					'article_title'	=> $articleTitle,
					'article_text'	=> $articleText,
					'aritcle_link'	=> $articleLink
				);
				
				generateArticleCampaign($mLink, $aCamp);	
			}
			
		}else{
			
			echo '<div class="note note-danger"><ul>';
			foreach($aErrors as $key=>$error){
				echo '<li>'.$error.'</li>';
			}
			echo '</ul></div>';
				
		}
		
	break;
	
	
	//+-----------------------------------------------------------------------------------------------------------------------+
	//													Load Article Fields
	//+-----------------------------------------------------------------------------------------------------------------------+
	case 'load-article-fields':
		
		$prefill 		= $_REQUEST['prefill'];
		
		//echo $prefill;
		
		if($prefill == '1'){
			
			$articleID		= $_REQUEST['articleID'];
				
			$query = "
				SELECT * 
				FROM ".$_SESSION['members_articles_table']."
				WHERE article_id=:article_id
			";
			try{
				$rsGetArticle = $mLink->prepare($query);
					$aValues = array(
						':article_id'	=> $articleID
					);
					$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
					$rsGetArticle->execute($aValues);
			}
			catch(PDOException $error){
					// Log any error
					file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
			echo $error;
			
			$article = $rsGetArticle->fetch(PDO::FETCH_ASSOC);
			
			$articleTitle	= $article['article_title'];
			$articleLink	= $article['article_link'];
			$articleText	= $article['article_text'];
			$publishDate	= $article['publish_date']; echo $publishDate;echo '<br />';echo date('Y-m-d',$publishDate);
			$articleStocks	= $article['article_stock'];
			$articleTags	= $article['article_tags'];
			
			if($_REQUEST['articleType'] == ''){
				$articleType	= $article['article_type'];
			}else{
				$articleType	= $_REQUEST['articleType'];	
			}
			//echo $articleType;
			
			if(strpos($articleLink, 'https') !== false){
				$https = true;	
			}else{
				$https = false;	
			}
			
			$articleLink = str_replace('https://', '', $articleLink);
			$articleLink = str_replace('http://', '', $articleLink);
			if($https == true){
				$articleLink = 'https://'.$articleLink;
			}else{
				$articleLink = 'http://'.$articleLink;
			}
			
			
			?>
            <div class="form-group">
                <label class="control-label">Article Type*</label>
                <select name="article_type" id="article_type2" class="form-control">
                    <?php echo printList($mLink, '12', $articleType);?>
                </select>
            </div>
            <input type="hidden" name="edit_article_id" id="edit_article_id" value="<?php echo $articleID;?>" />
            <?php
					
		}else{
			$articleType	= $_REQUEST['articleType'];
			$publishDate	= time();
		}
		
		switch($articleType){
			
			case 'blog':
				?>
                <div class="form-group">
                    <label class="control-label">Article Title*</label>
                    <input type="text" class="form-control" name="article_title" value="<?php echo $articleTitle;?>" />
                </div>
                <div class="form-group">
                    <label class="control-label">Blog Link*</label>
                    <input type="text" class="form-control" name="article_link" value="<?php echo $articleLink;?>" />
                </div>
                <div class="form-group">
                    <label class="control-label">Preview Text*</label>
                    <textarea class="form-control" name="article_text" row="3"><?php echo $articleText;?></textarea>
                    <span class="help-block">Copy/Paste First Paragraph</span>
                </div>
                <?php
			break;
			
			case 'article':
			
			break;
			
			case 'ext-article':
				?>
                <div class="form-group">
                    <label class="control-label">Article Title*</label>
                    <input type="text" class="form-control" name="article_title" value="<?php echo $articleTitle;?>" />
                </div>
                <div class="form-group">
                    <label class="control-label">Article Link*</label>
                    <input type="text" class="form-control" name="article_link" value="<?php echo $articleLink;?>" />
                </div>
                <div class="form-group">
                    <label class="control-label">Preview Text*</label>
                    <textarea class="form-control" name="article_text" row="3"><?php echo $articleText;?></textarea>
                    <span class="help-block">Copy/Paste First Paragraph</span>
                </div>
                <?php
			break;
			
			case 'forbes':
				?>
                <div class="form-group">
                    <label class="control-label">Article Title*</label>
                    <input type="text" class="form-control" name="article_title" value="<?php echo $articleTitle;?>" />
                </div>
                <div class="form-group">
                    <label class="control-label">Forbes Link*</label>
                    <input type="text" class="form-control" name="article_link" value="<?php echo $articleLink;?>" />
                </div>
                <div class="form-group">
                    <label class="control-label">Preview Text*</label>
                    <textarea class="form-control" name="article_text" row="3"><?php echo $articleText;?></textarea>
                    <span class="help-block">Copy/Paste First Paragraph</span>
                </div>
                <?php
			break;
			
			case 'seekingalpha':
				?>
                <div class="form-group">
                    <label class="control-label">Article Title*</label>
                    <input type="text" class="form-control" name="article_title" value="<?php echo $articleTitle;?>" />
                </div>
                <div class="form-group">
                    <label class="control-label">Seeking Alpha Link*</label>
                    <input type="text" class="form-control" name="article_link" value="<?php echo $articleLink;?>" />
                </div>
                <div class="form-group">
                    <label class="control-label">Preview Text*</label>
                    <textarea class="form-control" name="article_text" row="3"><?php echo $articleText;?></textarea>
                    <span class="help-block">Copy/Paste First Paragraph</span>
                </div>
                <?php
			break;
				
		}
		
		?>
        
        <div class="form-group">
        	<label class="control-label">Stock Tags</label>
            <input type="text" class="form-control" name="article_stocks" value="<?php echo $articleStocks;?>" />
            <span class="help-block">Comma Delimited Example: AMZN,TSLA,AAPL</span>
        </div>
        
        <div class="form-group">
        	<label class="control-label">Article Tags</label>
            <input type="text" class="form-control" name="article_tags" value="<?php echo $articleTags;?>" />
            <span class="help-block">Comma Delimited Example: Q3,featured,market strategy,stock strategy</span>
        </div>
        
        <div class="form-group">
        	<p><pre><?php
            echo $publishDate;echo'<br />';
			echo date('Y-m-d',$publishDate);
			?></pre></p>
            <label class="control-label">Select Publish Date</label><br />
            
            <select name="year">
                <?php
                echo date_list($mLink, 'year', NULL, date('Y',$publishDate),false);
                ?>
            </select>/
            <select  name="month">
                <?php
                echo date_list($mLink, 'month', NULL, date('m',$publishDate));
                ?>
            </select>/
            <select name="day">
                <?php
				echo date_list($mLink, 'day', NULL, date('d',$publishDate));
                ?>
            </select><br />
            <small>YYYY/MM/DD</small>
       	</div><!--form-group-->
        
        <?php if($_SESSION['admin'] == '1'){ ?>
            <hr />
            
            <div class="form-group">
                <label class="control-label"><input type="checkbox"  name="gen_camp" value="1" /> Generate Campaign for this Article</label><br /><p class="help-text">Tick the checkbox to automatically generate an email campaign.</p>
            </div>
            
            
            <div class="form-group">
                <label class="control-label">Send To:*</label>
                <select name="camp_list" class="form-control">
                    <option value='xx'>Select An Email List</option>
                    <?php
                    $query = "
                        SELECT *
                        FROM email_lists
                    ";
                    try{
                        $rsEmailList = $mLink->prepare($query);
                        $aValues = array();
                        $preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
                        $rsEmailList->execute($aValues);
                    }
                    
                    catch(PDOException $error){
                        // Log any error
                        file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
                    }
                    while($list = $rsEmailList->fetch(PDO::FETCH_ASSOC)){
                        echo '<option value="'.$list['list_id'].'">'.$list['list_name'].'</option>';	
                    }
                    ?>
                </select>
            </div>
            <?php }//end if admin == 1 ?>
        
        <?php
		
	break;
	
	//+-----------------------------------------------------------------------------------------------------------------------+
	//													Load Tracked Managers
	//+-----------------------------------------------------------------------------------------------------------------------+
	case 'load-tracked-managers':
				
		$userID = $_REQUEST['memberID'];	
		
		$query = "
			SELECT track_funds, member_id
			FROM ".$_SESSION['fund_tracking_table']."
			WHERE user_id=:member_id
		";
		try{
			$rsGetFunds = $mLink->prepare($query);
			$aValues = array(
				':member_id'	=> $userID
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsGetFunds->execute($aValues);
		}
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		
		$aTracking = array();
		
		while($funds	= $rsGetFunds->fetch(PDO::FETCH_ASSOC)){
			
			$aTrackFunds 	= explode('|',$funds['track_funds']);
			$memberID		= $funds['member_id'];
			
			foreach($aTrackFunds as $key=>$fundID){
				
				#get fund info
				//get basic fund info from db
				$query = "
					SELECT *
					FROM ".$_SESSION['fund_table']."
					WHERE fund_id=:fund_id
				";
				try{
					$rsFund = $mLink->prepare($query);
					$aValues = array(
						':fund_id'	=> $fundID
					);
					$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
					$rsFund->execute($aValues);
				}
				catch(PDOException $error){
					// Log any error
					file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
				}
				$fund = $rsFund->fetch(PDO::FETCH_ASSOC);
				
				//set vars
				//$fundID 		= $fund['fund_id'];
				$fundName		= $fund['fund_name'];
				$fundSymbol		= $fund['fund_symbol'];
				$aMember 		= get_member($mLink, $memberID, 'all');
				$aLivePrice		= get_funds($mLink, $fundID, 'lp', 'livePrice');
				$aAgg			= get_funds($mLink, $fundID, 'aggAll', 'agg');
				
				if($aLivePrice['compCash'] == '0' && $aLivePrice['compDiv25'] == '0' && $aLivePrice['compDiv10'] == '0' && $aLivePrice['compMargin'] == '0'){
					$compliant 	= '<span class="label label-success">Yes</span>';	
				}else{
					$compliant 	= '<span class="label label-danger">No</span>';	
				}
				
				$cashValue 		= $aLivePrice['cashValue'];
				$totalValue		= $aLivePrice['totalValue'];
				$percentCash	= number_format((($cashValue / $totalValue) * 100), 2);
				
				//get gains
				$query = "
					SELECT totalShares, gains
					FROM ".$_SESSION['fund_stratification_basic_table']."
					WHERE fund_id=:fund_id
					ORDER BY totalShares ASC
				";
				try{
				$rsGains = $mLink->prepare($query);
					$aValues = array(
						':fund_id' 	=> $fundID
						
					);
					$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
					$rsGains->execute($aValues);
				}
				
				catch(PDOException $error){
					// Log any error
					file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
				}
				
				$aGains = array();
				$gainCnt = 0;
				while($gains = $rsGains->fetch(PDO::FETCH_ASSOC)){
					$gainCnt++;
					$aGains[$fundID][$gainCnt] = $gains['gains'];
				}
				
				$posGainCnt = 0;
				$negGainCnt = 0;
				
				$posGainTotal = 0;
				$negGainTotal = 0;
				
				$positionCnt = 0;
				
				foreach($aGains[$fundID] as $key=>$gain){
					
					$positionCnt++;
					
					if($gain < 0){
						$negGainCnt++;	
						$negGainTotal = $negGainTotal + $gain;
					}elseif($gain > 0){
						$posGainCnt++;
						$posGainTotal = $posGainTotal + $gain;	
					}
				}
				
				$winningPercent = ($posGainCnt / $positionCnt) * 100;
				$gainLossRatio = $posGainTotal / abs($negGainTotal);
				
				$nGain = $posGainTotal / $posGainCnt;
				$dLoss = $negGainTotal / $negGainCnt;
				
				$avgGainLoss = $nGain / abs($dLoss);
				
				$query = "
					SELECT *
					FROM ".$_SESSION['fund_aggregate_table']."
					WHERE fund_id=:fundID
				";
				try{
				$rsAGG = $mLink->prepare($query);
					$aValues = array(
						':fundID' 	=> $fundID
						
					);
					$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
					$rsAGG->execute($aValues);
				}
				
				catch(PDOException $error){
					// Log any error
					file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
				}
				$agg = $rsAGG->fetch(PDO::FETCH_ASSOC);
				
				$AAR = get_fund_AAR($mLink, $fundID);
				
				$aTracking[$fundID] = array(
					'memberID'			=> $memberID,
					'firstName'			=> $aMember['firstName'],
					'lastName'			=> $aMember['lastName'],
					'fullName'			=> $aMember['fullName'],
					'username'			=> $aMember['username'],
					'fundID'			=> $fundID,
					'fundSymbol'		=> $fundSymbol,
					'fundName'			=> $fundName,
					'compliant'			=> $compliant,
					'percentCash'		=> $percentCash,
					'cashValue'			=> $cashValue,
					'totalValue'		=> $totalValue,
					'nav'				=> $aLivePrice['nav'],
					'positionCnt'		=> $positionCnt,
					'posGains'			=> $posGainCnt,
					'winLossRatio'		=> $winningPercent,
					'avgGainLoss'		=> $avgGainLoss,
					'returnLastWeek'	=> $aAgg['returnLastWeek'],
					'currentReturn'		=> $aAgg['currentReturn'],
					'mtd'				=> $agg['MTDReturn'],
					'qtd'				=> $agg['QTDReturn'],
					'ytd'				=> $agg['YTDReturn'],
					'aar'				=> $AAR
				);
				
			}	
			
		}
		
		$cntTrackers = count($aTracking);
		
		foreach($aTracking as $fundID=>$aFundInfo){
			
			$memberID		= $aFundInfo['memberID'];
			$fullName		= $aFundInfo['fullName'];
			$fundSymbol		= $aFundInfo['fundSymbol'];
			$compliant		= $aFundInfo['compliant'];
			$percentCash	= $aFundInfo['percentCash'];
			$nav			= $aFundInfo['nav'];
			$AAR			= $aFundInfo['aar'];
			$returnLastWeek	= $aFundInfo['returnLastWeek'];
			
			
			?>
			<tr>
				<td><a href="?page=04-00-00-001&member=<?php echo $memberID;?>" target="_blank"><?php echo $fullName;?></a></td>
				<td><a href="?page=04-00-00-001&member=<?php echo $memberID;?>&tab=<?php echo $fundID;?>" target="_blank"><?php echo $fundSymbol;?></a></td>
				<td><?php echo $compliant;?></td>
				<td><?php echo number_format($percentCash, 2, '.',',');?>%</td>
				<td>$<?php echo number_format($nav, 2, '.',',');?></td>
				<td><?php echo number_format($AAR, 2, '.',',');?>%</td>
				<td><?php echo number_format($returnLastWeek, 2, '.',',');?>%</td>
				<?php /*?><td><?php echo number_format($todayReturn, 2, '.',',');?>%</td><?php */?>
				<td><a href="javascript:void(0);" onclick="removeTracking('<?php echo $fundID;?>','<?php echo $userID;?>','<?php echo $fundSymbol;?>');"><i class="icon-ban-circle"></i> Stop Tracking</a></td>
			</tr>
			<?php
		}
		
		
		/*echo '<tr><td colspan="9"><pre>';
       	print_r($aTracking);
        echo '</pre></td></tr>';		*/
		
		/*if($cntTrackers == 0){
		
			echo '<tr><td colspan="9">You are not tracking any managers.</td></tr>';
			
		}*/
		
	break;
	
	//+-----------------------------------------------------------------------------------------------------------------------+
	//													Load Top Ten Stocks
	//+-----------------------------------------------------------------------------------------------------------------------+
	case 'load-top-ten-stocks':
		
		$aTop10 	= array();
		$now		= time();
		$today 		= mktime(0,0,0,date('m',$now),date('d',$now),date('Y',$now));
		$buildList	= true;
		
		$query = "
			SELECT *
			FROM ".$_SESSION['top_10_table']."
			ORDER BY funds_held DESC
		";
		try{
			$rsTop = $mLink->prepare($query);
			$aValues = array();
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsTop->execute($aValues);
		}
		
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		while($top = $rsTop->fetch(PDO::FETCH_ASSOC)){
			$aTop10[$top['stock_symbol']] = $top['funds_held'];
			
			if($top['timestamp'] > $today){
				$buildList = false;	
			}
		}
		
		if($buildList == true){
			$aStocks = array();
			$aStockCount = array();
			$aTop10 	= array();
			
			$query = "
				SELECT DISTINCT(stockSymbol) 
				FROM `members_fund_stratification_basic` 
			";
			try{
				$rsStocks = $mLink->prepare($query);
				$aValues = array();
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsStocks->execute($aValues);
			}
			
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
			while($stock = $rsStocks->fetch(PDO::FETCH_ASSOC)){
				$aStocks[] = $stock['stockSymbol'];	
			}
			
			foreach($aStocks as $key=>$stockSymbol){
				
				$query = "
					SELECT stockSymbol
					FROM `members_fund_stratification_basic`
					WHERE stockSymbol=:stockSymbol
				";
				try{
					$rsStocks = $mLink->prepare($query);
					$aValues = array(
						':stockSymbol'	=> $stockSymbol
					);
					$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
					$rsStocks->execute($aValues);
				}
				
				catch(PDOException $error){
					// Log any error
					file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
				}
				$numberHeld = $rsStocks->rowCount();
				
				$aStockCount[$stockSymbol] = $numberHeld;
					
			}
			
			unset($aStockCount[99999]);
			
			arsort($aStockCount);
			
			$cnt = 0;
			
			$query = "
				TRUNCATE TABLE ".$_SESSION['top_10_table']."
			";
			try{
				$rsEmpty = $mLink->prepare($query);
				$aValues = array();
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsEmpty->execute($aValues);
			}
			
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
			
			foreach($aStockCount as $stockSymbol=>$numberFunds){
				
				
				
				$cnt++;
				
				
				
				if($cnt > 10){
					continue;	
				}else{
					/*echo '
					<tr>
						<td>'.$stockSymbol.'</td>
						<td>'.$numberFunds.'</td>
					</tr>
					';*/
					
					$aTop10[$stockSymbol] = $numberFunds;
					
					$query = "
						INSERT INTO ".$_SESSION['top_10_table']." (
							stock_symbol,
							funds_held,
							timestamp
						)VALUES(
							:stock_symbol,
							:funds_held,
							UNIX_TIMESTAMP()
						)
					";
					try{
						$rsInsert = $mLink->prepare($query);
						$aValues = array(
							':stock_symbol'	=> $stockSymbol,
							':funds_held'	=> $numberFunds
						);
						$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
						$rsInsert->execute($aValues);
					}
					
					catch(PDOException $error){
						// Log any error
						file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
					}
				}
					
			}
		}//end if build list true
		
		foreach($aTop10 as $stockSymbol=>$numberFunds){
			echo '
				<tr>
					<td>'.$stockSymbol.'</td>
					<td>'.$numberFunds.'</td>
				</tr>
			';	
		}
		
		/*echo '
			<tr>
				<td colspan="2"><pre>'.$error.'</pre></td>
			</tr>
		';*/
	break;
	
	//+-----------------------------------------------------------------------------------------------------------------------+
	//													Load Articles
	//+-----------------------------------------------------------------------------------------------------------------------+
	case 'load-articles':
		
		//echo '<tr><td colspan="7">Test</td></tr>';
		
		$query = "
			SELECT *
			FROM ".$_SESSION['members_articles_table']."
			WHERE member_id=:member_id AND deleted='0'
		";
		try{
			$rsGetArticles = $mLink->prepare($query);
			$aValues = array(
				':member_id'	=> $_SESSION['member_id']
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsGetArticles->execute($aValues);
		}
		
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		
		$cnt = 0;
		while($article = $rsGetArticles->fetch(PDO::FETCH_ASSOC)){
			
			$cnt++;
			
			$published		= $article['published'];
			$articleType	= $article['article_type'];
			
			switch($articleType){
				case 'article':$articleType = 'Marketocracy Article';break;
				case 'forbes':$articleType = "Forbes Article";break;
				case 'blog':$articleType = "Blog Article";break;
				case 'ext-article':$articleType = 'External Article';break;	
				case 'seekingalpha':$articleType = 'Seeking Alpha Article';break;
			}
			
			if($published == '1'){
				$actionBtns = '<button type="button" class="btn btn-info btn-sm" data-toggle="modal" data-target="#edit-article" onclick="editArticle(\'load\',\''.$article['article_id'].'\');"><i class="icon-pencil"></i> Edit</button> <button type="button" class="btn btn-danger btn-sm" onclick="editArticle(\'delete\',\''.$article['article_id'].'\');">Delete</button>';
				$status = "Published";	
			}else{
				$actionBtns	= '';
				$status = "Not Published";
			}
			
			?>
            <tr>
            	<td><?php echo $cnt;?></td>
                <td><?php echo $article['article_title'];?></td>
                <td><?php echo date('m/d/Y h:i a', $article['publish_date']);?></td>
                <td><?php echo $articleType;?></td>
                <td><?php echo date('m/d/Y h:i a',$article['timestamp']);?></td>
                <td><?php echo $status;?></td>
                <td><?php echo $article['views'];?></td>
                <td><?php echo $actionBtns;?></td>
            </tr>
            <?php
			
		}
		
	break;
	
	//+-----------------------------------------------------------------------------------------------------------------------+
	//													Load Emails
	//+-----------------------------------------------------------------------------------------------------------------------+
	case 'load-emails':
		
		$memberID = $_REQUEST['memberID'];
		
		$query = "
			SELECT *
			FROM ".$_SESSION['email_campaigns_table']."
			WHERE member_id=:member_id AND deleted='0' && camp_type='manager_campaign' OR member_id=:member_id AND deleted='0' && camp_type='monthly_perf'
		";
		try{
			$rsGetCamp = $mLink->prepare($query);
			$aValues = array(
				':member_id'	=> $memberID
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsGetCamp->execute($aValues);
		}
		
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		
		$cnt = 0;
		$aJson = array();
		while($camp = $rsGetCamp->fetch(PDO::FETCH_ASSOC)){
		
			$cnt++;	
			
			$campID			= $camp['camp_id'];
			$scheduled 		= $camp['schedule_timestamp'];
			$campStatus		= $camp['camp_status'];
			$campType		= $camp['camp_type'];
			
			if($scheduled == NULL){
				$scheduled = 'N/A';
			}else{
				$scheduled = date('m/d/Y h:i a', $scheduled);
			}	
			
			$timestamp = $camp['timestamp'];
			
			switch($campStatus){
				
				case 'sent':
					$actionBtns = '
						
						<button type="button" data-toggle="modal" data-target="#report" onclick="loadCampaignReport(\''.$campID.'\');" class="btn btn-warning btn-sm"><i class="icon-file"></i>&nbsp;&nbsp;View Report</button>
						<button type="button" class="btn btn-default btn-sm" data-toggle="modal" data-target="#preview-email" onclick="loadEmailPreview(\''.$campID.'\');"><i class="icon-eye-open"></i> Preview</button>
					';
				break;
				
				case 'draft':
					switch($campType){
						
						case 'manager_campaign':
							$actionBtns = '
								<button type="button" class="btn btn-default btn-sm" data-toggle="modal" data-target="#preview-email" onclick="loadEmailPreview(\''.$campID.'\');"><i class="icon-eye-open"></i> Preview</button>
								<button type="button" class="btn btn-info btn-sm" data-toggle="modal" data-target="#edit-email-campaign" onclick="loadEditEmail(\''.$campID.'\');"><i class="icon-pencil"  ></i> Edit</button> 
								<button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#send-campaign" onclick="loadSendEmail(\''.$campID.'\');"><i class="icon-arrow-right"></i> Send</button>
							';
						break;
						
						case 'monthly_perf':
							$actionBtns = '
								<button type="button" class="btn btn-default btn-sm" data-toggle="modal" data-target="#preview-email" onclick="loadEmailPreview(\''.$campID.'\');"><i class="icon-eye-open"></i> Preview</button> 
								<!--<button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#confirm" onclick="loadConfirmEmail(\''.$campID.'\');"><i class="icon-arrow-right"></i> Confirm</button>-->
							';
						break;
							
					}
					
				break;
				
				case 'sending':
					$actionBtns = '
						<button type="button" class="btn btn-default btn-sm" disabled>Sending...</button>
					';
				break;
				
			}
			
			echo '
				<tr>
					<td>'.$cnt.'</td>
					<td>'.$camp['camp_name'].'</td>
					<td>'.$camp['email_subject'].'</td>
					<td>'.$scheduled.'</td>
					<td>'.date('m/d/Y h:i a', $timestamp).'</td>
					<td>'.ucfirst($camp['camp_status']).'</td>
					<td>'.$actionBtns.'</td>
				</tr>
			';
			
			//$aJson[] = array($cnt, $camp['camp_name'], $camp['email_subject'], $scheduled, date('m/d/Y h:i a', $timestamp), ucfirst($camp['camp_status']), $actionBtns);
			
		}
		
		/*echo '{ "data": ';
		echo json_encode($aJson);
		echo '}';*/
		
		
		
		
		/*echo '
			<tr>
				<td colspan="7">'.$error.'</td>
			</tr>
		';*/
	break;
	
	case 'load-confirm-email':
		
		$campID = $_REQUEST['campID'];
		
		?>
        <form action="" method="post" name="confirm-form" class="confirm-form">
            <div class="modal-body">
                
                <div id="confirm-errors"></div>
                
                <div class="form-group">
                    <label class="control-label">Select a Status:</label>
                    <select class="form-control" name="status">
                    	<option value="good">Email is Good To Go</option>
                        <option value="error">Email Has Errors</option>
                        <!--<option value="schedule">Schedule For Date/Time</option>-->
                    </select>
                </div>
                <input type="hidden" name="camp_id" value="<?php echo $campID;?>" />
            
            </div><!--modal-body-->
                
            <div class="modal-footer">
                <span id="confirm-processing-btn">
                    <input type="submit" value="Save Status" id="confirm-btn" class="btn btn-info" />
                </span>
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </form>
        <?php
		
	break;
	
	case 'save-confirm-status':
	
	break;
	
	case 'load-send-email':
		
		$campID = $_REQUEST['campID'];
		
		?>
        <form action="" method="post" name="send-campaign-form" class="send-campaign-form">
            <div class="modal-body">
                
                <div id="send-campaign-errors"></div>
                
                <div class="form-group">
                    <label class="control-label">When to send:</label>
                    <select class="form-control">
                        <option value="now">Now</option>
                        <!--<option value="schedule">Schedule For Date/Time</option>-->
                    </select>
                </div>
                <input type="hidden" name="camp_id" value="<?php echo $campID;?>" />
            
            </div><!--modal-body-->
                
            <div class="modal-footer">
                <span id="send-campaign-processing-btn">
                    <input type="submit" value="Send Campaign" id="send-campaign-btn" class="btn btn-info" />
                </span>
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </form>
        <?php
		
	break;
	
	case 'send-campaign':
		
		
		
		//echo 'alert-danger';
		
		$campID = $_REQUEST['camp_id'];
		
		$query = "
			SELECT *
			FROM ".$_SESSION['email_campaigns_table']."
			WHERE camp_id=:camp_id
		";
		
		try{
			$rsCampaign = $mLink->prepare($query);
			$aValues = array(
				':camp_id' 	=> $campID
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsCampaign->execute($aValues);
		}
		
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		
		//$aErrors[] = $error;
		
		$camp = $rsCampaign->fetch(PDO::FETCH_ASSOC);
		
		$campName		= $camp['camp_name'];
		$campType		= $camp['camp_type'];
		$memberID		= $camp['member_id'];
		$list			= $camp['recipient_list'];
		$emailTitle		= $camp['email_title'];
		$emailSubject	= $camp['email_subject'];
		$emailHeadline	= $camp['email_headline'];
		$emailBody		= $camp['email_body'];
		$emailClosing	= $camp['email_closing'];
		$scheduleDate	= $camp['schedule_timestamp'];
		$tempID			= $camp['temp_id'];	
		
		//GET THE USERNAME OF THE MEMBER
		$query = "
			SELECT username
			FROM members
			WHERE member_id=:member_id
		";
		try{
			$rsMember = $mLink->prepare($query);
			$aValues = array(
				':member_id'	=> $memberID
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsMember->execute($aValues);
		}
		
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		$member = $rsMember->fetch(PDO::FETCH_ASSOC);
		$username	= $member['username'];
		
		//$aErrors[] = $tempID;
		
		//GET TEMPLATE INFO
		$query = "
			SELECT *
			FROM email_templates
			WHERE temp_id=:temp_id
		";
		try{
			$rsTemplate = $mLink->prepare($query);
			$aValues = array(
				':temp_id'	=> $tempID
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsTemplate->execute($aValues);
		}
		
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		$template = $rsTemplate->fetch(PDO::FETCH_ASSOC);
		
		$templateHTML	= $template['html_source'];
		$templateFrom	= $template['headers_from'];
		
		
		switch($list){
		
			case 'f-f':
				
				$query = "
					SELECT *
					FROM members_fund_tracking
					WHERE member_id=:member_id AND tracker_code IS NOT NULL AND unsubscribe='0' AND manager_updates='1'
				";
				
			break;
			
			case 'trackers':
				$query = "
					SELECT *
					FROM members_fund_tracking
					WHERE member_id=:member_id AND unsubscribe='0' AND manager_updates='1'
				";
			break;
		
		}
		
		try{
			$rsGetList = $mLink->prepare($query);
			$aValues = array(
				':member_id'	=> $memberID
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsGetList->execute($aValues);
		}
		
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		$aRecipients = array();
		while($list = $rsGetList->fetch(PDO::FETCH_ASSOC)){
			
			$aRecipients[$list['email']] = array(
				'email_address'		=> $list['email'],
				'recepient_vars'	=> array(
					'name_first'		=> $list['first_name'],
					'name_last'			=> $list['last_name'],
					'email_code'		=> $list['code'],
					'manager_username'	=> $username
				)
			);
			
			
		}
		
		//UPDATE CAMPAIGN
		$query = "
			UPDATE email_campaigns
			SET camp_status='sending'
			WHERE camp_id=:camp_id
		";
		try{
			$rsUpdateCampaign = $mLink->prepare($query);
			$aValues = array(
				':camp_id' 	=> $campID
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsUpdateCampaign->execute($aValues);
		}
		
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		//$aErrors[] = $error;
		//END UPDATE CAMPAIGN
		
		$numberRecipients = count($aRecipients);
		
		/*$aRecipients['ken@marketocracy.com'] = array(
			'name_first'		=> 'Ken',
			'name_last'			=> 'Kam',
			'email_code'		=> 'X'
		);*/
		/*$aRecipients['brandon.mccarthy@marketocracy.com'] = array(
			'name_first'		=> 'Brandon',
			'name_last'			=> 'McCarthy',
			'email_code'		=> 'X'
		);*/
		
		$aEmail = array(
			'camp_id'			=> $campID,
			'camp_type'			=> $campType,
			'camp_name'			=> $campName,
			'camp_status'		=> $campStatus,
			'temp_id'			=> $tempID,
			'recipient_list'	=> $sendTo,
			'email_title'		=> $emailTitle,
			'email_subject'		=> $emailSubject,
			'email_headline'	=> $emailHeadline,
			'email_body'		=> $emailBody,
			'email_closing'		=> NULL,
			'schedule_date'		=> NULL,
			'recipients'		=> $aRecipients,
			'active_check'		=> true
		);
		
		echo '<pre>';
		print_r($aRecipients);
		echo '</pre';
		
		$campaignStatus = sendEmail($mLink, $aEmail, $aEmailVars, false, true);
		
		echo '<pre>';
		print_r($campaignStatus);
		echo '</pre>';
		
		//UPDATE CAMPAIGN INFO
		$query = "
			UPDATE email_campaigns
			SET camp_status='sent', sent_timestamp=UNIX_TIMESTAMP(), modified=UNIX_TIMESTAMP(), recipients=:recipients
			WHERE camp_id=:camp_id
		";
		try{
			$rsUpdateCampaign = $mLink->prepare($query);
			$aValues = array(
				':camp_id' 		=> $campID,
				':recipients'	=> $numberRecipients
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsUpdateCampaign->execute($aValues);
		}
		
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		//$aErrors[] = $error;
		//END UPDATE CAMPAIGN
		
		
		if(!empty($aErrors)){
			echo '<div class="alert alert-danger"><ul>';
			foreach($aErrors as $key=>$error){
				echo '<li>'.$error.'</li>';	
			}
			echo '</ul></div>';	
		}
		
	break;
	
	case 'load-edit-email':
		
		$campID = $_REQUEST['campID'];
		
		$query = "
			SELECT * FROM ".$_SESSION['email_campaigns_table']."
			WHERE camp_id=:camp_id
		";
		try{
			$rsGetCamp = $mLink->prepare($query);
			$aValues = array(
				':camp_id'	=> $campID
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsGetCamp->execute($aValues);
		}
		
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		$camp = $rsGetCamp->fetch(PDO::FETCH_ASSOC);
		
		
		?>
        <form action="" method="post" name="edit-campaign-form" class="edit-campaign-form">
            <div class="modal-body">
                    <div id="save-campaign-errors"></div>
                    <div class="form-body">
                        
                        <h4>Campaign Info</h4>
                        <div class="form-group">
                            <label class="control-label">Campaign Name:*</label>
                            <input type="text" class="form-control" name="camp_name" value="<?php echo $camp['camp_name'];?>" />
                        </div><!--form-group-->
                        
                        <div class="form-group">
                            <label class="control-label">Send To:*</label>
                            
                            <?php
							switch($camp['recipient_list']){
								case 'trackers': $selectTrackList = 'selected';$selectFFlist = '';break;
								case 'f-f': $selectTrackList = '';$selectFFlist = 'selected';break;
							}	
							?>
                            <select name="send-to" class="form-control">
                                <option value="trackers" <?php echo $selectTrackList;?>>Trackers List</option>
                                <option value="f-f" <?php echo $selectFFlist;?>>Friends And Family List</option>
                            </select>
                        </div>
                    
                        <div class="form-group">
                            <label class="control-label">Select Template:*</label>
                            <select name="email-template" class="form-control" id="select-template">
                                <option value="x">Choose Template</option>
                                <option value="20">Test</option>
                                <?php
                                $query = "
                                    SELECT temp_title, temp_id
                                    FROM ".$_SESSION['email_templates_table']."
                                    WHERE camp_type='manager_campaign'
                                ";
                                try{
                                    $rsTemplate = $mLink->prepare($query);
                                    $aValues = array();
                                    $preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
                                    $rsTemplate->execute($aValues);
                                }
                                
                                catch(PDOException $error){
                                    // Log any error
                                    file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
                                }
                                while($template = $rsTemplate->fetch(PDO::FETCH_ASSOC)){
									
									if($camp['temp_id'] == $template['temp_id']){
										$showSelected = 'selected';	
									}else{
										$showSelected = '';
									}
									
                                    echo '<option value="'.$template['temp_id'].'" '.$showSelected.'>'.$template['temp_title'].'</option>';	
                                }
                                ?>
                            </select>
                        </div>
                        <hr />
                        <h4>Email Info</h4>
                        <div id="load-template-options"></div>
                        
                        <div class="form-group">
                            <label class="control-label">Email Title*</label>
                            <input type="text" class="form-control" name="email_title" value="<?php echo $camp['email_title'];?>" />
                        </div>
                        <div class="form-group">
                            <label class="control-label">Subject Line*</label>
                            <input type="text" class="form-control" name="email_subject" value="<?php echo $camp['email_subject'];?>" />
                        </div>
                        <div class="form-group">
                            <label class="control-label">Headline</label>
                            <input type="text" class="form-control" name="email_headline" value="<?php echo $camp['email_headline'];?>" />
                        </div>
                        <div class="form-group">
                            <label class="control-label">Body*</label>
                            <textarea type="text" class="form-control email-body" name="email_body" rows="10" id="email-body-<?php echo $campID;?>"><?php echo $camp['email_body'];?></textarea>
                        </div>
                        
                       <input type="hidden" name="camp_id" value="<?php echo $campID;?>" />
                        
                        <a href="javascript:void(0);" onclick="toggleID('format-help-<?php echo $campID;?>');">Toggle Formatting Help</a>
                        <div class="note note-info" id="format-help-<?php echo $campID;?>" style="display:none;">
                            <h3>Formatting Help</h3>
                            <table width="100" cellpadding="5" cellspacing="5"  class="table">
                                            <thead>
                                                <tr>
                                                    <td><strong>Code</strong></td>
                                                    <td><strong>Result</strong></td>
                                                    <td><strong>Description</strong></td>
                                                </tr>
                                            </thead>
                                            <tbody>

                                                <tr>
                                                    <td>[Link Text](http://example.com)</td>
                                                    <td><a href="http://example.com" target="_blank">Link Text</a></td>
                                                    <td>Trackable Link</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                        
                                        
                                        <h3>Email Tags</h3>
                                        <table width="100" cellpadding="5" cellspacing="5"  class="table">
                                            <thead>
                                                <tr>
                                                    <th><strong>Tag</strong></th>
                                                    <th><strong>Result</strong></th>
                                                    <th><strong>Description</strong></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>[NAME_FIRST]</td>
                                                    <td>Brandon</td>
                                                    <td align="left">Adds recipients first name. Brackets must be in all caps.</td>
                                                </tr>
                                                
                                            </tbody>
                                        </table>
                                    </div>
                        </div>
                    
                        <hr />
                        <h4>Testing</h4>
                        
                        <div class="form-group">
                            <label class="control-label">Send Test
                            <input type="checkbox" class="form-control send-test-check" name="send_test" value="1" /></label>
                        </div>
                        <div class="form-group">
                            <label class="control-label">Test Email Address</label>
                            <input type="text" class="form-control" name="test_email" value="Ken:kkam@mac.com" />
                        </div>
                        
                        
                    </div><!--form-body-->
                
            </div><!--modal-body-->
            
            <div class="modal-footer">
                <span class="email-processing-btn">
                                <input type="submit" value="Save Campaign" id="submit-email" class="btn btn-info" />
                            </span>
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
            </div>
        
        </form><!--send email-->
        <?php
		
	break;
	
	case 'load-email-preview':
		
		$campID = $_REQUEST['campID'];
		
		
		$query = "
			SELECT ect.*
			FROM ".$_SESSION['email_campaigns_table']." as ect
			WHERE ect.camp_id=:camp_id
		";
		try{
			$rsGetCamp = $mLink->prepare($query);
			$aValues = array(
				':camp_id'	=> $campID
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsGetCamp->execute($aValues);
		}
		
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		$camp = $rsGetCamp->fetch(PDO::FETCH_ASSOC);
		
		$memberID = $camp['member_id'];
		
		$asOfDate = $camp['asOfDate'];
		$unixAsOfDate	= mktime(8,0,0,substr($asOfDate,4,2),substr($asOfDate,6,2),substr($asOfDate,0,4));
				$lastMonth = strtotime('-1 month', $unixAsOfDate);
				$unixMonthStart	= mktime(8,0,0,date('m',$lastMonth),date('t',$lastMonth),date('Y',$lastMonth));
				
		//get template data
		$query = "
			SELECT *
			FROM email_templates
			WHERE temp_id=:temp_id
		";
		try{
			$rsTemp = $mLink->prepare($query);
			$aValues = array(':temp_id'=>$camp['temp_id']);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsTemp->execute($aValues);
		}
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		$tempInfo = $rsTemp->fetch(PDO::FETCH_ASSOC);

		if($camp['email_headline'] == '' || $camp['email_headline'] == NULL){
			$emailHeadline = $tempInfo['temp_headline'];
		}else{
			$emailHeadline = $camp['email_headline'];
		}
		if($camp['email_body'] == '' || $camp['email_body'] == NULL){
			$emailBody = $tempInfo['temp_body'];
		}else{
			$emailBody = $camp['email_body'];
		}
		
		if($camp['temp_id'] == '22'){
			//START | Get fund Information
			$aFunds = get_fund_symbols($mLink, $memberID, 'funds', true);
			
			//print_r($aFunds);
			
			foreach($aFunds as $fundID=>$fundSymbol){
				
				$query 			= "
					SELECT m.username, m.name_first, m.name_last, mf.composite_fund, mf.unix_date, mf.fund_symbol, mf.fund_name, mfa.MTDReturn, mfa.QTDReturn, mfa.YTDReturn, mfa.sp500MTDReturn, mfa.sp500QTDReturn, mfa.sp500YTDReturn, mfa.unix_date as asOfDate
					FROM members AS m
					INNER JOIN members_fund AS mf ON mf.member_id=m.member_id
					INNER JOIN members_fund_aggregate_history AS mfa ON mfa.fund_id=mf.fund_id
					WHERE m.member_id=:member_id AND mf.fund_id=:fund_id
					ORDER BY mfa.unix_date DESC
					LIMIT 1
				";
				try{
					$rsFundInfo 	= $mLink->prepare($query);
					$aValues 		= array(
						':member_id' 	=> $memberID,
						':fund_id'		=> $fundID
					);
					$preparedQuery 	= str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
					$rsFundInfo	->execute($aValues);
				}
				
				catch(PDOException $error){
					// Log any error
					file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
				}
				
				//echo $preparedQuery;
				$fundInfo 	= $rsFundInfo->fetch(PDO::FETCH_ASSOC);
				
				$managerName			= $fundInfo['name_first'].' '.$fundInfo['name_last'];
				$managerUsername		= $fundInfo['username'];
				
				
				
				$aFunds[$fundID] = array(
					'composite_fund'	=> $fundInfo['composite_fund'],
					'asOfDate'			=> $fundInfo['asOfDate'],
					'fund_symbol'		=> $fundInfo['fund_symbol'],
					'fund_name'			=> $fundInfo['fund_name'],
					'MTDReturn'			=> $fundInfo['MTDReturn'],
					'QTDReturn'			=> $fundInfo['QTDReturn'],
					'YTDReturn'			=> $fundInfo['YTDReturn'],
					'sp500MTDReturn'	=> $fundInfo['sp500MTDReturn'],
					'sp500QTDReturn'	=> $fundInfo['sp500QTDReturn'],
					'sp500YTDReturn'	=> $fundInfo['sp500YTDReturn'],
				);
					
			}//END foreach $aFunds
			//END | Get fund Info
			
			#get articles
		$aArticles = array();
		
		$query = "
			SELECT *
			FROM members_profile_articles
			WHERE member_id=:member_id AND published='1' AND deleted='0' AND publish_date>:publish_date
		";
		try{
			$rsArticles = $mLink->prepare($query);
			$aValues = array(
				'member_id'		=> $memberID,
				'publish_date'	=> $unixMonthStart
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsArticles->execute($aValues);
		}
		
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		$articleCnt = 0;				
		while($article = $rsArticles->fetch(PDO::FETCH_ASSOC)){
			
			$articleCnt++;
			
			$aArticles[$article['article_id']] = array(
				'article_type'	=> $article['article_type'],
				'article_title'	=> $article['article_title'],
				'publish_date'	=> $article['publish_date'],
				'article_link'	=> $article['article_link']
			);
			
		}
		
		//print_r($aArticles);
		
		if($articleCnt > 0){
			
			$showArticle = '<hr style="width:560px; height:1px; background-color:#EEEEEE; border:none">
			<h3>Articles</h3><table width="100%" cellspacing="0" cellpadding="10" style="border-radius:5px;text-align:center;">
							<thead>
								<tr>
									<th bgcolor="#FCB322" style="text-align:left;color:#ffffff;border:1px solid #999999;border-left:2px solid #999999;border-top:2px solid #999999;">Publish Date</th>
									<th bgcolor="#5BC0DE" style="color:#ffffff;border:1px solid #999999;border-top:2px solid #999999;">Article</th>
									
								</tr>
							</thead>
							<tbody>';
							
			foreach($aArticles as $articleID=>$article){
				$showArticle .= '
				<tr>
					<td style="text-align:left;border:1px solid #999999;border-left:2px solid #999999;">'.date('m/d/Y', $article['publish_date']).'</td>
					<td style="text-align:left;border:1px solid #999999;border-left:2px solid #999999;"><a href="'.$article['article_link'].'" target="_blank" style="display:block;color:#333333;width:100%;" title="'.$article['article_title'].'"><strong>'.$article['article_title'].'</strong></a></td>
					
				</tr>
				';	
			}
			$showArticle .= '
					</tbody>
				</table>';
			
				
		}else{
			$showArticle = '';	
		}
			
			
			#START | Build fund Info
			$fundPerfTable = '<table width="100%" cellspacing="0" cellpadding="10" style="border-radius:5px;text-align:center;">
								<thead>
									<tr>
										<th bgcolor="#FCB322" style="text-align:left;color:#ffffff;border:1px solid #999999;border-left:2px solid #999999;border-top:2px solid #999999;">Fund</th>
										<th bgcolor="#5BC0DE" style="color:#ffffff;border:1px solid #999999;border-top:2px solid #999999;">MTD</th>
										<th bgcolor="#5BC0DE" style="color:#ffffff;border:1px solid #999999;border-top:2px solid #999999;">QTD</th>
										<th bgcolor="#422A88" style="color:#ffffff;border: 2px solid #422A88;border-bottom:2px solid #311f66;">YTD</th>
										
									</tr>
								</thead>
								<tbody>';
			
			foreach($aFunds as $fundID=>$fundInfo){
				
				if($fundInfo['fund_symbol'] == ''){
					continue;	
				}
				
				$mtrLink		= 'https://'.str_replace('_','-',$managerUsername).'.mytrackrecord.com/'.$fundInfo['fund_symbol'];
				
				$mMTD			= number_format($fundInfo['MTDReturn'],2,'.',',');
				$mQTD			= number_format($fundInfo['QTDReturn'],2,'.',',');
				$mYTD			= number_format($fundInfo['YTDReturn'],2,'.',',');
				$spMTD			= number_format($fundInfo['sp500MTDReturn'],2,'.',',');
				$spQTD			= number_format($fundInfo['sp500QTDReturn'],2,'.',',');
				$spYTD			= number_format($fundInfo['sp500YTDReturn'],2,'.',',');
				
				if($mMTD < 0){
					$mtdNegColor = 'color:#ef1b1b;';
				}else{
					$mtdNegColor = '';
				}
				if($mQTD < 0){
					$qtdNegColor = 'color:#ef1b1b;';
				}else{
					$qtdNegColor = '';
				}
				if($mYTD < 0){
					$ytdNegColor = 'color:#ef1b1b;font-weight:bold;';
				}else{
					$ytdNegColor = '';
				}
				
				$fundPerfTable .= '<tr bgcolor="'.$rowColor.'">
										<td style="text-align:left;border:1px solid #999999;border-left:2px solid #999999;"><a href="'.$mtrLink.'" target="_blank" style="display:block;color:#333333;width:100%;" title="Full Profile"><strong>'.$fundInfo['fund_name'].' ('.$fundInfo['fund_symbol'].')</strong></a></td>
										<td style="border:1px solid #999999;'.$mtdNegColor.'">'.number_format($fundInfo['MTDReturn'],2,'.',',').'</td>
										<td style="border:1px solid #999999;'.$qtdNegColor.'">'.number_format($fundInfo['QTDReturn'],2,'.',',').'%</td>
										<td bgcolor="'.$lastCellColor.'" style="border:1px solid #422A88;border-left: 2px solid #422A88;border-right: 2px solid #422A88;'.$ytdNegColor.'">'.number_format($fundInfo['YTDReturn'],2,'.',',').'%</td>
									</tr>';
					
			}
			
			$fundPerfTable .= '<tr bgcolor="#DBE6F2">
								<td bgcolor="#006CA3" style="color:#ffffff; text-align:left;border:2px solid #006CA3;"><strong>S&P 500</strong></td>
								<td style="border:1px solid #999999;border-top:2px solid #006CA3;border-bottom:2px solid #006CA3;'.$spMTDcolor.'">'.$spMTD.'%</td>
								<td style="border:1px solid #999999;border-top:2px solid #006CA3;border-bottom:2px solid #006CA3;'.$spQTDcolor.'">'.$spQTD.'%</td>
								<td bgcolor="#97B9D7" style="border:2px solid #006CA3;'.$spYTDcolor.'">'.$spYTD.'%</td>
							</tr>
						</tbody>
				</table><h3 style="color:red;">NOTE: Followers will only see the fund(s) they subscribe to.</h3>';	
		}
		
		
		$htmlSource 	= $tempInfo['html_source'];
		$emailTitle		= $camp['email_title'];
		
		$emailClosing	= NULL/*$camp['email_closing']*/;
		
		$preview	= true;
		
		include('../../includes/email/templates/'.$htmlSource);
		
		echo $message;
		
	break;
	
	//+-----------------------------------------------------------------------------------------------------------------------+
	//													Save Email Campaign Changes
	//+-----------------------------------------------------------------------------------------------------------------------+
	case 'save-email-campaign':
		
		$campID			= $_REQUEST['camp_id'];
		$campName 		= $_REQUEST['camp_name'];
		$sendTo			= $_REQUEST['send-to'];
		$templateID		= $_REQUEST['email-template'];
		$campType		= 'manager_campaign';
		$campStatus		= 'draft';
		
		$emailTitle		= $_REQUEST['email_title'];
		$emailSubject	= $_REQUEST['email_subject'];
		$emailHeadline	= $_REQUEST['email_headline'];
		$emailBody		= $_REQUEST['email_body'];
		
		$sendTest		= $_REQUEST['send_test'];
		$testEmail		= $_REQUEST['test_email'];
		
		$aErrors		= array();

		
		if($sendTest == '1'){
			
			if(empty($testEmail)){
				$aErrors[] = 'Please provide a test name and email address in this format: Brandon:brandon.mccarthy@marketocracy.com';	
			}else{
				if (strpos($testEmail, ':') !== false) {
					//echo 'true';
				}else{
					$aErrors[] = 'Please properly format the test email field, example: Brandon:brandon.mccarthy@marketocracy.com';	
				}
			}
			
		}

		/*$emailBody = preg_replace_callback(
			'~\[([^\]]+)\]\(([^)]+)\)~',
			function ($m) {
				return '<a href="'.$_SESSION['base_url'].'includes/email/tracking.php?track=subscription~[TRACK_ID]~'.$m[2].'~[TRACKER_CODE]~[EMAIL_ADDRESS]" target="_blank">'.substr($m[1],0,80).'</a>';
			},
			$emailBody
		);*/

		$emailBody = preg_replace_callback(
			'~\[([^\]]+)\]\(([^)]+)\)~',
			function ($m) {
				return '<a href="http://portfolio.marketocracy.com/includes/email/tracking.php?track=clicked~[TRACK_ID]~'.$m[2].'~[TRACKER_CODE]~[EMAIL_ADDRESS]">'.substr($m[1],0,80).'</a>';
			},
			$emailBody
		);
		
		if(empty($campName)){
			$aErrors[]	= 'Please provide a name for this email campaign.';
		}else{
			
			$query = "
				SELECT camp_name
				FROM ".$_SESSION['email_campaigns_table']."
				WHERE camp_name=:camp_name AND member_id=:member_id AND camp_id<>:camp_id
			";
			try{
				$rsGetCamp = $mLink->prepare($query);
				$aValues = array(
					':camp_name'	=> strtolower($campName),
					':member_id'	=> $_SESSION['member_id'],
					':camp_id'		=> $campID
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsGetCamp->execute($aValues);
			}
			
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
			
			$rowCount = $rsGetCamp->rowCount();
			
			if($rowCount > 0){
				$aErrors[] = 'Campaign Name already in use. Please choose a different campaign name.';	
			}
		}
		
		if($templateID == 'x'){
			$aErrors[]	= 'Please select an email template.';
		}	
		
		if(empty($emailTitle)){
			$aErrors[]	= 'Please provide a title for this email.';
		}
		
		if(empty($emailSubject)){
			$aErrors[]	= 'Please provide a subject for this email.';
		}
		
		/*if(empty($emailHeadline)){
			$aErrors[]	= 'Please provide a headline for this email.';
		}*/
		
		if(empty($emailBody)){
			$aErrors[]	= 'Please provide content in the body for this email.';
		}
		
		
		//No errors start processing
		if(empty($aErrors)){
			
			if($sendTest == '1'){
				$aTestEmail = explode(':',$testEmail);
				
				$testEmailAddress 	= $aTestEmail[1];
				$testEmailName		= $aTestEmail[0];
				
				$aRecipients 	=	array(
					
					'0'	=> array(
						'email_address'		=> 'brandon.mccarthy@marketocracy.com',
						'recepient_vars'	=> array(
							'name_first'	=> 'Brandon'
						)
					)
				);
				
				$aRecipients[1] =	 array(
						'email_address'		=> $testEmailAddress,
						'recepient_vars'	=> array(
							'name_first'	=> $aTestEmail[0]
						)
					);
				
				$aEmail = array(
					'camp_type'			=> $campType,
					'camp_name'			=> $campName,
					'camp_status'		=> $campStatus,
					'temp_id'			=> $templateID,
					'recipient_list'	=> $sendTo,
					'email_title'		=> $emailTitle,
					'email_subject'		=> $emailSubject,
					'email_headline'	=> $emailHeadline,
					'email_body'		=> $emailBody,
					'email_closing'		=> NULL,
					'schedule_date'		=> NULL,
					'recipients'		=> $aRecipients
				);
				
				$campaignStatus = sendTestEmail($mLink, $aEmail, $aEmailVars);
				
				/*echo '<pre>';
				print_r($campaignStatus);
				echo '</pre>';*/
				
				echo '<div class="alert alert-danger">
					<h3>Test Email Sent Successfully</h3>
					<p>You must unclick "Send Test" checkbox to save the campaign.</p>
				</div>';
			}else{
			
			
				$query = "
					UPDATE ".$_SESSION['email_campaigns_table']."
					SET camp_name=:camp_name, 
						recipient_list=:recipient_list, 
						temp_id=:email_template,
						email_title=:email_title,
						email_subject=:email_subject,
						email_headline=:email_headline,
						email_body=:email_body,
						modified=UNIX_TIMESTAMP()
					WHERE camp_id=:camp_id
				";
				try{
					$rsGetCamp = $mLink->prepare($query);
					$aValues = array(
						':camp_id'			=> $campID,
						':camp_name'		=> $campName,
						':recipient_list'	=> $sendTo,
						':email_template'	=> $templateID,
						':email_title'		=> $emailTitle,
						':email_subject'	=> $emailSubject,
						':email_headline'	=> $emailHeadline,
						':email_body'		=> $emailBody
					);
					$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
					$rsGetCamp->execute($aValues);
				}
				
				catch(PDOException $error){
					// Log any error
					file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
				}
			}
			
			
			/*echo '<div class="alert alert-danger">';
			echo $error;
			echo $preparedQuery;
			echo '</div>';*/
			
		}else{
			
			echo '<div class="alert alert-danger"><ul>';
			foreach($aErrors as $key=>$error){
				echo '<li>'.$error.'</li>';	
			}
			echo '</ul></div>';	
		}
		
		
	break;
	
	//+-----------------------------------------------------------------------------------------------------------------------+
	//													Create Campaign Emails
	//+-----------------------------------------------------------------------------------------------------------------------+
	case 'create-email-campaign':
	
		$post 			= $_POST;
		$memberID		= $_SESSION['member_id'];
		$campName 		= $_REQUEST['camp_name'];
		$sendTo			= $_REQUEST['sent-to'];
		$templateID		= $_REQUEST['email-template'];
		$campType		= 'manager_campaign';
		$campStatus		= 'draft';
		
		$emailTitle		= $_REQUEST['email_title'];
		$emailSubject	= $_REQUEST['email_subject'];
		$emailHeadline	= $_REQUEST['email_headline'];
		$emailBody		= $_REQUEST['email_body'];
		//$emailClosing	= $_REQUEST['email_closing'];
		
		$sendTest		= $_REQUEST['send_test'];
		$testEmail		= $_REQUEST['test_email'];
		
		
		
		$aErrors		= array();


		if($sendTest == '1'){
			
			if(empty($testEmail)){
				$aErrors[] = 'Please provide a test name and email address in this format: Brandon:brandon.mccarthy@marketocracy.com';	
			}else{
				if (strpos($testEmail, ':') !== false) {
					//echo 'true';
				}else{
					$aErrors[] = 'Please properly format the test email field, example: Brandon:brandon.mccarthy@marketocracy.com';	
				}
			}
			
		}

        $emailBody = preg_replace_callback(
			'~\[([^\]]+)\]\(([^)]+)\)~',
			function ($m) {
				return '<a href="http://portfolio.marketocracy.com/includes/email/tracking.php?track=clicked~[TRACK_ID]~'.$m[2].'~[TRACKER_CODE]~[EMAIL_ADDRESS]">'.substr($m[1],0,80).'</a>';
			},
			$emailBody
		);
		
		if(empty($campName)){
			$aErrors[]	= 'Please provide a name for this email campaign.';
		}else{
			
			$query = "
				SELECT camp_name
				FROM ".$_SESSION['email_campaigns_table']."
				WHERE camp_name=:camp_name AND member_id=:member_id
			";
			try{
				$rsGetCamp = $mLink->prepare($query);
				$aValues = array(
					':camp_name'	=> strtolower($campName),
					':member_id'	=> $_SESSION['member_id']
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsGetCamp->execute($aValues);
			}
			
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
			
			$rowCount = $rsGetCamp->rowCount();
			
			if($rowCount > 0){
				$aErrors[] = 'Campaign Name already in use. Please choose a different campaign name.';	
			}
		}
		
		if($templateID == 'x'){
			$aErrors[]	= 'Please select an email template.';
		}	
		
		if(empty($emailTitle)){
			$aErrors[]	= 'Please provide a title for this email.';
		}
		
		if(empty($emailSubject)){
			$aErrors[]	= 'Please provide a subject for this email.';
		}
		
		/*if(empty($emailHeadline)){
			$aErrors[]	= 'Please provide a headline for this email.';
		}*/
		
		if(empty($emailBody)){
			$aErrors[]	= 'Please provide content in the body for this email.';
		}
		
		/*if(empty($emailClosing)){
			$aErrors[]	= 'Please provide a closing for this email.';
		}*/
		
		//No errors start processing
		if(empty($aErrors)){
			
			if($sendTest == '1'){
				$aTestEmail = explode(':',$testEmail);
				
				$testEmailAddress 	= $aTestEmail[1];
				$testEmailName		= $aTestEmail[0];
				
				$aRecipients 	=	array(
					
					'0'	=> array(
						'email_address'		=> 'brandon.mccarthy@marketocracy.com',
						'recepient_vars'	=> array(
							'name_first'	=> 'Brandon'
						)
					)
				);
				
				$aRecipients[1] =	 array(
						'email_address'		=> $testEmailAddress,
						'recepient_vars'	=> array(
							'name_first'	=> $aTestEmail[0]
						)
					);
				
				$aEmail = array(
					'camp_type'			=> $campType,
					'camp_name'			=> $campName,
					'camp_status'		=> $campStatus,
					'temp_id'			=> $templateID,
					'recipient_list'	=> $sendTo,
					'email_title'		=> $emailTitle,
					'email_subject'		=> $emailSubject,
					'email_headline'	=> $emailHeadline,
					'email_body'		=> $emailBody,
					'email_closing'		=> NULL,
					'schedule_date'		=> NULL,
					'recipients'		=> $aRecipients
				);
				
				$campaignStatus = sendTestEmail($mLink, $aEmail, $aEmailVars);
				
				echo '<div class="alert alert-danger">
					<h3>Test Email Sent Successfully</h3>
					<p>You must unclick "Send Test" checkbox to save the campaign.</p>
				</div>';
			}else{
			
				$aEmail = array(
					'camp_type'			=> $campType,
					'camp_name'			=> $campName,
					'camp_status'		=> $campStatus,
					'temp_id'			=> $templateID,
					'recipient_list'	=> $sendTo,
					'email_title'		=> $emailTitle,
					'email_subject'		=> $emailSubject,
					'email_headline'	=> $emailHeadline,
					'email_body'		=> $emailBody,
					'email_closing'		=> NULL,
					'schedule_date'		=> NULL,
				);
				
				$campaignStatus = sendEmail($mLink, $aEmail, $aEmailVars, true, false);
				
			}
			
			
			
		}else{
			
			echo '<div class="alert alert-danger"><ul>';
			foreach($aErrors as $key=>$error){
				echo '<li>'.$error.'</li>';	
			}
			echo '</ul></div>';	
		}
		
		/*echo '<pre>';
		echo 'note-danger';
		print_r($campaignStatus);
		print_r($post);
		echo '</pre>';*/
	break;
	
	//+-----------------------------------------------------------------------------------------------------------------------+
	//													View Campaign Report
	//+-----------------------------------------------------------------------------------------------------------------------+
	case 'view-campaign-report':
	
		$campID = $_REQUEST['campID'];
		
		$query = "
			SELECT et.*, mft.first_name, mft.last_name, mft.tracker_code
			FROM email_tracking et, members_fund_tracking mft
			WHERE et.camp_id=:camp_id AND et.bounced IS NULL AND et.manager_id=mft.member_id AND et.email=mft.email
		";
		try{
			$rsTracking = $mLink->prepare($query);
			$aValues = array(
				':camp_id'	=> $campID
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsTracking->execute($aValues);
		}
		
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		
		echo $error;
		
		$trackerCnt = 0;
		$numOpened	= 0;
		$numClicked	= 0;
		$aList		= array();
			
		while($tracker = $rsTracking->fetch(PDO::FETCH_ASSOC)){
			
			$aList[$tracker['member_id']] = array(
				'name_first'	=> $tracker['first_name'],
				'name_last'		=> $tracker['last_name'],
				'username'		=> $tracker['username'],
				'opened'		=> $tracker['opened'],
				'open_ip'		=> $tracker['opened_ip'],
				'clicked'		=> $tracker['clicked'],
				'clicked_ip'	=> $tracker['clicked_ip'],
				'friendFamily'	=> $tracker['tracker_code'],
				'email'			=> $tracker['email']
			);
			
			$trackerCnt++;
			
			if($tracker['opened'] != NULL){
				$numOpened++;	
			}
			
			if($tracker['clicked'] == '1'){
				$numClicked++;	
			}
			
		}
		
		$openRatio 		= (($numOpened/$trackerCnt)*100);
		$clickedRatio	= (($numClicked/$trackerCnt)*100);
		
		?>
        <div class="profile-section">
            <h4>Email Activity</h4>
            
            <ul class="stat-list">
                
                <li>Opened: <span><?php echo $numOpened;?> (of <?php echo $trackerCnt;?>)</span></li>
                <li>Clicked Through: <span><?php echo $numClicked;?> (of <?php echo $trackerCnt;?>)</span></li>
                <li>Open Rate: <span><?php echo number_format($openRatio,2);?>%</span></li>
                <li>Click-to-Open Rate: <span><?php echo number_format($clickedRatio,2);?>%</span></li>
            </ul>
            
            <div class="clearfix"></div>
            
           
            <table class="table table-striped table-bordered table-hover" id="campaign-table" style="margin-top:20px;">
                <thead>
                    <tr>
                        <th>Recipient</th>
                        <th>Opened On</th>
                        <?php /*?><th>Clicked</th><?php */?>
                    </tr>
                </thead>
                <tbody>
                	<?php
					foreach($aList as $memberID=>$memberInfo){
						
						$friendFamily = $memberInfo['friendFamily'];
						
						if($friendFamily != NULL){
							$showName = $memberInfo['name_first'].' '.$memberInfo['name_last'].' ('.$memberInfo['email'].')';	
						}else{
							$showName = $memberInfo['name_first'].' '.$memberInfo['name_last'];	
						}
						
						if($memberInfo['opened'] != NULL){
							$dateOpen = date('m/d/Y h:i a', $memberInfo['opened']);	
						}else{
							$dateOpen = "Not Opened";	
						}
						
						if($memberInfo['clicked'] != NULL){
							$dateClick = date('m/d/Y h:i a', $memberInfo['clicked']);	
						}else{
							$dateClick = "No Links Clicked";	
						}
						
						?>
                        <tr>
                        	<td><?php echo $showName;?></td>
                            <td><?php echo $dateOpen;?></td>
                            <?php /*?><td><?php echo $dateClick;?></td><?php */?>
                        </tr>
                        <?php	
					}
					?>
                </tbody>
                
                </table>
            
        </div><!--profile-section-->
        <?php
		
	
	break;
	/*case 'view-campaign-report':
	
		$campID = $_REQUEST['campID'];
		
		$query = "
			SELECT * 
			FROM email_tracking
			WHERE camp_id=:camp_id AND bounced IS NULL
		";
		try{
			$rsTracking = $mLink->prepare($query);
			$aValues = array(
				':camp_id'	=> $campID
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsTracking->execute($aValues);
		}
		
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		
		$trackerCnt = 0;
		$numOpened	= 0;
		$numClicked	= 0;
			
		while($tracker = $rsTracking->fetch(PDO::FETCH_ASSOC)){
			
			$trackerCnt++;
			
			if($tracker['opened'] == '1'){
				$numOpened++;	
			}
			
			if($tracker['clicked'] == '1'){
				$numClicked++;	
			}
			
		}
		
		$openRatio 		= (($numOpened/$trackerCnt)*100);
		$clickedRatio	= (($numClicked/$trackerCnt)*100);
		
		?>
        <div class="profile-section">
            <h4>Email Activity</h4>
            
            <ul class="stat-list">
                
                <li>Opened: <span><?php echo $numOpened;?> (of <?php echo $trackerCnt;?>)</span></li>
                <li>Clicked Through: <span><?php echo $numClicked;?> (of <?php echo $trackerCnt;?>)</span></li>
                <li>Open Rate: <span><?php echo number_format($openRatio,2);?>%</span></li>
                <li>Click-to-Open Rate: <span><?php echo number_format($clickedRatio,2);?>%</span></li>
            </ul>
            
            <div class="clearfix"></div>
            
           
            
            
        </div><!--profile-section-->
        <?php
		
	
	break;*/
	
	//+-----------------------------------------------------------------------------------------------------------------------+
	//													Stop Tracking
	//+-----------------------------------------------------------------------------------------------------------------------+
	case 'stop-track-fund':
		
		
		
	break;

}
?>