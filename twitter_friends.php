<?php
/*
Plugin Name: Twitter Friends
Plugin URI: http://www.realhomeincomes.com
Description: Displays a list of our twitter followers. Help and tutorials, go to <a href='http://realhomeincomes.com'>Realhomeincomes.com</a>
Version: 1.2
License: GPL
Author: Matt
Author URI: http://www.realhomeincomes.com
Contributors:
 ==============================================================================
*/

add_action('admin_menu', 'tf_admin_menu');
add_action('plugins_loaded', 'tf_widget_init');

function tf_admin_menu() 
{

   if (function_exists('add_management_page')) 
   {
        add_options_page('Twitter Followers', 'Twitter Followers', 8, basename(__FILE__), 'twitter_friends_subpanel');
   }
   
  

}



function twitter_friends_subpanel()
{
global $current_user, $wpdb, $table_prefix;
$user_id = $current_user->ID;

	
if(isset($_POST['submit']))
{
	echo "<div style='padding:0px 20px 0px 20px; color:#cc0000; font-size:1.2em;'>".authenticate_twitter_friends($_POST['t_username'], $_POST['t_pass'], 'http://twitter.com/account/verify_credentials.xml')."</div>";
}

?>

<div class='wrap'>
  <h2>Twitter Followers Setup</h2>
  <form action="" method="post" enctype="multipart/form-data">
    Twitter Username:<br />
    <input name="t_username" type="text" value="<?= get_option('twitter_friends_username') ?>" />
    <br />
    Twitter Password:<br />
    <input name="t_pass" type="password" value="<?= get_option('twitter_friends_password') ?>" />
    <br />
    <input name="submit" type="submit" value="Submit" />
  </form>
  <div style="width:350px;">
    <table width="1000px" border="0">
      <tr>
        <td width="350" valign="top"><h3>Below is your widget</h3>
          <?
	  if(get_option('twitter_friends_auth'))
	  {
		get_followers_from_twitter();
		$options = get_option('tf_widget_options');
		$width = htmlspecialchars($options['width'], ENT_QUOTES);
		$limit = htmlspecialchars($options['limit'], ENT_QUOTES);
		$pic_only = htmlspecialchars($options['pic'], ENT_QUOTES);
		$pic_only = ($pic_only==1)?true:false;
		display_followers($width, $limit, $pic_only);
	  }
	  ?>
        </td>
        <td valign="top"><h3>Developer Information</h3>
          <div style="padding:5px;border-bottom:2px solid #FFCC66; border-top:2px solid #FFCC66; background-color:#FFFFCC"> Please Follow Me ( <strong>Hit6</strong> ) on Twitter. All the widget updates will be tweeted<br />
            <a href='http://twitter.com/hit6'>Click Here to Follow Me</a></div>
          Hi, My name is Matt and I made this widget. If you are looking at this page you are probably pretty familiar with 
          Wordpress. You can authenticate your Twitter account on this page, then to add the Twitter Friends widget to your sidebar
          either go to Design and add the widget there or add the function  directly to your sidebar.<br />
          <br />
          The main function to display the widget:<br />
          <strong>function display_followers($width='100%', $limit=5, $pic_only=false)</strong><br />
          <br />
          To add the function directly, simple add this code to your html:<br />
          <strong>&lt? display_followers(); ?&gt;</strong> <br />
          This will display 5 followers with picture, username and most recent tweet.
          
          You can change the widget with the following variables:
          <div style="padding:5px;"><strong>$width = '250px'</strong> (can either be % like 100% or px for a fixed width. You must use % or px.).</div>
          <div style="padding:5px;"><strong>$limit = 10</strong> (number of followers to display).</div>
          <div style="padding:5px;"><strong>$pic_only = true</strong> (true or false. True displays only pictures, false displays picture, username, and most recent post)</div>
          <br />
          <br />
          <strong>Example:</strong><br />
          display a widget that is 250px wide, with 5 followers and display pictures only<br />
          <strong>&lt? display_followers('250px', 5, true); ?&gt;</strong> </td>
      </tr>
    </table>
  </div>
</div>
<?
}

