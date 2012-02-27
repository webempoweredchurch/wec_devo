<?php
if (!defined ("TYPO3_MODE")) 	die ("Access denied.");

include_once(t3lib_extMgm::extPath('wec_devo').'pi1/class.tx_wecdevo_procfunc.php');

$TCA["tx_wecdevo_section"] = Array (
	"ctrl" => $TCA["tx_wecdevo_section"]["ctrl"],
	"interface" => Array (
		"showRecordFieldList" => "name,position,start_at,time_period,template,has_comments,subscriber_only,data_uid,next_sequence"
	),
	"feInterface" => $TCA["tx_wecdevo_section"]["feInterface"],
	"columns" => Array (
		"name" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:wec_devo/locallang_db.php:tx_wecdevo_section.name",
			"config" => Array (
				"type" => "input",
				"size" => "32",
				"max" => "64",
			)
		),
		"position" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:wec_devo/locallang_db.php:tx_wecdevo_section.position",
			"config" => Array (
				"type" => "input",
				"size" => "4",
				"max" => "4",
				"eval" => "int",
				"checkbox" => "0",
				"range" => Array (
					"upper" => "10000",
					"lower" => "1"
				),
				"default" => 0
			)
		),
		"start_at" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:wec_devo/locallang_db.php:tx_wecdevo_section.start_at",
			"config" => Array (
				"type" => "radio",
				"cols" => 3,
				"items" => Array (
					Array("LLL:EXT:wec_devo/locallang_db.php:tx_wecdevo_section.start_at.I.0", 1),
					Array("LLL:EXT:wec_devo/locallang_db.php:tx_wecdevo_section.start_at.I.1", 2),
					Array("LLL:EXT:wec_devo/locallang_db.php:tx_wecdevo_section.start_at.I.2", 3),
				),
				"default" => 1
			)
		),
		"time_period" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:wec_devo/locallang_db.php:tx_wecdevo_section.time_period",
			"config" => Array (
				"type" => "radio",
				"cols" => 5,
				"items" => Array (
					Array("LLL:EXT:wec_devo/locallang_db.php:tx_wecdevo_section.time_period.I.0", 1),
					Array("LLL:EXT:wec_devo/locallang_db.php:tx_wecdevo_section.time_period.I.1", 2),
					Array("LLL:EXT:wec_devo/locallang_db.php:tx_wecdevo_section.time_period.I.2", 3),
					Array("LLL:EXT:wec_devo/locallang_db.php:tx_wecdevo_section.time_period.I.3", 4),
					Array("LLL:EXT:wec_devo/locallang_db.php:tx_wecdevo_section.time_period.I.4", 5),
				),
				"default" => 1
			)
		),
		"template" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:wec_devo/locallang_db.php:tx_wecdevo_section.template",
			"config" => Array (
				"type" => "input",
				"size" => "30",
			)
		),
		"has_comments" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:wec_devo/locallang_db.php:tx_wecdevo_section.has_comments",
			"config" => Array (
				"type" => "check",
			)
		),
		"subscriber_only" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:wec_devo/locallang_db.php:tx_wecdevo_section.subscriber_only",
			"config" => Array (
				"type" => "check",
			)
		),
		"data_uid" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:wec_devo/locallang_db.php:tx_wecdevo_section.data_uid",
			"config" => Array (
				"type" => "input",
				"size" => "4",
				"max" => "4",
				"eval" => "int",
				"checkbox" => "0",
				"range" => Array (
					"upper" => "100000",
					"lower" => "1"
				),
				"default" => 0
			)
		),
		"next_sequence" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:wec_devo/locallang_db.php:tx_wecdevo_section.next_sequence",
			"config" => Array (
				"type" => "input",
				"size" => "4",
				"max" => "4",
				"eval" => "int",
				"checkbox" => "0",
				"range" => Array (
					"upper" => "10000",
					"lower" => "1"
				),
				"default" => 0
			)
		),
		'hidden' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.hidden',
			'config' => Array (
				'type' => 'check',
				'default' => '0'
			)
		),		
	),
	"types" => Array (
		"0" => Array("showitem" => "name;;;;1-1-1, position, start_at, time_period, template, has_comments, subscriber_only, data_uid,next_sequence, hidden")
	),
	"palettes" => Array (
		"1" => Array("showitem" => "name")
	)
);


