<?php
if (!defined ("TYPO3_MODE")) 	die ("Access denied.");

t3lib_extMgm::allowTableOnStandardPages("tx_wecdevo_content");
t3lib_extMgm::addToInsertRecords("tx_wecdevo_content");

$TCA["tx_wecdevo_content"] = Array (
	"ctrl" => Array (
		"title" => "LLL:EXT:wec_devo/locallang_db.php:tx_wecdevo_content",
		"label" => "content",
		"tstamp" => "tstamp",
		"crdate" => "crdate",
		"cruser_id" => "cruser_id",
		"default_sortby" => "ORDER BY crdate DESC",
		"delete" => "deleted",
		"enablecolumns" => Array (
			"starttime" => "starttime",
			"endtime" => "endtime",
		),
		"dynamicConfigFile" => t3lib_extMgm::extPath($_EXTKEY)."tca.php",
		"iconfile" => t3lib_extMgm::extRelPath($_EXTKEY)."res/icons/icon_tx_wecdevo_content.gif",
	),
	"feInterface" => Array (
		"fe_admin_fieldList" => "starttime, endtime, type, section, content, intro, scripture_ref, scripture, questions, prayer, application, end_content, audio_file, video_file",
	)
);

t3lib_extMgm::allowTableOnStandardPages("tx_wecdevo_section");

$TCA["tx_wecdevo_section"] = Array (
	"ctrl" => Array (
		"title" => "LLL:EXT:wec_devo/locallang_db.php:tx_wecdevo_section",
		"label" => "name",
		"tstamp" => "tstamp",
		"crdate" => "crdate",
		"cruser_id" => "cruser_id",
		"default_sortby" => "ORDER BY position",
		"delete" => "deleted",
		"enablecolumns" => Array (
			"disabled" => "hidden",
		),		
		"dynamicConfigFile" => t3lib_extMgm::extPath($_EXTKEY)."tca.php",
		"iconfile" => t3lib_extMgm::extRelPath($_EXTKEY)."res/icons/icon_tx_wecdevo_section.gif",
	),
	"feInterface" => Array (
		"fe_admin_fieldList" => "name, position, start_at, time_period, template, has_comments, subscriber_only,data_uid,next_sequence,hidden",
	)
);

//$TCA["tx_wecdevo_journal"] = Array (
//	"ctrl" => Array (
//		"title" => "LLL:EXT:wec_devo/locallang_db.php:tx_wecdevo_journal",
//		"label" => "uid",
//		"tstamp" => "tstamp",
//		"crdate" => "crdate",
//		"cruser_id" => "cruser_id",
//		"default_sortby" => "ORDER BY crdate DESC",
//		"dynamicConfigFile" => t3lib_extMgm::extPath($_EXTKEY)."tca.php",
//		"iconfile" => t3lib_extMgm::extRelPath($_EXTKEY)."res/icons/icon_tx_wecdevo_journal.gif",
//	),
//	"feInterface" => Array (
//		"fe_admin_fieldList" => "owner_userid, entry, post_date",
//	)
//);

t3lib_extMgm::allowTableOnStandardPages("tx_wecdevo_users");
t3lib_extMgm::addToInsertRecords("tx_wecdevo_users");

$TCA["tx_wecdevo_users"] = Array (
	"ctrl" => Array (
		"title" => "LLL:EXT:wec_devo/locallang_db.php:tx_wecdevo_users",
		"label" => "email",
		"tstamp" => "tstamp",
		"crdate" => "crdate",
		"cruser_id" => "cruser_id",
		"default_sortby" => "ORDER BY uid",
		"dynamicConfigFile" => t3lib_extMgm::extPath($_EXTKEY)."tca.php",
		"iconfile" => t3lib_extMgm::extRelPath($_EXTKEY)."res/icons/icon_tx_wecdevo_users.gif",
	),
	"feInterface" => Array (
		"fe_admin_fieldList" => "user_uid, email, lastlogin_date, receive_by_email, how_paid, paidto_date,usergroup",
	)
);

t3lib_extMgm::allowTableOnStandardPages("tx_wecdevo_topics");

$TCA["tx_wecdevo_topics"] = Array (
	"ctrl" => Array (
		"title" => "LLL:EXT:wec_devo/locallang_db.php:tx_wecdevo_topics",
		"label" => "topic",
		"tstamp" => "tstamp",
		"crdate" => "crdate",
		"cruser_id" => "cruser_id",
		"default_sortby" => "ORDER BY num",
		"delete" => "deleted",		
		"dynamicConfigFile" => t3lib_extMgm::extPath($_EXTKEY)."tca.php",
		"iconfile" => t3lib_extMgm::extRelPath($_EXTKEY)."res/icons/icon_tx_wecdevo_topics.gif",
	),
	"feInterface" => Array (
		"fe_admin_fieldList" => "topic, show_date, num",
	)
);


t3lib_div::loadTCA("tt_content");
$TCA["tt_content"]["types"]["list"]["subtypes_excludelist"][$_EXTKEY."_pi1"]="layout,select_key,pages,recursive";

t3lib_extMgm::addPlugin(Array("LLL:EXT:wec_devo/locallang_db.php:tt_content.list_type_pi1", $_EXTKEY."_pi1"),"list_type");

$TCA["tt_content"]["types"]["list"]["subtypes_addlist"][$_EXTKEY."_pi1"]="pi_flexform";
t3lib_extMgm::addPiFlexFormValue($_EXTKEY."_pi1", "FILE:EXT:wec_devo/flexform_ds.xml");

t3lib_extMgm::addStaticFile($_EXTKEY,"static/ts/","WEC Devotional Journal Template");
t3lib_extMgm::addStaticFile($_EXTKEY, 'static/rss2/', 'WEC Devotional RSS 2.0 Feed' );

if (TYPO3_MODE=="BE")    $TBE_MODULES_EXT["xMOD_db_new_content_el"]["addElClasses"]["tx_wecdevo_pi1_wizicon"] = t3lib_extMgm::extPath($_EXTKEY).'pi1/class.tx_wecdevo_pi1_wizicon.php';

?>