function tf_widget_init() 
{

	if ( !function_exists('register_sidebar_widget') || !function_exists('register_widget_control') )
		return;
		
		register_sidebar_widget('Twitter Friends', 'tf_widget_display');
		register_widget_control('Twitter Friends', 'tf_widget_control');
		
		function tf_widget_control()
		{
		// Collect our widget's options.
				$options = get_option('tf_widget_options');
					if($_REQUEST['tf_submit'])
					{
					$newoptions['width'] = strip_tags(stripslashes($_POST['twitter-friends-width']));
					$newoptions['limit'] = strip_tags(stripslashes($_POST['twitter-friends-limit']));
					$newoptions['pic'] = strip_tags(stripslashes($_POST['pic']));
				
				if ( $options != $newoptions ) {
					$options = $newoptions;
					update_option('tf_widget_options', $options);
					}
				}
		
				// Format options as valid HTML. Hey, why not.
				$width = htmlspecialchars($options['width'], ENT_QUOTES);
				$limit = htmlspecialchars($options['limit'], ENT_QUOTES);
				$pic = htmlspecialchars($options['pic'], ENT_QUOTES);
				?>
<div>
  <div>Defaults to 100% wide and limit of 5 followers. You must include either % or px or width.</div>
  <label for="twitter-friends-width" style="line-height:35px;display:block;">Widget Width:
  <input size="10" type="text" id="twitter-friends-width" name="twitter-friends-width" value="<?php echo $width; ?>" />
  </label>
  <label for="twitter-friends-limit" style="line-height:35px;display:block;">Follower Limit:
  <input size="10" type="text" id="twitter-friends-limit" name="twitter-friends-limit" value="<?php echo $limit; ?>" />
  </label>
  <label for="twitter-friends-limit" style="line-height:35px;display:block;">Pictures Only: <? echo ($pic==1)? '<input name="pic" checked="checked" type="checkbox" value="1" />' :'<input name="pic" type="checkbox" value="1" />' ;?></label>
  <input type="hidden" id="tf_submit" name="tf_submit" value="1" />
</div>
<?
		}

		
}

function tf_widget_display($args='') 
{
	$options = get_option('tf_widget_options');
	$width = htmlspecialchars($options['width'], ENT_QUOTES);
    $limit = htmlspecialchars($options['limit'], ENT_QUOTES);
	$pic_only = htmlspecialchars($options['pic'], ENT_QUOTES);
	$pic_only = ($pic_only==1)?true:false;
	display_followers($width, $limit, $pic_only);
}

function hit_twitter($username,$password, $url){

    $host = $url;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $host);
    curl_setopt($ch, CURLOPT_VERBOSE, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
    curl_setopt($ch, CURLOPT_POST, 0);

    $result = curl_exec($ch);
	$result = simplexml_load_string($result);
	curl_close($ch);
    // Look at the returned header
    return $result;
}

function authenticate_twitter_friends($username,$password, $url)
{
 $resultArray = hit_twitter($username,$password, $url);

    if($resultArray[0] == "true"){
         $twitter_status='Authentication Success';
		 update_option('twitter_friends_username',$username);
	     update_option('twitter_friends_password',$password);
		 update_option('twitter_friends_auth', 1);
		 
		 get_me($username, $password);
		 
    } else {
         $twitter_status="Authentication Failed";
		 update_option('twitter_friends_auth', 0);
    }
	return $twitter_status;
}

function get_followers_from_twitter()
{
	$username = get_option('twitter_friends_username');
	$password = get_option('twitter_friends_password');
	$url =  "http://twitter.com/statuses/followers/".$username.".xml";
	$next_pull  = mktime(date("H"), date("i"), date("s"), date("m")+5, date("d"), date("Y"));
	
		$people = hit_twitter($username, $password, $url);
		if($people->error!='')
		{
			echo "<div style='color:#cc0000;'>Last Twitter touch returned an error: ".$people->error."<br><br>
			Your Twitter Friends widget will not be updated until the error is fixed.</div>";
		}
		else
		{
		//print_r($people);
			$i = 0;
			foreach($people as $p)
			{
				 $pa[$i]['name'] = (string)$p->screen_name;
				 $pa[$i]['image'] = (string)$p->profile_image_url;
				 $pa[$i]['private'] = (string)$s->protected;
				 foreach($p->status as $s)
				 {
					$pa[$i]['last_message'] = (string)$s->text;
					$pa[$i]['favorite'] = (string)$s->favorited;
					$pa[$i]['followers'] = (string)$s->followers_count;
				 }
				 $i++;
			}
	    @update_option('twitter_friends_cache', $pa);
		update_option('twitter_friends_next_pull', $next_pull);
		get_me($username, $password);
		}
	
	return $pa;
}

function get_followers()
{
	if(strtotime(date("Y-m-d H:i:s")) > get_option('twitter_friends_next_pull'))
	//if(true)
	{
		$username = get_option('twitter_friends_username');
		$password = get_option('twitter_friends_password');
		get_me($username, $password);
		$result = get_followers_from_twitter();
	}
	else
	{
		
		$result = get_option('twitter_friends_cache');
	}
	
	shuffle($result);
	return $result;
}

function get_me($username, $password)
{
	$url = "http://twitter.com/users/show/".$username.".xml";
	$me = hit_twitter($username,$password, $url);
	//print_r($me);
	$ma['name'] = (string)$me->screen_name;
	$ma['image'] = (string)$me->profile_image_url;
	$ma['followers'] = (string)$me->followers_count;
	$ma['description'] = (string)$me->description;
	$ma['following'] = (string)$me->friends_count;
    $ma['updates'] = (string)$me->statuses_count;
	
	@update_option('my_twitter_profile', $ma);
}