$TCA["tx_wecdevo_content"] = Array (
	"ctrl" => $TCA["tx_wecdevo_content"]["ctrl"],
	"interface" => Array (
		"showRecordFieldList" => "starttime,endtime,content,type,section,audio_file,video_file"
	),
	"feInterface" => $TCA["tx_wecdevo_content"]["feInterface"],
	"columns" => Array (
		"starttime" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:lang/locallang_general.php:LGL.starttime",
			"config" => Array (
				"type" => "input",
				"size" => "8",
				"max" => "20",
				"eval" => "date",
				"default" => "0",
				"checkbox" => "0"
			)
		),
		"endtime" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:lang/locallang_general.php:LGL.endtime",
			"config" => Array (
				"type" => "input",
				"size" => "8",
				"max" => "20",
				"eval" => "date",
				"checkbox" => "0",
				"default" => "0",
				"range" => Array (
					"upper" => mktime(0,0,0,12,31,2020),
					"lower" => mktime(0,0,0,1,1,2000),
				)
			)
		),
		"type" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:wec_devo/locallang_db.php:tx_wecdevo_content.type",
			"config" => Array (
				"type" => "select",
				"items" => Array (
					Array("LLL:EXT:wec_devo/locallang_db.php:tx_wecdevo_content.type.I.0", "0"),
					Array("LLL:EXT:wec_devo/locallang_db.php:tx_wecdevo_content.type.I.1", "1"),
					Array("LLL:EXT:wec_devo/locallang_db.php:tx_wecdevo_content.type.I.2", "2"),
					Array("LLL:EXT:wec_devo/locallang_db.php:tx_wecdevo_content.type.I.5", "5"),
				),
				"size" => 1,
				"maxitems" => 1,
				"default" => 0,
			)
		),
		"section" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:wec_devo/locallang_db.php:tx_wecdevo_content.section",
			"config" => Array (
				"type" => "select",
				"foreign_table" => "tx_wecdevo_section",
				"foreign_table_where" => "AND (tx_wecdevo_section.pid=###STORAGE_PID### OR tx_wecdevo_section.pid=###CURRENT_PID###) ORDER BY tx_wecdevo_section.position",
				"size" => 8,
				"minitems" => 1,
				"maxitems" => 1,
				'items' => Array (
					Array('LLL:EXT:wec_devo/locallang_db.php:tx_wecdevo_content.section_all', 0), 
				),
				'wizards' => array( 
			    	'uproc' => array( 
			          'type' => 'userFunc', 
			          'userFunc' => 'tx_wecdevo_procfunc->setSection', 
			          'params' => array( 
			          	'start' => 1 
			        	), 
			   		),
				),
			)
		),
		"content" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:wec_devo/locallang_db.php:tx_wecdevo_content.content",
			"config" => Array (
				"type" => "text",
				"cols" => "30",
				"rows" => "5",
				"wizards" => Array(
					"_PADDING" => 2,
					"RTE" => Array(
						"notNewRecords" => 1,
						"RTEonly" => 1,
						"type" => "script",
						"title" => "Full screen Rich Text Editing|Formatteret redigering i hele vinduet",
						"icon" => "wizard_rte2.gif",
						"script" => "wizard_rte.php",
					),
				),
			)
		),
		"scripture_ref" => Array (
			"exclude" => 0,
			"label" => "LLL:EXT:wec_devo/locallang_db.php:tx_wecdevo_content.scripture_ref",
			"config" => Array (
				"type" => "input",
				"size" => "32",
				"max" => "64",
			)
		),
		"scripture" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:wec_devo/locallang_db.php:tx_wecdevo_content.scripture",
			"config" => Array (
				"type" => "text",
				"cols" => "30",
				"rows" => "3",
				"wizards" => Array(
					"_PADDING" => 2,
					"RTE" => Array(
						"notNewRecords" => 1,
						"RTEonly" => 1,
						"type" => "script",
						"title" => "Full screen Rich Text Editing|Formatteret redigering i hele vinduet",
						"icon" => "wizard_rte2.gif",
						"script" => "wizard_rte.php",
					),
				),
			)
		),
		"questions" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:wec_devo/locallang_db.php:tx_wecdevo_content.questions",
			"config" => Array (
				"type" => "text",
				"cols" => "30",
				"rows" => "3",
				"wizards" => Array(
					"_PADDING" => 2,
					"RTE" => Array(
						"notNewRecords" => 1,
						"RTEonly" => 1,
						"type" => "script",
						"title" => "Full screen Rich Text Editing|Formatteret redigering i hele vinduet",
						"icon" => "wizard_rte2.gif",
						"script" => "wizard_rte.php",
					),
				),
			)
		),
		"prayer" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:wec_devo/locallang_db.php:tx_wecdevo_content.prayer",
			"config" => Array (
				"type" => "text",
				"cols" => "30",
				"rows" => "3",
				"wizards" => Array(
					"_PADDING" => 2,
					"RTE" => Array(
						"notNewRecords" => 1,
						"RTEonly" => 1,
						"type" => "script",
						"title" => "Full screen Rich Text Editing|Formatteret redigering i hele vinduet",
						"icon" => "wizard_rte2.gif",
						"script" => "wizard_rte.php",
					),
				),
			)
		),
		"application" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:wec_devo/locallang_db.php:tx_wecdevo_content.application",
			"config" => Array (
				"type" => "text",
				"cols" => "30",
				"rows" => "3",
				"wizards" => Array(
					"_PADDING" => 2,
					"RTE" => Array(
						"notNewRecords" => 1,
						"RTEonly" => 1,
						"type" => "script",
						"title" => "Full screen Rich Text Editing|Formatteret redigering i hele vinduet",
						"icon" => "wizard_rte2.gif",
						"script" => "wizard_rte.php",
					),
				),
			)
		),
		"end_content" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:wec_devo/locallang_db.php:tx_wecdevo_content.end_content",
			"config" => Array (
				"type" => "text",
				"cols" => "30",
				"rows" => "3",
				"wizards" => Array(
					"_PADDING" => 2,
					"RTE" => Array(
						"notNewRecords" => 1,
						"RTEonly" => 1,
						"type" => "script",
						"title" => "Full screen Rich Text Editing|Formatteret redigering i hele vinduet",
						"icon" => "wizard_rte2.gif",
						"script" => "wizard_rte.php",
					),
				),
			)
		),
		"audio_file" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:wec_devo/locallang_db.php:tx_wecdevo_content.audio_file",
			"config" => Array (
				"type" => "input",
				"size" => "32",
				"max" => "64",
			)
		),
		"video_file" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:wec_devo/locallang_db.php:tx_wecdevo_content.video_file",
			"config" => Array (
				"type" => "input",
				"size" => "32",
				"max" => "64",
			)
		),
	),
	"types" => Array (
		"0" => Array("showitem" => "starttime;;;;1-1-1, endtime, type, section, content;;;richtext[paste|bold|italic|underline|formatblock|class|left|center|right|orderedlist|unorderedlist|link|image]:rte_transform[mode=ts], scripture_ref,scripture;;;richtext[paste|bold|italic|underline|formatblock|class|left|center|right|orderedlist|unorderedlist|link|image]:rte_transform[mode=ts], questions;;;richtext[paste|bold|italic|underline|formatblock|class|left|center|right|orderedlist|unorderedlist|link|image]:rte_transform[mode=ts], prayer;;;richtext[paste|bold|italic|underline|formatblock|class|left|center|right|orderedlist|unorderedlist|link|image]:rte_transform[mode=ts], application;;;richtext[paste|bold|italic|underline|formatblock|class|left|center|right|orderedlist|unorderedlist|link|image]:rte_transform[mode=ts], end_content;;;richtext[paste|bold|italic|underline|formatblock|class|left|center|right|orderedlist|unorderedlist|link|image]:rte_transform[mode=ts], audio_file, video_file")
	),
	"palettes" => Array (
		"1" => Array("showitem" => "")
	)
);


