<?php

// Disallow direct access to this file for security reasons
if(!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.");
}

$plugins->add_hook('member_do_register_end', 'nationverify_regredirect', 20);

function nationverify_info()
{
	return array(
		"name"			=> "NationStates Nation Verfication",
		"description"	=> "Utilize the NationStates API to verify that a user is the real owner of an associated nation.",
		"website"		=> "http://github.com/hierocles/mybb_nationverify",
		"author"		=> "Dylan/Glen-Rhodes",
		"authorsite"	=> "http://github.com/hierocles",
		"version"		=> "1.0",
		"compatibility" => "18*"
	);
}

function nationverify_install()
{
  global $db, $mybb;

  $setting_group = array(
      'name' => 'nationverify_settingsgroup',
      'title' => 'Nation Verification Settings',
      'description' => 'You must define a profile field and usergroup for verification purposes',
      'disporder' => 5, // The order your setting group will display
      'isdefault' => 0
  );

  $gid = $db->insert_query("settinggroups", $setting_group);

  $setting_array = array(
      'nationverify_profilefield' => array(
          'title' => 'Nation Profile Field',
          'description' => 'Enter the profile field ID used for providing nation:',
          'optionscode' => 'numeric',
          'value' => '1',
          'disporder' => 1
      ),
      'nationverify_verifiedgroup' => array(
          'title' => 'Verified Usergroup',
          'description' => 'Select the usergroup verified users are added to:',
          'optionscode' => "groupselectsingle",
          'value' => 1,
          'disporder' => 2
      )
  );

  foreach($setting_array as $name => $setting)
  {
      $setting['name'] = $name;
      $setting['gid'] = $gid;

      $db->insert_query('settings', $setting);
  }

  rebuild_settings();
}

function nationverify_is_installed()
{
  global $mybb;
  if(isset($mybb->settings['nationverify_profilefield']))
  {
      return true;
  }

  return false;
}

function nationverify_uninstall()
{
  global $db;

  $db->delete_query('settings', "name IN ('nationverify_profilefield','nationverify_verifiedgroup')");
  $db->delete_query('settinggroups', "name = 'nationverify_settingsgroup'");

  rebuild_settings();
}

function nationverify_regredirect()
{
		redirect('nationverify.php', 'You are being taken to the nation verification page...');
}