function hyperlink($text)
{
    $text = ereg_replace("[a-zA-Z]+://([.]?[a-zA-Z0-9_/-])*", "<a style='color:#0066cc' href=\"\\0\">\\0</a>", $text);
    $text = ereg_replace("(^| )(www([.]?[a-zA-Z0-9_/-])*)", "\\1<a style='color:#0066cc' href=\"http://\\2\">\\2</a>", $text);
	$text = preg_replace('#@([a-zA-Z0-9]+)#','<a href="http://twitter.com/\\1">\\0</a>',$text);
    return $text;
}

function display_followers($width='100%', $limit=5, $pic_only=false)
{
	$flag = 'wide';
	if(strpos($width, 'px'))
	{
		//if we are here then we are using px, not %
		$min = str_replace('px','', $width);
		if(!is_numeric($min))
		return "";
		else
		{
			if($min < '250')
			{
			$flag = 'narrow';
			}
		}
	}

	$me = get_option('my_twitter_profile');
	?>
<link href="http://<?= $_SERVER['HTTP_HOST']?>/wp-content/plugins/twitter_friends/style.css" rel="stylesheet" type="text/css" />
<div class='tf_main_box' style="width:<?= $width ?>;">
  <div class='tf_inner_box'>
    <table width="100%" border="0" class='tf_main_table'>
      <tr>
        <td colspan="4"><div class='tf_header_image'></div></td>
      </tr>
      <tr>
        <td colspan="2" class='tf_user_header'><a href='http://twitter.com/<?= $me['name'] ?>'>
          <?= $me['name'] ?>
          </a></td>
        <td colspan="2" class='tf_user_header' align="right"><a class='tf_button' href='http://twitter.com/<?= $me['name'] ?>'>Follow Me</a></td>
      </tr>
      <tr>
        <td colspan="4"><table class='tf_user_details'>
            <? 
	  if($flag == 'wide')
	  {
	  ?>
            <tr>
              <td><img align="left" style="padding:5px 5px 5px 5px;border:1px solid #CCCCCC; background-color:#FFFFFF" src="<?= $me['image']?>" /></td>
              <td style="border-right:1px solid #CCCCCC; margin-right:3px"><?= $me['following']?>
                <br />
                <span style="font-size:.8em">following</span></td>
              <td style="border-right:1px solid #CCCCCC; margin-right:3px"><?= $me['followers']?>
                <br />
                <span style="font-size:.8em">followers</span></td>
              <td><?= $me['updates']?>
                <br />
                <span style="font-size:.8em">updates</span></td>
            </tr>
            <?
	  }
	  else
	  {
	  ?>
            <tr>
              <td colspan="4"><table>
                  <tr>
                    <td><img align="left" style="padding:5px 5px 5px 5px;border:1px solid #CCCCCC; background-color:#FFFFFF" src="<?= $me['image']?>" /> </td>
                    <td style="margin-right:3px"><span style="font-size:.8em">following: </span>
                      <?= $me['following']?>
                      <br />
                      <span style="font-size:.8em">followers: </span>
                      <?= $me['followers']?>
                      <br />
                      <span style="font-size:.8em">updates: </span>
                      <?= $me['updates']?>
                      <br />
                    </td>
                  </tr>
                </table></td>
            </tr>
            <?
	  }
	  ?>
          </table></td>
          </tr>
          <tr>
        <td colspan="4" align="left"><?= $me['description']?>
        </td>
    </table>
  </div>
  <div class="tf_followers_header">My Wonderful Followers</div>
  <?
	echo ($pic_only) ? "<div style='text-align:center; margin:0 auto; width:95%;'>": "";
	$people = get_followers();
	$i = 0;
	foreach($people as $p)
	{
	 if($i < $limit)
	 {
	 $last_message = hyperlink($p['last_message']);
	 
	 if($pic_only)
	 {
	 	?>
 		 <div style="float:left; width:25%;"><a style='color:#0066cc' href='http://twitter.com/<?= $p['name'] ?>'><img style="background-color:#FFFFFF; border:1px solid #999999; padding:2px;" title="<?= $p['name'] ?>" alt="<?= $p['name'] ?>" src="<?= $p['image']?>" /></a> </div>
  		<?
	 }
	 else
	 {
		?>
  		<div class='tf_follower_box'> 
        <img align="left" style="padding:0px 5px 5px 0px;" src="<?= $p['image']?>" />
        <a style='color:#0066cc' href='http://twitter.com/<?= $p['name'] ?>'>
    	<?= $p['name'] ?>
    	</a>
        	<div>
        	<?= $last_message ?>
        	</div>
    	<div style="clear:left"></div>
  		</div>
  <?
	  }
		$i++;
	 }
	}
	echo ($pic_only) ? "</div> ": "";
	?>
  <div class='tf_link_love'>Get your Twitter plugin at <a href='http://realhomeincomes.com'>RealHomeIncomes.com</a></div>
</div>
<?
}
?>