//$TCA["tx_wecdevo_journal"] = Array (
//	"ctrl" => $TCA["tx_wecdevo_journal"]["ctrl"],
//	"interface" => Array (
//		"showRecordFieldList" => "owner_userid,entry,post_date",
//		"maxDBListItems" => -1,
//	),
//	"feInterface" => $TCA["tx_wecdevo_journal"]["feInterface"],
//	"columns" => Array (
//		"owner_userid" => Array (
//			"exclude" => 0,
//			"label" => "LLL:EXT:wec_devo/locallang_db.php:tx_wecdevo_journal.owner_userid",
//			"config" => Array (
//				"type" => "input",
//				"size" => "4",
//				"max" => "4",
//				"eval" => "int",
//				"checkbox" => "0",
//				"range" => Array (
//					"upper" => "100000",
//					"lower" => "1"
//				),
//				"default" => 0
//			)
//		),
//		"entry" => Array (
//			"exclude" => 0,
//			"label" => "LLL:EXT:wec_devo/locallang_db.php:tx_wecdevo_journal.entry",
//			"config" => Array (
//				"type" => "text",
//				"cols" => "30",
//				"rows" => "5",
//				"wizards" => Array(
//					"_PADDING" => 2,
//					"RTE" => Array(
//						"notNewRecords" => 1,
//						"RTEonly" => 1,
//						"type" => "script",
//						"title" => "Full screen Rich Text Editing|Formatteret redigering i hele vinduet",
//						"icon" => "wizard_rte2.gif",
//						"script" => "wizard_rte.php",
//					),
//				),
//			)
//		),
//		"post_date" => Array (
//			"exclude" => 0,
//			"label" => "LLL:EXT:wec_devo/locallang_db.php:tx_wecdevo_journal.post_date",
//			"config" => Array (
//				"type" => "input",
//				"size" => "8",
//				"max" => "20",
//				"checkbox" => "0",
//				"default" => "0"
//			)
//		),
//	),
//	"types" => Array (
//		"0" => Array("showitem" => "owner_userid;;;;1-1-1, entry, post_date")
//	),
//	"palettes" => Array (
//		"1" => Array("showitem" => "")
//	)
//);



