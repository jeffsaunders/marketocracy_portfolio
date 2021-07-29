<?php

    /* 
    Prints the comments recursively. The only down side is that the array is
    potentially being traversed through each recursive call.
    */

    function printNestedComment($parent, $comments_array) {
      foreach ($comments_array as $comment) {
        if($parent == $comment['parent_id']) {
          $html .= '<ul>' . "\n";
          $html .= '<li>' . $comment['message'] . '</li>' . "\n" ;
          $html .= printNestedComment($comment['id'], $comments_array);
          $html .= '</ul>' . "\n";
        }
      }

      return $html;
    }

    $host = '10.0.0.20';
    $user = 'marketocracy';
    $password = 'KfabyZcbE3';

    $database = 'nova';

    $link = mysql_connect($host, $user, $password)
            or die ('Could not connect: ' . mysql_error());

    mysql_select_db($database) or die ('Could not select database');
	
    // You should make use of prepared statements as here:
    // http://www.databasejournal.com/features/mysql/article.php/3599166 . 
    // I am not using it because I do not have PHP 5 installed 

    // This is a convoluted example to show how to prevent SQL injections
    // Get the parameter passed in through the URL
    $query = sprintf("SELECT * FROM comments WHERE category='1'",
               mysql_real_escape_string($_REQUEST['category']));
	
    $raw_result = mysql_query($query);
	
    // Stick this in an array of associative arrays (hash)
    $comments_array = Array();
    while ($row = mysql_fetch_array($raw_result, MYSQL_ASSOC)) {
      $comments_array[] = array(
		  'id' => $row['id'],
		  'parent_id' => $row['parent_id'],
		  'message' => $row['message']);
    }
	
	print_r($comments_array);
	
	

    // Start with the comment that has no parent (NULL parent)
    echo printNestedComment(NULL, $comments_array);

    // Free the results

    mysql_free_result($raw_result);

    mysql_close($link);

?>