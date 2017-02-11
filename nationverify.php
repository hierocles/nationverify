<?php
define("IN_MYBB", 1);
define('THIS_SCRIPT', 'nationveryify.php');
require_once "./global.php";
global $db;

// Block guests and users who have already verified their nation
if(!$mybb->user['uid'] || $mybb->user['additionalgroups'] == $mybb->settings['nationverify_verifiedgroup'] || in_array($mybb->settings['nationverify_verifiedgroup'], explode(',', $mybb->user['additionalgroups'])))
{
	error("You either are not logged in or you have already attached a nation to your account. If you wish to change the nation associated with your accounnt, you must contact an administrator.");
}

// Add a breadcrumb
add_breadcrumb('Nation Verfication', THIS_SCRIPT);

// Get common variables
$nation = $mybb->user['fid'.$mybb->settings['nationverify_profilefield']];
$site_token = crypt($nation, $mybb->settings['bburl']);

// Verify process
if($mybb->get_input('action') == 'verify') {
  if($mybb->request_method == 'post') {
    $verify_code = trim($mybb->input['verificationcode']);

    $options = array('http' => array('user_agent' => 'Offsite forum [nationverify mybb plugin]: ' . $mybb->settings['bburl']));
    $context = stream_context_create($options);
    $url = 'https://www.nationstates.net/cgi-bin/api.cgi?a=verify&nation='.$nation.'&checksum='.$verify_code.'&token='.$site_token;
    $response = file_get_contents($url, false, $context) or exit('Could not grab API response');

    // $response: 1 = verified; 0 = unverified
    if($response == 1) {
      $build_query = "UPDATE " . TABLE_PREFIX . "users SET additionalgroups = IF(additionalgroups IS NULL OR additionalgroups = '', " . $mybb->settings['nationverify_verifiedgroup'] . ", CONCAT_WS(',', additionalgroups, " . $mybb->settings['nationverify_verifiedgroup'] . ")) WHERE uid = " . $mybb->user['uid'];
      $db->query($build_query);
      redirect(INDEX_URL, 'You have successfully activated your account! Redirecting to home...');
    }
    else {
      error("The verification process failed. Please make sure you are logged into the correct nation on NationStates and try again.");
    }
  }
	else {
		error_no_permission();
	}
}
// Display verification form
else {
  $page = <<<HTML
  <html>
  <head>
  <title>{$title}</title>
  {$headerinclude}
  </head>
  <body>
  {$header}
  <iframe id="nsframe" src="https://www.nationstates.net/page=verify_login?token={$site_token}" class="modal" style="height: 75%; width: 50%;"></iframe>

  <table border="0" cellspacing="0" cellpadding="5" class="tborder">
    <tr>
      <td class="thead"><span class="smalltext"><strong>Nation Verification</strong></span></td>
    </tr>
    <tr>
      <td class="trow1">
      <strong>Make sure that the nation you are logged into on NationStates is the same nation you registered with!</strong><br>
      <strong>Step 1.</strong> Click the "Get Verification Code" link below.<br>
      <strong>Step 2.</strong> Copy the verification code from the NationStates page that appears.<br>
      <strong>Step 3.</strong> Enter the code in to the box below and hit the "Verify" button.<br><br>

      <span style="text-align:center;"><h1><a href="#nsframe" rel="modal:open">Get Verification Code</a></h1></span>
      </td>
    </tr>
    <tr>
      <td class="trow2">
        <form action="nationverify.php?action=verify" method="post">
        <fieldset>
        <label for="verificationcode">Verification code</label>
        <input id="verificationcode" name="verificationcode" type="text" placeholder="" required="">
        <button id="verifybutton" name="verifybutton">Verify</button>
        </fieldset>
        </form>
      </td>
    </tr>
  </table>

  {$footer}
  </body>
  </html>
HTML;

  output_page($page);
}

?>