$TCA["tx_wecdevo_users"] = Array (
	"ctrl" => $TCA["tx_wecdevo_users"]["ctrl"],
	"interface" => Array (
		"showRecordFieldList" => "user_uid,email,lastlogin_date,receive_by_email,how_paid,paidto_date,usergroup,misc"
	),
	"feInterface" => $TCA["tx_wecdevo_users"]["feInterface"],
	"columns" => Array (
		"user_uid" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:wec_devo/locallang_db.php:tx_wecdevo_users.user_uid",
			"config" => Array (
				"type" => "input",
				"size" => "4",
				"max" => "4",
				"eval" => "int",
				"checkbox" => "0",
				"range" => Array (
					"upper" => "100000",
					"lower" => "1"
				),
				"default" => 0
			)
		),
		"email" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:wec_devo/locallang_db.php:tx_wecdevo_users.email",
			"config" => Array (
				"type" => "input",
				"size" => "32",
				"max" => "64",
			)
		),
		"lastlogin_date" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:wec_devo/locallang_db.php:tx_wecdevo_users.lastlogin_date",
			"config" => Array (
				"type" => "input",
				"size" => "12",
				"max" => "20",
				"eval" => "datetime",
				"checkbox" => "0",
				"default" => "0"
			)
		),
		"receive_by_email" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:wec_devo/locallang_db.php:tx_wecdevo_users.receive_by_email",
			"config" => Array (
				"type" => "check",
			)
		),
		"how_paid" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:wec_devo/locallang_db.php:tx_wecdevo_users.how_paid",
			"config" => Array (
				"type" => "input",
				"size" => "16",
				"max" => "32",
			)
		),
		"paidto_date" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:wec_devo/locallang_db.php:tx_wecdevo_users.paidto_date",
			"config" => Array (
				"type" => "input",
				"size" => "8",
				"max" => "20",
				"eval" => "date",
				"checkbox" => "0",
				"default" => "0"
			)
		),
		"usergroup" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:wec_devo/locallang_db.php:tx_wecdevo_users.usergroup",
			"config" => Array (
				"type" => "input",
				"size" => "4",
				"max" => "4",
				"eval" => "int",
				"checkbox" => "0",
				"range" => Array (
					"upper" => "100000",
					"lower" => "1"
				),
				"default" => 0
			)
		),
		"misc" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:wec_devo/locallang_db.php:tx_wecdevo_users.misc",
			"config" => Array (
				"type" => "input",
				"size" => "10",
				"max" => "20",
				"default" => "0"
			)
		),
	),
	"types" => Array (
		"0" => Array("showitem" => "user_uid;;;;1-1-1, email, lastlogin_date, receive_by_email, how_paid, paidto_date, usergroup, misc")
	),
	"palettes" => Array (
		"1" => Array("showitem" => "")
	)
);



$TCA["tx_wecdevo_topics"] = Array (
	"ctrl" => $TCA["tx_wecdevo_topics"]["ctrl"],
	"interface" => Array (
		"showRecordFieldList" => "topic,show_date,num"
	),
	"feInterface" => $TCA["tx_wecdevo_topics"]["feInterface"],
	"columns" => Array (
		"topic" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:wec_devo/locallang_db.php:tx_wecdevo_topics.topic",
			"config" => Array (
				"type" => "input",
				"size" => "30",
			)
		),
		"show_date" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:wec_devo/locallang_db.php:tx_wecdevo_topics.show_date",
			"config" => Array (
				"type" => "input",
				"size" => "8",
				"max" => "20",
				"eval" => "date",
				"checkbox" => "0",
				"default" => "0"
			)
		),
		"num" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:wec_devo/locallang_db.php:tx_wecdevo_topics.num",
			"config" => Array (
				"type" => "input",
				"size" => "4",
				"max" => "4",
				"eval" => "int",
				"checkbox" => "0",
				"range" => Array (
					"upper" => "10000",
					"lower" => "1"
				),
				"default" => 0
			)
		),
	),
	"types" => Array (
		"0" => Array("showitem" => "topic;;;;1-1-1, show_date, num")
	),
	"palettes" => Array (
		"1" => Array("showitem" => "")
	)
);
?>