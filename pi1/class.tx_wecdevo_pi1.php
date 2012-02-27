<?php
/***********************************************************************
* Copyright notice
*
* (c) 2005-2011 Christian Technology Ministries International Inc.
* All rights reserved
*
* This file is part of the Web-Empowered Church (WEC)
* (http://WebEmpoweredChurch.org) ministry of Christian Technology Ministries
* International (http://CTMIinc.org). The WEC is developing TYPO3-based
* (http://typo3.org) free software for churches around the world. Our desire
* is to use the Internet to help offer new life through Jesus Christ. Please
* see http://WebEmpoweredChurch.org/Jesus.
*
* You can redistribute this file and/or modify it under the terms of the
* GNU General Public License as published by the Free Software Foundation;
* either version 2 of the License, or (at your option) any later version.
*
* The GNU General Public License can be found at
* http://www.gnu.org/copyleft/gpl.html.
*
* This file is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* This copyright notice MUST APPEAR in all copies of the file!
*************************************************************************/
require_once(PATH_tslib."class.tslib_pibase.php");

// for textpaging...
define("SHOW_FULL",		'1');
define("SHOW_BY_PAGES", '2');
define("SHOW_SCROLLBOX",'3');

// for textpaging...
define('SHOW_FULL', 	'1');
define('SHOW_BY_PAGES', '2');
define('SHOW_SCROLLBOX','3');

/**
* Plugin 'WEC Devotional Journal' for the 'wec_devo' extension.
*
* @author Web-Empowered Church Team <devteam(at)webempoweredchurch.org>
*
* "For the word of God is living and active. Sharper than any double-edged
*  sword, it penetrates even to dividing soul and spirit, joints and marrow;
*  it judges the thoughts and attitudes of the heart." (Hebrews 4:12)
*
* DESCRIPTION:
* The Web-Empowered Church Devotional Journal ('WEC Devo') extension
* implements a devotional with a private personal journal for each user.
* A devotional is writings/articles or Bible passages helping people to
* devote themselves to and focus on God and spiritual issues. While this
* extension is designed for churches and ministries, and devotionals in
* particular, the extension can support any content.
*
* This extension has 3 main parts:
* 1) Devotional: A key feature of the Devotional Journal is the ability
*  to present scheduled content with tabbed categories. Content entered
*  into the Devotional Journal is given a start and an end date that defines
*  when it is schedule to appear on the Devotional Journal page. That duration
*  could be any number of days but it is typically a day, a week, or a month.
*  Content for the future can be entered any time and will appear when it is
*  scheduled to appear. The Devotional Journal also supports categories of
*  content that are accessed by the user via different tabs. The content under
*  different tabs can have different start and end dates.
*
* 2) Journal: The journal feature enables a user to type in notes while reading
*  and pondering the current devotional. The journal entries are synchronized
*  to the time of the scheduled content so the journal entries stay with the
*  content. A user can view previous content and journal entries by navigating
*  to a different day. Since journal entries may be personal, they are only
*  accessible to the logged in user who entered them, and for added privacy,
*  the data is stored in an encrypted format. The journal uses the rich text
*  editor if the browser supports it.
*
* 3) Optional Group Discussion: An optional online group discussion, which is
*  also synchronized with the scheduled content, can be added using the WEC
*  Discussion extension. This is a separate extension that can work with the
*  WEC Devotional Journal.
*
*/

class tx_wecdevo_pi1 extends tslib_pibase {
	var $prefixId 			= "tx_wecdevo_pi1";		// Same as class name
	var $scriptRelPath 		= "pi1/class.tx_wecdevo_pi1.php";	// Path to this script relative to the extension dir.
	var $extKey 			= "wec_devo";	// The extension key.
	var $devoContentTable	= "tx_wecdevo_content";
	var $devoUserTable  	= "tx_wecdevo_users";
	var $devoSectionTable 	= "tx_wecdevo_section";
	var $devoTopicsTable	= "tx_wecdevo_topics";
	var $devoJournalTable	= "tx_wecdevo_journal";

	var $pid_list;		// Page ID for data
	var $cObj; 			// The backReference to the mother cObj object set at call time
	var $curDay;		// current date in MMDDYY format
	var $curDayTS;		// current date TIMESTAMP
	var $curSec;		// current section selected
	var $curPage;		// current page (if paged  content)
	var $curTextPaging;	// current text paging (FULL / PAGED  / SCROLLBOX)
	var $curTextSize;	// current text size (SMALL / MEDIUM / BIG)
	var $forceTextPaging;// if force to certain text paging
	var $forceTextSize;	// if force to certain text size
	var $dateOffset;	// date offset

	var $userID;		// current UID of user logged in (0 = no user logged in)
	var $userName;		// user account name for logged in user
	var $userFirstName; // first name for logged in user
	var $lastLoginDate;	// when last logged in

	var $templateName; 	// template name based on section in

	var $isAdministrator;// if is an administrator

	var $first_datetime;// first devo datetime (to restrict so cannot go past)
	var $last_datetime; // last devo datetime (to restrict calendar so cannot go past)

	var $sectionDataPID;// location for section data (if different than usual)
	var $nextInSequence;// next section in sequence (0 = none)

	var $curTopic;
	var $curContentTitle;// current title from main content (usually H4)
	var $curVerses;		// current verses for scripture content (usually H5)

	var $forumVars;		// keep track of forum GET/POST vars
	
	var $headerData;	// include data for CSS / JS in header

	// for rtehtmlarea in Front-end
	var $RTEObj;
	var $docLarge = 0;
	var $RTEcounter = 0;
	var $formName;
	var $additionalJS_initial = '';// Initial JavaScript to be printed before the form (should be in head, but cannot due to IE6 timing bug)
	var $additionalJS_pre = array();// Additional JavaScript to be printed before the form (works in Mozilla/Firefox when included in head, but not in IE6)
	var $additionalJS_post = array();// Additional JavaScript to be printed after the form
	var $additionalJS_submit = array();// Additional JavaScript to be executed on submit
	var $PA = array(
		'itemFormElName' =>  '',
		'itemFormElValue' => '',
	);
	var $specConf = array(
		'rte_transform' => array(
		'parameters' => array('mode' => 'ts_css')
		)
	);
	var $thisConfig = array();
	var $RTEtypeVal = 'text';
	
	var $isRSSFeed = false;
	
	/**
	* Init: Initialize the extension. read in Flexform/Typoscript/getvars.
	*
	* @param array  $conf  the TypoScript configuration
	* @return void  No return value needed.
	*/
	function init($conf) {

		// ------------------------------------------------------------
		// Initialize vars, structures, arrays, etc.
		// ------------------------------------------------------------
		if (!$this->cObj) $this->cObj = t3lib_div::makeInstance('tslib_cObj');
		$this->conf = $conf;				// TypoScript configuration
		$this->pi_setPiVarDefaults();		// GetPut-parameter configuration
		$this->pi_initPIflexForm();			// Initialize the FlexForms array
		$this->pi_loadLL();					// localized language variables
		$GLOBALS['TSFE']->set_no_cache();   // CONTENT CHANGES DAILY/WEEKLY...PLUS IS CUSTOMIZED...SO DON'T CACHE

		$this->templateName = 0;
		$this->curTextPaging = SHOW_FULL;
		$this->curTextSize	 = 2;
		$this->isAdministrator = 0;

		// setup RTE if enabled
		if(($this->conf['RTEenabled'] == 'rtehtmlarea') && t3lib_extMgm::isLoaded('rtehtmlarea')) {
			require_once(t3lib_extMgm::extPath('rtehtmlarea').'pi2/class.tx_rtehtmlarea_pi2.php');
			$this->RTEObj = t3lib_div::makeInstance('tx_rtehtmlarea_pi2');
		} elseif (($this->conf['RTEenabled'] == 'tinymce') && t3lib_extMgm::isLoaded('tinymce_rte')) {
			require_once(t3lib_extMgm::extPath('tinymce_rte').'pi1/class.tx_tinymce_rte_pi1.php');
			$this->RTEObj = t3lib_div::makeInstance('tx_tinymce_rte_pi1');
			// add header data for BG_HTMLAREA RTE plugin
		} elseif (($this->conf['RTEenabled'] == 'bg_htmlarea') && t3lib_extMgm::isLoaded('bg_htmlarea')) {
				$bghtmlareaPath = t3lib_extMgm::siteRelPath('bg_htmlarea');
		    	$this->headerData = '
				  <style type="text/css">@import url("'.$bghtmlareaPath.'res/htmlarea.css")</style>
				  <script type="text/javascript" src="'.$bghtmlareaPath.'res/htmlarea.js"></script>
				  <script type="text/javascript" src="'.$bghtmlareaPath.'res/lang/en.js"></script>
				  <script type="text/javascript" src="'.$bghtmlareaPath.'res/dialog.js"></script>
				';
				
		} else {
			$this->RTEObj = 0;
		}	
		// Set the storage PID (currently supports one page...could add recursive or multiple pages later)
		//-------------------------------------------------------------
		$this->config['storagePID'] 		= $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'storagePID', 'sDEF');
		if ($this->config['storagePID']) 	// can specify in flexform
			$this->pid_list = $this->config['storagePID'];
		else if ($this->conf['pid_list'])	// or specify in TypoScript
			$this->pid_list = $this->conf['pid_list'];
		else
			$this->pid_list = $GLOBALS['TSFE']->id; 	// the default is the current page

		// set the current page id
		$this->id = $GLOBALS['TSFE']->id;
		
		// ----------------------------------------------------------------------------------------
		//	Set USER Info
		// ----------------------------------------------------------------------------------------
		if ($GLOBALS['TSFE']->loginUser) {
			$this->userID = $GLOBALS['TSFE']->fe_user->user['uid'];
			$this->userName = $GLOBALS['TSFE']->fe_user->user['username'];
			$this->userFirstName = $GLOBALS['TSFE']->fe_user->user['first_name'];
			if (strlen($this->userFirstName) < 1) $this->userFirstName = $this->userName;
			$lastName = $GLOBALS['TSFE']->fe_user->user['last_name'];
			// set login date based on TYPO3 user information. Update lastLoginDate by cookies later, if possible.
			$this->lastLoginDate = $GLOBALS['TSFE']->fe_user->user['lastlogin'];
		}
		else { // no user logged in...so clear out vars
			$this->userID = 0;
			$this->userName = '';
			$this->userFirstname = '';
		}

		// try to load user info from cookie
		if ($cookieData = $this->getWECcookie($GLOBALS['TSFE']->id)) {
			$this->lastLoginDate = $cookieData[1];
			$this->curTextPaging = $cookieData[2];
			$this->curTextSize = $cookieData[3];
		}

		// ----------------------------------------------------------------------------------------
		// Handle Passed-In values
		// passed in: &show_date (date in MMDDYY format) and &section (section ID)
		// ----------------------------------------------------------------------------------------
		$this->postvars = t3lib_div::_GP('tx_wecdevo');

		// read, validate, and then setup curDay and curDayTS
		$devo_dateTime = trim($this->postvars['show_date']);
		if (($devo_dateTime <= 0) || (strlen($devo_dateTime) != 6) || !is_numeric($devo_dateTime)) {
			$this->curDayTS = mktime(12, 0, 0, date('m'), date('d'), date('y')); // return today() if not set
			$this->curDay = date('mdy');
		} else {
			$this->curDay = $devo_dateTime;
			$this->curDayTS = mktime(12, 0, 0, substr($devo_dateTime, 0, 2), substr($devo_dateTime, 2, 2), substr($devo_dateTime, 4, 2));
		}

		// read, validate, and then setup current section
		$devo_section = $this->postvars['section'];
		if (!isset($devo_section) || !is_numeric($devo_section) || $devo_section < 0)
			$devo_section = 0;
		$this->curSec = $devo_section;

		// ------------------------------------------------------------
		// Load in all flexform values
		// ------------------------------------------------------------
		$this->config['preview_pid'] = $this->getConfigVal($this, 'previewPID', 'sDEF');
		$this->config['comments_pid'] = $this->getConfigVal($this, 'commentsPID', 'sDEF');

		$this->config['allowPaging'] = $this->getConfigVal($this, 'allowPaging', 's_display');
		$this->config['allowTextSize'] = $this->getConfigVal($this, 'allowTextSize', 's_display');
		$this->config['useNextArrow'] = $this->getConfigVal($this, 'useNextArrow', 's_display');

		$this->config['hasJournal'] = $this->getConfigVal($this, 'hasJournal', 's_journal');
		$this->config['allowPrintJournal'] = $this->getConfigVal($this, 'allowPrintJournal', 's_journal');
		$this->config['journal_extrabuttons'] = $this->getConfigVal($this, 'journal_extrabuttons', 's_journal');
		$this->config['rte_code'] = $this->getConfigVal($this, 'rte_code', 's_journal');

		$this->config['hasAudio'] = $this->getConfigVal($this, 'hasAudio', 's_audiovideo');
		$this->config['hasVideo'] = $this->getConfigVal($this, 'hasVideo', 's_audiovideo');
		$this->config['audiofile_autoformat'] = $this->getConfigVal($this, 'audiofile_autoformat', 's_audiovideo');
		$this->config['audioPlayer_code'] = $this->getConfigVal($this, 'audioplayer_code', 's_audiovideo');
		$this->config['videofile_autoformat'] = $this->getConfigVal($this, 'videofile_autoformat', 's_audiovideo');
		$this->config['videoPlayer_code'] = $this->getConfigVal($this, 'videoplayer_code', 's_audiovideo');

		$this->config['administrator_list'] = $this->getConfigVal($this, 'administrator_list', 's_admin');

		$this->config['date_offset_orig'] = $this->getConfigVal($this, 'date_offset_orig', 's_dates');
		$this->config['date_offset_cur'] = $this->getConfigVal($this, 'date_offset_cur', 's_dates');
		$this->config['devo_firstdate'] = $this->getConfigVal($this, 'devo_firstdate', 's_dates');
		$this->config['devo_lastdate'] = $this->getConfigVal($this, 'devo_lastdate', 's_dates');
		$this->config['date_format'] = $this->getConfigVal($this, 'date_format', 's_dates');

		if ($this->config['devo_firstdate'])
			$this->first_datetime = mktime(12, 0, 0, substr($this->config['devo_firstdate'], 0, 2), substr($this->config['devo_firstdate'], 3, 2), substr($this->config['devo_firstdate'], 6, 4));
		if ($this->config['devo_lastdate'])
			$this->last_datetime = mktime(12, 0, 0, substr($this->config['devo_lastdate'], 0, 2), substr($this->config['devo_lastdate'], 3, 2), substr($this->config['devo_lastdate'], 6, 4));

		// force paging / font size
		$this->forceTextPaging = $this->conf['forceTextPaging'];
		$this->forceTextSize = $this->conf['forceTextSize'];
		if ($this->forceTextPaging && $this->forceTextSize) { // if both forced to values, turn of allowing icons
			$this->config['allowPaging'] = 0;
			$this->config['allowTextSize'] = 0;
		}
		if ($this->forceTextPaging)
			$this->curTextPaging = $this->forceTextPaging;
		if ($this->forceTextSize)
			$this->curTextSize = $this->forceTextSize;
			
		// SET if administrator
		//---------------------------------------------------
		if ($this->userID && ($admins = $this->config['administrator_list'])) {
			$adminList = t3lib_div::trimExplode(',', $admins);
			foreach ($adminList as $thisAdmin) {
				if (($thisAdmin == $this->userID) || ($thisAdmin == $this->userName)) {
					$this->isAdministrator = 1;
					break;
				}
			}
		}

		$this->sectionDataPID = $this->conf['sectionDataPID'] ? $this->conf['sectionDataPID'] : $this->pid_list;

		// figure out date offset
		//
		if ($this->config['date_offset_orig'] && $this->config['date_offset_cur']) {
			// parse format MM-DD-YYYY
			$origDate = mktime(12, 0, 0, substr($this->config['date_offset_orig'], 0, 2), substr($this->config['date_offset_orig'], 3, 2), substr($this->config['date_offset_orig'], 6, 4));
			$curDate  = mktime(12, 0, 0, substr($this->config['date_offset_cur'], 0, 2), substr($this->config['date_offset_cur'], 3, 2), substr($this->config['date_offset_cur'], 6, 4));
			$this->dateOffset = $curDate - $origDate;

	  		// need to setup a devoDay and a curDay
	  		//	devoDay = the day for the devo (used only in displayDevo)
	  		//  curDay = when it actually is
		}

		if ($cssFile = $this->conf['cssFile']) {
			$fileList = array(t3lib_div::getFileAbsFileName($cssFile));
			$fileList = t3lib_div::removePrefixPathFromList($fileList,PATH_site);
			$GLOBALS['TSFE']->additionalHeaderData['wecdevo_css'] = '<link type="text/css" rel="stylesheet" href="'.$fileList[0].'" />';
		}

		// RSS FEED?
		//----------------------------------------------------------
		if ($this->conf['rssFeedOn'] == 1) {
			if ($GLOBALS["TSFE"]->type == 224) {  // RSS FEED
				$this->isRSSFeed = true;
			}
			// allow RSS feed to be "discovered" by feed readers			
			else if ($rssLink = $this->conf['xml.']['rss.']['link']) {
				if (strpos($rssLink,'http:') === FALSE) {
					$urlParam['type'] = 224;
					$urlParam['sp'] = $this->pid_list;
					$rssURL = $this->getAbsoluteURL($this->id,$urlParam,TRUE);
				}
				else {
					$rssURL = $rssLink;
				}
				$rssTitle =  $this->conf['xml.']['rss.']['channel_title'] ? $this->conf['xml.']['rss.']['channel_title'] : ($this->config['title'] ? $this->config['title'] : 'RSS 2.0');
				$GLOBALS['TSFE']->additionalHeaderData['tx_wecdevo'] = '<link rel="alternate" type="application/rss+xml" title="'.$rssTitle.'" href="'.$rssURL.'" />';
			}
		}
				
		// ------------------------------------------------------------
		// Check INCOMING POST Vars
		// ------------------------------------------------------------

		// CURRENT PAGE
		$this->curPage = ($pagenum = htmlspecialchars(t3lib_div::_GP('pagenum'))) ? $pagenum : 1;

		// TEXT PAGING/SIZE
		if ($this->postvars['txtpg']) $textPaging = intval($this->postvars['txtpg']);
		if ($this->postvars['txtsz']) $textSize = intval($this->postvars['txtsz']);
		// SHOULD UPDATE COOKIE???
		if ((isset($textPaging) && ($this->curTextPaging != $textPaging)) || (isset($textSize) && ($this->curTextSize != $textSize))) {
			if (isset($textPaging)) $this->curTextPaging = $textPaging;
			if (isset($textSize)) $this->curTextSize = $textSize;
			// we now save the data to a cookie.  This must be done here and page reloaded.
			$this->saveUserData();
			header('Location: '.t3lib_div::locationHeaderURL($this->getURL($GLOBALS['TSFE']->id, $this->curDay, $this->curSec)));
		}

		// UPDATE TEXT PAGING STYLE if passed in
		if (isset($textPaging)) $this->curTextPaging = $textPaging;

		// UPDATE TEXT SIZE if passed in
		if (isset($textSize)) $this->curTextSize = $textSize;

		// SAVE JOURNAL...
		$saveJournal = htmlspecialchars($this->postvars['saveJournal']);
		if(isset($saveJournal) && $saveJournal == 1) {
			$saveJournalData = t3lib_div::_GP('journal_entry') ? t3lib_div::_GP('journal_entry') : $this->postvars['journal_entry'];
			$this->saveJournal($this->postvars['journal_date'], $saveJournalData);
			$this->curDay = $this->postvars['show_date'];
			$this->curSec = $this->postvars['section'];			
		}

		//
		// PRINTING JOURNAL...
		$printJournalStart = htmlspecialchars(t3lib_div::_GP('printstart'));
		$printJournalEnd = htmlspecialchars(t3lib_div::_GP('printend'));
		if (isset($printJournalStart) && ($printJournalStart > 0) && isset($printJournalEnd) && ($printJournalEnd > 0)) {
			$this->printJournal($printJournalStart, $printJournalEnd);
			exit(1);
		}

		//
		// LOGOUT...
		$isLogout = t3lib_div::_GP('logintype');
		if ($isLogout == 'logout') {
			header('Location: '.t3lib_div::locationHeaderURL($this->pi_getPageLink($GLOBALS['TSFE']->id)));
			exit;
		}

		// if passed in wec discussion vars, then setup for the wec discussion plugin
		if (t3lib_div::_GP('tx_wecdiscussion')) {
			$this->forumVars = t3lib_div::_GP('tx_wecdiscussion');
			// SECURITY: make sure the passed in vars are secure
			if (is_array($this->forumVars))
				foreach ($this->forumVars as $fvKey => $fvVal) {
					$this->forumVars[$fvKey] = $fvVal; //htmlspecialchars($fvVal);
				}
		}
		
	}

	/**
	* Main function: calls the init() function and sets up templates and fills with content
	*
	* @param string  $content : function output is added to this
	* @param array  $conf : TypoScript configuration array
	* @return string  $content: complete content generated by the plugin
	*/
	function main($content,$conf) {
	   	$this->init($conf);
	   	if ($conf['isLoaded'] != 'yes') 
	      return $this->pi_getLL('errorIncludeStatic');
	
		// if RSS Feed, then just show that
		if ($this->isRSSFeed) {
			return $this->displayRSSFeed();
		}
		
 		// LOAD TEMPLATE FILE
		//-------------------------
		$templateCode = $this->cObj->fileResource($this->conf['templateFile']);
		$this->subpartMarker = array();
	  	// BUILD EACH PIECE AND THEN DISPLAY
		//-------------------------------------------------------------------------------
		$this->marker['###SECTION_INTERFACE###'] = $this->displaySectionInterface($this->curDay, $this->curSec);
		$this->marker['###SELECT_TOPIC###'] = $this->displayTopicSelector($this->curDayTS); // this needs to be above the displayDevo so that curTopic can be set

		// The templateName is set in the displaySectionInterface which reads from the database what templateName to set
		if ($this->templateName && (strlen($this->templateName) > 0))
			$subTemplateName = '###'.trim($this->templateName).'###';
		else
			$subTemplateName = '###TEMPLATE_DEFAULT###';

		// Read in the part of the template file with the 'subtemplatename'
		$thisTemplate = $this->cObj->getSubpart($templateCode,$subTemplateName);
		// clear out iconBox if disabled
		if (!$this->config['allowPaging'] && !$this->config['allowTextSize']) {
			$thisTemplate = $this->cObj->substituteSubpart($thisTemplate, '###SHOW_ICONBOX###', '');
		}
		if (!strstr($thisTemplate, '###TEXTPAGING_ICONS###')) { // if no text paging in template => set default
			if (!$this->forceTextPaging) $this->curTextPaging = SHOW_FULL;
		}
		if (!strstr($thisTemplate, '###TEXTSIZE_ICONS###')) { // if no text size in template => set default
			if (!$this->forceTextSize) $this->curTextSize = 2;
		}
		
		// handle if no journal, then do not display rightCol
		if (!$this->config['hasJournal']) {
			$this->marker['###JOURNAL_RIGHTCOL_CSS###'] = ' hideCol';
		}
		else {
			$thisTemplate = $this->cObj->substituteSubpart($thisTemplate, '###SHOW_ICONBAR###', ''); 
		}
		
		// add RSS feed icon
		$this->marker = $this->addSubscribeRSSFeed($this->marker);		

		// Generate main content
		//----------------------
		$numContent = $this->displayDevo($this->curDayTS, $this->curSec);

		// Show paging and text size icons
		if ($this->config['allowPaging'] && !$this->forceTextPaging)
			$this->marker['###TEXTPAGING_ICONS###'] = $this->displayTextPagingIcons();
		if ($this->config['allowTextSize'] && !$this->forceTextSize)
			$this->marker['###TEXTSIZE_ICONS###'] = $this->displayTextSizeIcons();

		// Set large/medium/small content size class

		switch ($this->curTextSize) {
			case 1:
				$contentClass = 'tx-wecdevo-contentSmall'; 	break;
			case 3:
				$contentClass = 'tx-wecdevo-contentBig';  	break;
			case 2:
			default:
				$contentClass = 'tx-wecdevo-content';
		};
		$this->marker['###CONTENT_CLASS###'] = $contentClass;

		// Show date
		$thisDate = date($this->config['date_format'] ? $this->config['date_format'] : "F j, Y", $this->curDayTS);
		$this->marker['###DATE###'] = '<div class="tx-wecdevo-showDateText">'.$thisDate.'</div>';

		$this->marker['###DAILY_CALENDAR###'] = $this->displayCalendar($this->curDayTS, $this->curSec);
		$this->marker['###WEEK_CALENDAR###'] = $this->displayWeekCalendar($this->curDayTS, $this->curSec);
		if ($this->curDayTS != mktime(12,0,0)) {
			$todayDate = date('mdy', mktime(12,0,0));
			$this->marker['###TODAY###'] = '<div class="tx-wecdevo-showToday"><a href="'.$this->getUrl($GLOBALS['TSFE']->id, $todayDate, $this->curSec).'">today</a></div>';
		}
		else
			$this->marker['###TODAY###'] = '';

		// need to allow journal and have a valid user before can show journal
		if ($this->config['hasJournal']) {
			// if logged in and can use journal
			if ($this->userID) {
				$this->marker['###JOURNAL###'] = $this->displayJournal($this->curDay);
				if ($this->conf['journalOnObj']) {
					$this->marker['###JOURNAL###'] .= $this->cObj->cObjGetSingle($this->conf['journalOnObj'], $this->conf["journalOnObj."]);
				}
			}
			// if not logged in, then show any TS to show login or message...
			elseif ($this->conf['journalOffObj']) {
				$this->marker['###JOURNAL###'] = $this->cObj->cObjGetSingle($this->conf['journalOffObj'], $this->conf["journalOffObj."]);
			}
		}

		// only add monthly calendar if it is in template
		if (strstr($thisTemplate,'###MONTHLY_CALENDAR###'))
			$this->marker['###MONTHLY_CALENDAR###'] = $this->displayMonthlyCalendar($this->curDay);

		// only add this if the marker is in code & want comments (because this loads & inits discussion extension)
		if ((strstr($thisTemplate,'###COMMENTS###')) && $this->config['comments_pid']) {
			$this->marker['###COMMENTS###'] = $this->addComments();
		}
		// only add this if the marker is in the code (because this loads & inits discussion extension)
		if (strstr($thisTemplate,'###DISCUSSION_PREVIEW###') && $this->config['preview_pid']) {
			$this->marker['###DISCUSSION_PREVIEW###'] = $this->addDiscussionPreview();
		}
		// clear out scripture ref if it is empty
		if (!strlen($this->marker['###SCRIPTURE_REF###'])) {
			$thisTemplate = $this->cObj->substituteSubpart($thisTemplate,'###SHOW_SCRIPTURE_REF###','');
		}

		// Substitute all the markers in the template into appropriate places
		$content = $this->cObj->substituteMarkerArrayCached($thisTemplate,$this->marker,$this->subpartMarker, array());

		// clear out any empty template fields
		$content = preg_replace('/###.*?###/', '', $content);

		// set robots not following after certain dates OR if no content...
		$checkDate = !$this->curDay ? gmdate('Ymd') : date('Ymd', mktime(12, 0, 0, substr($this->curDay, 0, 2), substr($this->curDay, 2, 2), substr($this->curDay, 4, 2)));
		if (!$numContent || ($checkDate <= date('Ymd', strtotime($this->conf['startLinkRange']))) || ($checkDate >= date('Ymd', strtotime($this->conf['endLinkRange'])))) {
			// Set additional META-Tag for google and other robots
			$GLOBALS['TSFE']->additionalHeaderData['tx_wecdevo'] .= '<meta name="robots" content="index,nofollow" />';
			// Set/override no_search for current page-object
			$GLOBALS['TSFE']->page['no_search'] = 0;
		}

//		if (is_array($this->conf['general_stdWrap.'])) {
//			$content = $this->cObj->stdWrap($content, $this->conf['general_stdWrap.']);
//		}
		
		$GLOBALS['TSFE']->additionalHeaderData['tx_wecdevo'] .= $this->headerData;
		
		return $this->pi_wrapInBaseClass($content);
	}

	/**
	*==================================================================================
	* SAVE USER DATA
	*
	*   Save the user data in a cookie
	*
	* @return void
	*==================================================================================
	*/
	function saveUserData() {
		$saveUserData[0] = time();
		$saveUserData[1] = $this->curTextPaging;
		$saveUserData[2] = $this->curTextSize;
		$this->setWECcookie($GLOBALS['TSFE']->id, $saveUserData);
	}

	/**
	*==================================================================================
	* DISPLAY SECTION INTERFACE
	*
	*    Show the sections in a horizontal interface. Supports single level row of interface.
	*
	* @param  string $curDT current date
	* @param integer $curSectionID  current section
	* @return  string  $content to display section
	*==================================================================================
	*/
	function displaySectionInterface($curDT, $curSectionID)	{
		$sections = array();		// [index][value] -- the interface elements loaded from database
		$selSectionID = 0;			// selected section item
		$selSectionIndex = 0; 		// array index of selected section
		$sectionCount = 0;			// count of elements
		$if_content = '';			// generated content

		//  LOAD CATEGORIES FROM DATABASE
		//
		// load in all of the sections from database. Store in array $sections[][]
		//  set the selSectionID & selSectionIndex
		//-----------------------------------------------------------------------------------------
		$where = 'pid IN('.$this->sectionDataPID.') AND deleted=0 AND hidden=0';
		$orderBy = 'position';
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', $this->devoSectionTable, $where, '', $orderBy, '');
//		$query = 'SELECT * FROM '.$this->devoSectionTable.' WHERE pid IN('.$this->sectionDataPID.') AND deleted=0 AND hidden=0 ORDER BY position';
//		$res = $GLOBALS['TYPO3_DB']->sql(TYPO3_db, $query);
		if (mysql_error()) t3lib_div::debug(array(mysql_error(), $query));
			$count = $GLOBALS['TYPO3_DB']->sql_num_rows($res);
		if ($count == 0) // if no sections, then no interface
		return "";

		$interfWeekly = -1;
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$interfDefault = 0;
			$interfID = $row['uid'];
			$interfDataUID = $row['data_uid'];
			$interfStart = $row['start_at'];

			// if we have a data UID, then replace UID with that...because just duplicating data with data_UID
			if ($interfDataUID)
				$interfID = $interfDataUID;
			// if have start_at set, then set the global vars appropriately
			if ($interfStart == 2) // DEFAULT
				$interfDefault = $interfID;
			else if ($interfStart == 3) // WEEKLY
				$interfWeekly = $sectionCount;

			$sections[$sectionCount]['name'] = $row['name'];
			$sections[$sectionCount]['uid'] = $interfID;
			$sections[$sectionCount]['parent_uid'] = 0; // no parent
			$sections[$sectionCount]['time_period'] = $row['time_period'];
			$sections[$sectionCount]['template'] = trim($row['template']);
			$sections[$sectionCount]['next_sequence'] = $row['next_sequence'];
			// If the current section ID is this one OR this is the default, then setup
			if (!$selSectionID && ($curSectionID == $interfID) || (!$curSectionID && $interfDefault)) {
				$selSectionID = $interfID;
				$selSectionIndex = $sectionCount;
			}

			$sectionCount++;
		}

		// check if weekly should start
		if (($interfWeekly != -1) && !$curSectionID && $this->lastLoginDate) {
			// see when last logged in & compare to today
			$devoDateTime = $this->curDayTS;
			$startDayOfWeekTS = mktime(12, 0, 0, date('m', $devoDateTime), date('d', $devoDateTime) - date('w', $devoDateTime), date('Y', $devoDateTime));
			if ((date('j', $devoDateTime) - date('w', $devoDateTime)) <= 0) // if #days in month - #days in week, THEN previous month
				$startDayOfWeekTS = mktime(12, 0, 0, date('m', $startDayOfWeekTS), date('d', $startDayOfWeekTS), date('Y', $startDayOfWeekTS));
			if ($this->lastLoginDate < $startDayOfWeekTS) {
				$selSectionID = $sections[$interfWeekly]['uid'];
				$selSectionIndex = $interfWeekly;
			}
		}

		// if no section selected, then select first one as default section
		if (!$selSectionID && !$curSectionID || !isset($curSectionID)) {
			$selSectionID = $sections[0]['uid'];
			$selSectionIndex = 0;
		}

		$this->nextInSequence = $sections[$selSectionIndex]['next_sequence'];

		// SET THE SELECTED SECTION
		if (strlen($sections[$selSectionIndex]['template']) != 0) { // set templateName if it is set
			$this->templateName = $sections[$selSectionIndex]['template'];
		}
		// SET THE GLOBAL SECTION IF NOT SET
		if (!$this->curSec)
			$this->curSec = $selSectionID;

		// DRAW INTERFACE
		//
		// the section interface is drawn in a CSS format
		// it uses CSS-styled text buttons with optional backgrounds
		//-----------------------------------------------------------------------------------------
		$if_content = '<div class="ddcolortabs" id="ddcolortabs">
				<ul>
		';
		for ($i = 0; $i < $sectionCount; $i++) {
			if ($selSectionID == $sections[$i]['uid'])
				$if_content .= '<li id="current">';
			else
				$if_content .= '<li>';
			$if_content .= "<a href=\"".$this->getUrl($GLOBALS['TSFE']->id, $curDT, $sections[$i]['uid'])."\"";
			$if_content .= ' style="text-decoration:none">';
			$if_content .= '<span>';
			$if_content .= $sections[$i]['name'];
			$if_content .= '</span>';
			$if_content .= '</a>';
			$if_content .= '</li>';
		}
		$if_content .= '
			</ul>
			</div>
			<div class="ddcolortabsline">&nbsp;</div>
		';

		return $if_content;

	}

	/**
	*==================================================================================
	* DISPLAY DEVOTION
	*
	* This will display the devotion content for the given date and section.
	*
	* @param  string devoDateTime datetime_stamp of start
	* @param integer devoSection  section
	* @return  integer count of content elements displayed
	*==================================================================================
	*/
	function displayDevo($devoDateTime, $devoSection) {
		$contentCount = 0;
		$this->curContentTitle = 0;
		$this->curVerses = '';

		// if a date offset, then the display time needs to be adjusted
		if ($this->dateOffset) {
			$devoDateTime = $devoDateTime - $this->dateOffset;
		}

		//-----------------------------------------------------------------------------------------
		//
		// load in ALL content for the given day and section
		// (order by tstamp so last one updated will be shown if duplicate)
		//-----------------------------------------------------------------------------------------
//		$query = 'SELECT * FROM '.$this->devoContentTable.' WHERE starttime<='.intval($devoDateTime).' AND endtime>'.intval($devoDateTime).' AND (section='.intval($devoSection).' OR section=0) AND pid IN ('.$this->pid_list.') AND deleted=0 ORDER BY endtime DESC,tstamp ASC';
//		$res = $GLOBALS['TYPO3_DB']->sql(TYPO3_db, $query);
		$where = 'starttime<='.intval($devoDateTime).' AND endtime>'.intval($devoDateTime).' AND (section='.intval($devoSection).' OR section=0) AND pid IN ('.$this->pid_list.') AND deleted=0';
		$orderBy = 'endtime DESC,tstamp ASC';
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', $this->devoContentTable, $where, '', $orderBy, '');
		if (mysql_error()) t3lib_div::debug(array(mysql_error(), $query));
			$count = $GLOBALS['TYPO3_DB']->sql_num_rows($res);
		if ($count > 0) {
			$makeMainPlain = 0; // if main content should go through paged_content or not
			while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
				$ifCat = $row['section'];
				$mainContent = '';
				// IF TYPE == HTMLTEXT (RTE)
				if ($row['type'] == 0) {
					$mainContent = $row['content'];
				}
				// IF TYPE == PAGE, THEN LOAD THE PAGE (# comes from 'content')
				else if ($row['type'] == 1) {
					$gotoPageID = trim($row['content']);
					$gotoPageID = strip_tags($gotoPageID);

					// load page id based on page#
					$query = array(
						'table' => 'tt_content',
						'select.' => array(
						'pidInList' => $gotoPageID,
						'orderBy' => 'sorting',
						'where' => 'colPos = 0 AND CType != "'.$extKey.'_pi1 "'.$this->cObj->enableFields('tt_content'), // Avoid any loop
						'languageField' => 'sys_language_uid' )
					);
					$mainContent = $GLOBALS['TSFE']->cObj->CONTENT($query);

					// remove the <div
					$newMainContent = preg_replace("'<div class[^>]*?pi1\">'" , '', $mainContent);
					if (strlen($mainContent) != strlen($newMainContent)) {
						// find last occurrence of </div>
						$newstr = strrev($newMainContent);
						if ($lastI = strpos($newstr, '>vid/<')) {
							$lastI = strlen($newMainContent)-$lastI-6;
							$newMainContent = substr_replace($newMainContent, '', $lastI, 6);
						}
					}
					$mainContent = $newMainContent;
				}
				// IF TYPE == FORUM, THEN LOAD THE FORUM PAGE (# comes from 'content')
				else if ($row['type'] == 2)  {
					// load in page#
					$gotoPageID = trim($row['content']);
					$gotoPageID = strip_tags($gotoPageID);
					$_POST['wecdiscussion_inside'] = $gotoPageID;
					if ($this->forumVars) {
						$_POST['tx_wecdiscussion'] = $this->forumVars;
					}
					$_GET['tx_wecdevo']['show_date'] = $this->postvars['show_date'];

					// load page id based on page#
					$query = array(
						'table' => 'tt_content',
						'select.' => array(
							'pidInList' => $gotoPageID,
							'orderBy' => 'sorting',
							'where' => 'CType != "'.$extKey.'_pi1 "'.$this->cObj->enableFields('tt_content'), // Avoid any loop
							'languageField' => 'sys_language_uid' 
						)
					);
					$mainContent = $this->cObj->CONTENT($query);
					$makeMainPlain = 1;
					$this->headerData = '';
				}
				// if TYPE == HEADER, then stuff in header
				else if ($row['type'] == 5) {
					$this->marker['###HEADER###'] = $row['content'];
					continue;
				}
				if ($makeMainPlain) {
					$this->marker['###MAIN_CONTENT###'] = $mainContent;
					$this->marker['###SCRIPTURE###'] = $row['scripture'];
				} else {
					$this->showPagedContent($mainContent, $this->curTextPaging, $this->curPage, '###MAIN_CONTENT###');
					$this->showPagedContent($row['scripture'], $this->curTextPaging, $this->curPage, '###SCRIPTURE###');
				}
				$this->marker['###SCRIPTURE_REF###'] = trim($row['scripture_ref']);
				$this->marker['###QUESTIONS###'] = $row['questions'];
				$this->marker['###PRAYER###'] = $row['prayer'];
				$this->marker['###APPLICATION###'] = $row['application'];
				$this->marker['###END_CONTENT###'] = $row['end_content'];

				$nextArrowContent = '';
				if ($this->config['useNextArrow'] && $this->nextInSequence) {
					$nextSequenceURL = $this->getUrl($GLOBALS['TSFE']->id, $this->curDay, $this->nextInSequence, $paramArray);
					$nextArrowContent = '<div class="tx-wecdevo-nextArrow" id="next_arrow">';
					$altStr = 'alt="'.$this->pi_getLL('nextArrow_alt', 'Go To Next Section').'", title="'.$this->pi_getLL('nextArrow_title', 'Go To Next Section').'"';
					$nextArrowContent .= '<a href="'.$nextSequenceURL.'">'.$this->cObj->fileResource($this->conf['nextArrowImg'], $altStr).'</a>';
					$nextArrowContent .= '</div>';
				}
				$this->marker['###NEXT_ARROW###'] = $nextArrowContent;

				if ($this->config['hasAudio'])
					$this->marker['###AUDIO_LINK###'] = $this->buildAudioLink($row['audio_file'], $devoDateTime);

				if ($this->config['hasVideo'])
					$this->marker['###VIDEO_LINK###'] = $this->buildVideoLink($row['video_file'], $devoDateTime);


				// THE FOLLOWING IS FOR SPECIALIZED CODING THAT MAY OR MAY NOT BE USED...
				//
				// Pull out Title, if any, from the MAIN_CONTENT (wrapped in H4 tag)
				$titleTag = $this->conf['titleTag'];
				$titleTagStart = '<'.$titleTag.'>';
				$titleTagEnd = '</'.$titleTag.'>';
				$pregMatchTitle = "/(<".$titleTag.">).*(<\/".$titleTag.">)/Ui";
				if (preg_match($pregMatchTitle, $row['content'], $matches)) {
					$this->curContentTopic = $matches[0];
					$this->curContentTopic = str_replace($titleTagStart, '', $this->curContentTopic); // remove <h4>
					$this->curContentTopic = str_replace($titleTagEnd, '', $this->curContentTopic); // remove </h4>
					$this->curContentTopic = trim($this->curContentTopic);
				}
				// Pull out Scripture References...if any...from the SCRIPTURE (wrapped in H5 tag)
				$scriptureTag = $this->conf['scriptureTag'];
				if ($scriptureTag) {
					$scriptureTagStart = '<'.$scriptureTag.'>';
					$scriptureTagEnd = '</'.$scriptureTag.'>';
					$pregMatchScripture ="/(<".$scriptureTag.">).*(<\/".$scriptureTag.">)/Ui";
					if (preg_match_all($pregMatchScripture, $row['scripture'], $matches)) {
						for ($j = 0; $j < 5; $j++) { // up to 5 references
							if (strlen($matches[0][$j]) > 1) {
								if ($j > 0) $this->curVerses .= '; ';
								$this->curVerses .= trim($matches[0][$j]);
							}
						}
						$this->curVerses = str_replace($scriptureTagStart, '', $this->curVerses); // remove <h5>
						$this->curVerses = str_replace($scriptureTagEnd, '', $this->curVerses); // remove </h5>
						$this->curVerses = str_replace("'", "\\'", $this->curVerses); // handle single quote
					}
				}
				if (!$this->curVerses && $this->marker['###SCRIPTURE_REF###']) {
					$this->curVerses = $this->marker['###SCRIPTURE_REF###'];
				}
				// KEEP COUNT OF ALL VALID CONTENT FOR THIS PAGE
				$contentCount++;
			}
		}

		// return how many content pieces there are
		return $contentCount;
	}


	/**
	*==================================================================================
	* showPagedContent
	*
	* Handles showing page(s) of text. Just pass text in, and will format and
	*
	* how to show...
	*  = BY_PAGES
	*  = SCROLLBOX
	*  = FULL
	*
	*  variables...
	*  config['page_chars'] = how many characters per page
	*  config['page_width'] = how many characters wide (default = 80)
	*  config['break_on_paragraph'] = [TRUE/FALSE] -- if should break page on paragraph or not
	*
	*
	*  @param string $pageTextHTML
	*  @param integer $how  how to show
	*  @param integer $whatpage which page to show (from 1 to #)
	*  @param string $cMarker which marker to use
	*  @return void
	*==================================================================================
	*/
	function showPagedContent($pageTextHTML, $how = SHOW_FULL, $whatPage = 1, $cMarker = '###MAIN_CONTENT###') {
		$pageWidth = $this->config['page_width'] ? $this->config['page_width'] : 70;
		$pageCharSize = $this->config['page_chars'] ? $this->config['page_chars'] : 1000;
		$pageText = strip_tags($pageTextHTML);
		$pageTextLen = strlen($pageText);
		$totalPages = (int) ($pageTextLen / $pageCharSize) + 1;
		if ($totalPages == 1) $how = SHOW_FULL;

		switch ($how) // how to show the paged content
		{
			case SHOW_BY_PAGES:
				// read in all the text & break up ALL pages. Then copy current page
				// how this works is by looking at the plain text (no HTML) and then breaking it up according to that,
				// then finding where that end is in the HTML text.
				$startPage[1] = 0;
				$startPageHTML[1] = 0;

				// scan the first 250 chars and if there is a header <H#> tag, keep it
				//$hdrStr = substr($pageTextHTML, 0, (($pageTextLen > 250) ? 250 : $pageTextLen));
				$headerStr = '';
				if (preg_match("/(<h4>).*(<\/h4>)/Ui", $pageTextHTML, $matches)) {
					$headerStr = $matches[0];
				}
				// break up at end of sentences or paragraphs
				if ($this->config['break_on_paragraph']) {
					for ($p = 1; $p <= $totalPages; $p++) {
						$startIndex = $startPage[$p];
						$endIndex = $startIndex + $pageCharSize;
						if ($p == $totalPages) {
							$endIndex = $pageTextLen;
							 break;
						}

						// now go through and search for \n or \r to find the end of last paragraph
						for ($i = $endIndex; $i >= $startIndex; $i--) {
							if (($pageText[$i] == "\n") || ($pageText[$i] == "\r")) {
								$startPage[$p+1] = $i+1;
								break;
							}
						}
						if ($i == $startIndex) // no breaks
						$startPage[$p+1] = $endIndex;
						else
							$endIndex = $i+1;

						// now, given the new endIndex...find the right endIndex in pageTextHTML
						$lastFifteen = trim(substr($pageText, $endIndex-15, 15));
						$endOfPage = strpos($pageTextHTML, $lastFifteen, $endIndex-1);
						if (!$endOfPage) // this would happen if the text from pageText has HTML tags in pageTextHTML
						{
							// try with 6 chars...
							$lastSix = trim(substr($pageText, $endIndex-6, 6));
							if ($endOfPage = strpos($pageTextHTML, $lastSix, $endIndex-1))
								$endOfPage += strlen($lastSix);
							else
								$endOfPage = $endIndex; // just take somewhere ok...
						}
						else
							$endOfPage += strlen($lastFifteen);
						$startPage[$p+1] = $endIndex;
						$startPageHTML[$p+1] = $endOfPage;
					}
				}
				else // break at end of sentences
				{
					for ($p = 1; $p <= $totalPages; $p++) {
						$startIndex = $startPage[$p];
						$endIndex = $startIndex + $pageCharSize;

						if ($p == $totalPages) {
							$endIndex = $pageTextLen;
							 break;
						}

						// now go through and set the end index (start of next page) if end of sentence
						for ($i = $endIndex; $i >= $startIndex; $i--) {
							if (($pageText[$i] == '.') || (($pageText[$i] == '!') && ($pageText[$i-1] != '<')) || ($pageText[$i] == '\n') || ($pageText[$i] == '\r') || (($pageText[$i] == '"') && (($pageText[$i-1] == '.') || ($pageText[$i-1] == '!')))
							) {
								$endIndex = $i+1;
								$startPage[$p+1] = $i+1;
								break;
							}
						}
						// now, given the new endIndex...find the right endIndex in pageTextHTML
						// end the lastFifteen on the end of the last word (not in middle of a word)
						for ($k = $endIndex; $k--; $k >= 0) {
							if ($pageText[$k] == ' ' || $pageText[$k] == '.' || $pageText[$k] == '"') {
								$lastFifteen = substr($pageText, $k - 14, 15);
								break;
							}
						}

						$endOfPage = strpos($pageTextHTML, $lastFifteen, $endIndex-1);
						if ($endOfPage === false) // this would happen if the text from pageText has HTML tags in pageTextHTML
						{
							// try with 6 chars...
							$lastSix = trim(substr($pageText, $endIndex-6, 6));
							if ($endOfPage = strpos($pageTextHTML, $lastSix, $endIndex-1)) {
								$endOfPage += strlen($lastSix);
							} else {
								for ($k = $endIndex; $k--; $k >= 0) {
									if ($pageText[$k] == ' ' || $pageText[$k] == '.') {
										$endOfPage = $endIndex; // just take somewhere ok...
										break;
									}
								}
							}
						}
						else
							$endOfPage += strlen($lastFifteen);

						$startPage[$p+1] = $endIndex;
						$startPageHTML[$p+1] = $endOfPage;
					}
				}
				$startPageHTML[$totalPages+1] = strlen($pageTextHTML);
				$pageLen = $startPageHTML[$this->curPage+1] - $startPageHTML[$this->curPage];

				// add javascript for paging...
				$showPageText = '
					<script type="text/javascript">
					//<![CDATA[
					var curPageNum = 1;

					function devo_showPage(newPageNum) {
						if (newPageNum == curPageNum)
						return false;

						if (oldPageID = document.getElementById("showpage_"+curPageNum)) {
							oldPageID.style.display = \'none\';
						}
						if (newPageID = document.getElementById("showpage_"+newPageNum)) {
							newPageID.style.display = \'block\';
						}

						if (oldPageNumID = document.getElementById("devo_pagenum"+curPageNum)) {
							oldPageNumID.style.fontWeight = \'normal\';
							oldPageNumID.style.backgroundColor = \'#FFFFFF\';
						}
						if (newPageNumID = document.getElementById("devo_pagenum"+newPageNum)) {
							newPageNumID.style.fontWeight = \'bold\';
							newPageNumID.style.backgroundColor = \'#40F040\';
						}

						curPageNum = newPageNum;

					return false;
					}
					//]]>
					</script>
					';

				// add all pages together here with appropriate markers
				for ($i = 1; $i <= $totalPages; $i++) {
					$pageLen = $startPageHTML[$i+1] - $startPageHTML[$i];
					$showPageText .= '<div id="showpage_'.$i.'" style="display:'.(($i == 1) ? "block" : "none").';">'. (($i == 1) ? "" : $headerStr). substr($pageTextHTML, $startPageHTML[$i], $pageLen) . '</div>';
				}
				// add page links
				$pageLinkText .= '<div class="tx-wecdevo-pagenum">'.$this->pi_getLL('pagenum_header', 'Page: ');
				for ($i = 1; $i <= $totalPages; $i++) {
					$pageLinkText .= '<div id="devo_pagenum'.$i.'" style="display:inline;'.(($i == $this->curPage)?"font-weight:bold;background-color:#40F040;":"font-weight:normal;").'"><a href="#" onclick="devo_showPage('.$i.');return false;">'.((string) $i).'</a></div>'.$this->pi_getLL('pagenum_spacer', '&nbsp;');
				}
				$pageLinkText .= '</div>';

				$showPageText .= $pageLinkText;

				// now set the marker with the current page info
				$this->marker[$cMarker] = $showPageText;

			break;

			case SHOW_SCROLLBOX: //  add all text to scrollbox.
				$BOX_HT = $this->conf['scrollbox_height'] ? $this->conf['scrollbox_height'] : ($pageCharSize / $pageWidth) * 18 ;
				$BOX_WD = $this->conf['scrollbox_width'] ? $this->conf['scrollbox_width'] : $pageWidth * 6;
				$showPageText = '<div id="scrollbox" style="height:'.$BOX_HT.'px;width:'.$BOX_WD.'px;">'.$pageTextHTML.'</div>';
				$this->marker[$cMarker] = $showPageText;
				break;

			case SHOW_FULL: // show all text
			default:
				$this->marker[$cMarker] = $pageTextHTML;
				break;

		}
	}

	/**
	*=====================================================================================
	* Build Audio Link
	*
	* @param  string audioString
	* @param  string devoDateTime
	* @return  string  content that contains the audio link/player
	*=====================================================================================
	*/
	function buildAudioLink($audioString, $devoDateTime) {
		$audioLinkStr = '';

		// if has audio, then show the audio player
		//--------------------------------------------------------------------
		if (isset($audioString) && ((strlen($audioString) > 3) || ((int)$audioString != 0))) {
			$audioFilePath = $this->conf['audioPath'];
			$downloadAudioIcon = $this->conf['downloadAudioIcon'];
			if (((int) $audioString >= 1) && ((int) $audioString <= 9) && $this->config['audiofile_autoformat']) // then auto-generate
			{
				// extract & parse audio formatting string
				$aFormatStr = t3lib_div::trimExplode('|', $this->config['audiofile_autoformat']);
				for ($i = 0; $i < sizeof($aFormatStr); $i += 2) {
					$num = (int) trim($aFormatStr[$i]);
					$aFormatList[$num] = trim($aFormatStr[$i+1]);
				}
				$aNum = (int)$audioString;
				if (strlen($aFormatList[$aNum]) > 0) {
					$audioFileName = $this->generateAudioVideoFilename($aFormatList[$aNum], $devoDateTime);
				}
			} else {
				$audioFileName = $audioString;
			}

			$fullAudioFileName = $audioFilePath . $audioFileName;
			if (@file_exists($fullAudioFileName)) // only show link if file exists
			{
				$audioLinkStr = '<div class="tx-wecdevo-audioLink">';
				// if we have an audio player, then load in that code. Otherwise just put in link.
				if ($audioPlayerCode = $this->config['audioPlayer_code']) {
					$audioPlayerCode = str_replace('#AUDIO_PLAYERURL#', $this->conf['audioPlayerURL'], $audioPlayerCode);
					$audioPlayerCode = str_replace('#AUDIO_FILENAME#', $audioFileName, $audioPlayerCode);
					$audioPlayerCode = str_replace('#AUDIO_FILEPATH#', $audioFilePath, $audioPlayerCode);
					$audioLinkStr .= $audioPlayerCode;
				} else {
					$audioLinkStr .= "<a href='".$fullAudioFileName."'>".$this->pi_getLL('play_audio', 'Play').'</a>';
				}

				// add download icon
				if (strlen($downloadAudioIcon) > 1) {
					$audioLinkStr .= '
						<a href="'.$fullAudioFileName.'" onclick="alert(\''.$this->pi_getLL('download_audio_help', 'Right click on this and choose Save Target/File As to download this MP3').'\');return false;"><img src="'.$downloadAudioIcon.'" border=0></a>
						';
				}
				$audioLinkStr .= '</div>';
			} else {
				// if should be a file, but not found/available, put in comment for debugging...
				if (strlen($audioFileName) > 1)
					$audioLinkStr = "<!-- audio file not available= '".$fullAudioFileName."' -->";
			}
		} else {
			// put in blank...
			$audioLinkStr = '';
		}

		return $audioLinkStr;
	}

	/**
	*=====================================================================================
	* Build Video Link
	*
	* @param  string videoString
	* @param  string devoDateTime
	* @return  string  content that contains the video link/player
	*=====================================================================================
	*/
	function buildVideoLink($videoString, $devoDateTime) {
		$videoLinkStr = '';

		// if has video, then show the video player
		//--------------------------------------------------------------------
		if (isset($videoString) && ((strlen($videoString) > 3) || ((int)$videoString != 0))) {
			$videoFilePath = $this->conf['videoPath'];
			if (((int) $videoString >= 1) && ((int) $videoString <= 9) && $this->config['videofile_autoformat']) // then auto-generate
			{
				// extract & parse video formatting string
				$aFormatStr = t3lib_div::trimExplode('|', $this->config['videofile_autoformat']);
				for ($i = 0; $i < sizeof($aFormatStr); $i += 2) {
					$num = (int) trim($aFormatStr[$i]);
					$aFormatList[$num] = trim($aFormatStr[$i+1]);
				}
				$aNum = (int)$videoString;
				if (strlen($aFormatList[$aNum]) > 0) {
					$videoFileName = $this->generateAudioVideoFilename($aFormatList[$aNum], $devoDateTime);
				}
			} else {
				$videoFileName = $videoString;
			}

			$fullvideoFileName = $videoFilePath . $videoFileName;
			//   if (@file_exists($fullvideoFileName)) // only show link if file exists
			//   {
			$videoLinkStr = '<div class="tx-wecdevo-videoLink">';
			// if we have a video player, then load in that code. Otherwise just put in link.
			if ($videoPlayerCode = $this->config['videoPlayer_code']) {
				$videoPlayerCode = str_replace('#VIDEO_PLAYERURL#', $this->conf['videoPlayerURL'], $videoPlayerCode);
				$videoPlayerCode = str_replace('#VIDEO_FILENAME#', $videoFileName, $videoPlayerCode);
				$videoPlayerCode = str_replace('#VIDEO_FILEPATH#', $videoFilePath, $videoPlayerCode);
				$videoLinkStr .= $videoPlayerCode;
			} else {
				$videoLinkStr .= "<a href='".$fullvideoFileName."'>".$this->pi_getLL('play_video', 'Play').'</a>';
			}

			$videoLinkStr .= '</div>';
			//   }
		} else {
			// put in blank...
			$videoLinkStr = '';
		}

		return $videoLinkStr;
	}

	/**
	*=====================================================================================
	* Auto-Generate Audio/Video Filename: Create the file name from the codes given
	*
	* @param  string fileCode
	* @param  string devoDateTime
	* @return  string  generated file name based on codes
	*=====================================================================================
	*/
	function generateAudioVideoFilename($fileCode, $devoDateTime) {
		$genFileName = $fileCode;

		// Figure out possible replacement values for file code
		// #DATE# = Y-m-d
		// #DATE-mdy# = m-d-Y
		// #DATE-dmy# = d-m-Y
		// #DAY# = Mon/Tue/Wed/etc
		// #START_DOW# = Y-m-d of Sunday of that week
		// #END_DOW# = Y-m-d of Saturday of that week
		// #YEAR# = Y
		// #WEEKNUM# = week# (1-52)
		// #DAYNUM# = day# (1-365)
		// #TOPIC# = current topic

		$startDayOfWeekTS = mktime(12, 0, 0, date('m', $devoDateTime), date('d', $devoDateTime) - date('w', $devoDateTime), date('Y', $devoDateTime));
		if ((date('j', $theDateTime) - date('w', $devoDateTime)) <= 0) // if #days in month - #days in week, THEN previous month
		{
			$startDayOfWeekTS = mktime(12, 0, 0, date('m', $startDayOfWeekTS), date('d', $startDayOfWeekTS), date('Y', $startDayOfWeekTS));
		}
		$endDayOfWeekTS = mktime(12, 0, 0, date('m', $startDayOfWeekTS), date('d', $startDayOfWeekTS) + 6, date('Y', $startDayOfWeekTS));

		$curTopicStr = $this->curTopic;
		if ($curTopicStr && (strlen($curTopicStr) > 1)) {
			$curTopicStr = trim($curTopicStr);
			$curTopicStr = strtoupper($curTopicStr); // make all uppercase
//			$curTopicStr = ereg_replace('[^A-Za-z0-9_,]', '', $curTopicStr); // make  only alphanumeric for filename
			$curTopicStr = preg_replace('/[^A-Za-z0-9_,]/', '', $curTopicStr); // make  only alphanumeric for filename
			
		}
		else
			$curTopicStr = '';

		$genFileName = str_replace('#DATE#', "".date('Y-m-d', $devoDateTime), $genFileName);
		$genFileName = str_replace('#DATE-mdy#', "".date('m-d-Y', $devoDateTime), $genFileName);
		$genFileName = str_replace('#DATE-dmy#', "".date('d-m-Y', $devoDateTime), $genFileName);
		$genFileName = str_replace('#DAY#', "".date('D', $devoDateTime), $genFileName);
		$genFileName = str_replace('#YEAR#', date('Y', $devoDateTime), $genFileName);
		$genFileName = str_replace('#DAYNUM#', date('z', $devoDateTime), $genFileName);
		$genFileName = str_replace('#WEEKNUM#', date('W', $devoDateTime), $genFileName);
		$genFileName = str_replace('#START_DOW#', date('Y-m-d', $startDayOfWeekTS), $genFileName);
		$genFileName = str_replace('#END_DOW#', date('Y-m-d', $endDayOfWeekTS), $genFileName);

		$genFileName = str_replace('#TOPIC#', $curTopicStr, $genFileName);
		$genFileName = str_replace('#TOPIC_lc#', $curTopicStr, $genFileName);

		return $genFileName;
	}

	/**
	*=====================================================================================
	* Build Text Paging Icons
	*
	* Can change the text paging type to FULL / PAGED / SCROLLBAR
	*
	* @return  string  generated text paging display HTML
	*=====================================================================================
	*/
	function displayTextPagingIcons() {
		$gotoURL = $this->getUrl($GLOBALS['TSFE']->id, $this->curDay, $this->curSec);
		$textPagingIconStr = '<div class="tx-wecdevo-iconBox">';
		$textPagingIconStr .= '<div class="tx-wecdevo-iconBoxTitle">'.$this->pi_getLL('textpaging_header', 'Paging').'</div>';

		$ctp = $this->curTextPaging;
		if ($this->conf['iconPageFull']) {
			$extraParams['tx_wecdevo']['txtpg'] = SHOW_FULL;
			$gotoURL = $this->getUrl($GLOBALS['TSFE']->id, $this->curDay, $this->curSec, $extraParams);
			$altStr = 'alt="'.$this->pi_getLL('iconPageFull_alt', 'Show Full Page').'" title="'.$this->pi_getLL('iconPageFull_title', 'Show Full Page').'"';
			$textPagingIconStr .= '<span class="'.(($ctp == 1)? "iconSelected" : "icon").'"><a href="'.$gotoURL.'">'.$this->cObj->fileResource($this->conf['iconPageFull'], $altStr).'</a></span>';
		}
		if ($this->conf['iconPage123']) {
			$extraParams['tx_wecdevo']['txtpg'] = SHOW_BY_PAGES;
			$gotoURL = $this->getUrl($GLOBALS['TSFE']->id, $this->curDay, $this->curSec, $extraParams);
			$altStr = 'alt="'.$this->pi_getLL('iconPage123_alt', 'Show By Paging 1.2.3').'" title="'.$this->pi_getLL('iconPage123_title', 'Show By Paging 1.2.3').'"';
			$textPagingIconStr .= '<span class="'.(($ctp == 2)? "iconSelected" : "icon").'"><a href="'.$gotoURL.'">'.$this->cObj->fileResource($this->conf['iconPage123'], $altStr).'</a></span>';
		}
		if ($this->conf['iconPageScroll']) {
			$extraParams['tx_wecdevo']['txtpg'] = SHOW_SCROLLBOX;
			$gotoURL = $this->getUrl($GLOBALS['TSFE']->id, $this->curDay, $this->curSec, $extraParams);
			$altStr = 'alt="'.$this->pi_getLL('iconPageScroll_alt', 'Show In Scrollbox').'" title="'.$this->pi_getLL('iconPageScroll_title', 'Show in Scrollbox').'"';
			$textPagingIconStr .= '<span class="'.(($ctp == 3)? "iconSelected" : "icon").'"><a href="'.$gotoURL.'">'.$this->cObj->fileResource($this->conf['iconPageScroll'], $altStr).'</a></span>';
		}
		$textPagingIconStr .= '</div>';
		return $textPagingIconStr;
	}

	/**
	*=====================================================================================
	* Build Text Sizing Icons
	*
	* Can change the text size to SMALL / NORMAL / LARGE
	*
	* @return  string  generated text paging display HTML
	*=====================================================================================
	*/
	function displayTextSizeIcons() {
		$gotoURL = $this->getUrl($GLOBALS['TSFE']->id, $this->curDay, $this->curSec);
		$textSizeIconStr = '<div class="tx-wecdevo-iconBox">';
		$textSizeIconStr .= '<div class="tx-wecdevo-iconBoxTitle">'.$this->pi_getLL('textsize_header', 'Text Size').'</div>';
		$ctp = $this->curTextSize;
		if ($this->conf['iconTextSizeSmall']) {
			$extraParams['tx_wecdevo']['txtsz'] = 1;
			$gotoURL = $this->getUrl($GLOBALS['TSFE']->id, $this->curDay, $this->curSec, $extraParams);
			$altStr = 'alt="'.$this->pi_getLL('iconTextSizeSmall_alt', 'Small Text Size').'" title="'.$this->pi_getLL('iconTextSizeSmall_title', 'Small Text Size').'"';
			$textSizeIconStr .= '<span class="'.(($ctp == 1)? "iconSelected" : "icon").'"><a href="'.$gotoURL.'">'.$this->cObj->fileResource($this->conf['iconTextSizeSmall'], $altStr).'</a></span>';
		}
		if ($this->conf['iconTextSizeMed']) {
			$extraParams['tx_wecdevo']['txtsz'] = 2;
			$gotoURL = $this->getUrl($GLOBALS['TSFE']->id, $this->curDay, $this->curSec, $extraParams);
			$altStr = 'alt="'.$this->pi_getLL('iconTextSizeMed_alt', 'Medium Text Size').'" title="'.$this->pi_getLL('iconTextSizeMed_title', 'Medium Text Size').'"';
			$textSizeIconStr .= '<span class="'.(($ctp == 2)? "iconSelected" : "icon").'"><a href="'.$gotoURL.'">'.$this->cObj->fileResource($this->conf['iconTextSizeMed'], $altStr).'</a></span>';
		}
		if ($this->conf['iconTextSizeBig']) {
			$extraParams['tx_wecdevo']['txtsz'] = 3;
			$gotoURL = $this->getUrl($GLOBALS['TSFE']->id, $this->curDay, $this->curSec, $extraParams);
			$altStr = 'alt="'.$this->pi_getLL('iconTextSizeBig_alt', 'Large Text Size').'" title="'.$this->pi_getLL('iconTextSizeBig_title', 'Large Text Size').'"';
			$textSizeIconStr .= '<span class="'.(($ctp == 3)? "iconSelected" : "icon").'"><a href="'.$gotoURL.'">'.$this->cObj->fileResource($this->conf['iconTextSizeBig'], $altStr).'</a></span>';
		}

		$textSizeIconStr .= '</div>';
		return $textSizeIconStr;
	}

	/**
	*=====================================================================================
	* DISPLAY WEEKLY CALENDAR
	*
	*   Show a weekly calendar. Can select a date or go to previous or next week.
	*
	* @param  string theDateTime
	* @param  string currentIF
	* @return  string  code to display calendar in HTML
	*=====================================================================================
	*/
	function displayCalendar($theDateTime, $currentIF) {
		//====================================================================================================
		// Add Popup Tip in javascript
		//====================================================================================================
		$cal_content = '
			<div id="dhtmltooltip"></div>

			<script type="text/javascript">
			//<![CDATA[
			/***********************************************
			* Cool DHTML tooltip script- © Dynamic Drive DHTML code library (www.dynamicdrive.com)
			* This notice MUST stay intact for legal use
			* NOTE -- the tipobj.parentNode.offsetWidth/Height was added to compensate for this being in a <div> tag
			***********************************************/
			var offsetxpoint=0 // Customize x offset of tooltip
			var offsetypoint=25 // Customize y offset of tooltip
			var isIE=document.all
			var ns6=document.getElementById && !document.all
			var enabletip=false

			if (isIE||ns6) {
				var tipobj=document.all? document.all["dhtmltooltip"] : document.getElementById ? document.getElementById("dhtmltooltip") : ""
			}

			function ietruebody(){
				return (document.compatMode && document.compatMode!="BackCompat")? document.documentElement : document.body
			}

			function showtip(thetext, thecolor, thewidth){
				if (ns6||isIE){
				if (typeof thewidth!="undefined") tipobj.style.width=thewidth+"px"
				if (typeof thecolor!="undefined" && thecolor!="") tipobj.style.backgroundColor=thecolor
					tipobj.innerHTML=thetext;
					enabletip=true
					return false
				}
			}
			function showtipfixed(thetext, thecolor, thewidth, thex, they){
				if (ns6||isIE){
				if (typeof thewidth!="undefined") tipobj.style.width=thewidth+"px"
				if (typeof thecolor!="undefined" && thecolor!="") tipobj.style.backgroundColor=thecolor
				if (typeof thex!="undefined") tipobj.style.left = thex;
				if (typeof they!="undefined") tipobj.style.top = they;
				tipobj.innerHTML=thetext;
				enabletip=true
				return false
			}
			}
			function positiontip(e){
				if (enabletip){
					var curX=(ns6)?e.pageX - tipobj.parentNode.offsetLeft: event.x+ietruebody().scrollLeft;
					var curY=(ns6)?e.pageY - tipobj.parentNode.offsetTop: event.y+ietruebody().scrollTop;

					//Find out how close the mouse is to the corner of the window
					var rightedge=isIE&&!window.opera&&!ns6? ietruebody().clientWidth-event.clientX-offsetxpoint : window.innerWidth-e.clientX-offsetxpoint-20
					var bottomedge=isIE&&!window.opera? ietruebody().clientHeight-event.clientY-offsetypoint : window.innerHeight-e.clientY-offsetypoint-20

					var leftedge=(offsetxpoint<0)? offsetxpoint*(-1) : -1000

					//if the horizontal distance isn\'t enough to accomodate the width of the context menu
					if (rightedge<tipobj.offsetWidth) //move the horizontal position of the menu to the left by it\'s width
					{
					tipobj.style.left=isIE? ietruebody().scrollLeft+event.clientX-tipobj.offsetWidth+"px" : window.pageXOffset+e.clientX-tipobj.offsetWidth+"px"
					}
					else if (curX<leftedge)
					tipobj.style.left="5px"
					else       //position the horizontal position of the menu where the mouse is positioned
					tipobj.style.left=curX+offsetxpoint+"px"

					//same concept with the vertical position
					if (bottomedge<tipobj.offsetHeight)
					tipobj.style.top=isIE? ietruebody().scrollTop+event.clientY-tipobj.offsetHeight-offsetypoint+"px" : window.pageYOffset+e.clientY-tipobj.offsetHeight-offsetypoint+"px"
					else
					tipobj.style.top=curY+offsetypoint+"px"
					tipobj.style.visibility="visible"
				}
			}
			function hidetip(){
				if (ns6||isIE){
					enabletip=false
					tipobj.style.visibility="hidden"
					tipobj.style.left="-1000px"
					tipobj.style.backgroundColor=\'\'
					tipobj.style.width=\'\'
				}
			}

			document.onmousemove=positiontip

			//]]>
			</script>
			';
		// END JAVASCRIPT =============================================================================================================

		//
		// Determine start day of week based on current date
		// ---------------------------------------------------
		$thisDayOfWeek = date('w', $theDateTime); // 0 - 6
		$thisMonth = date('n', $theDateTime);
		$prevMonth = $thisMonth - 1;
		 if ($prevMonth == 0) $prevMonth = 12;
		$dayOfMonth = date('j', $theDateTime); // 1 -  31
		$startDayOfWeek = $dayOfMonth - $thisDayOfWeek;
		$startDayOfWeekTS = mktime(12, 0, 0, date('m', $theDateTime), date('d', $theDateTime) - $thisDayOfWeek, date('Y', $theDateTime));
		if ($startDayOfWeek <= 0) // previous month
		{
			$startDayOfWeekTS = mktime(12, 0, 0, date('m', $startDayOfWeekTS), date('d', $startDayOfWeekTS), date('Y', $startDayOfWeekTS));
			$startDayOfWeek = date('j', $startDayOfWeekTS);
		}

		//
		// Build table with weekly header
		// -------------------------------
		$weekDayList = array('Su', 'Mo', "Tu", 'We', "Th", 'Fr', 'Sa');
		$cal_content .= "<table cellspacing=\"0\" cellpadding=\"0\" border=\"0\" class=\"tx-wecdevo-calendarTable\">";
		$cal_content .= '<tr>';

		// Add previous week arrow
		$prevWeekDateTS = mktime(12, 0, 0, date('m', $startDayOfWeekTS), date('d', $startDayOfWeekTS) - 1, date('Y', $startDayOfWeekTS));
		$prevWeekDate = date('mdy', $prevWeekDateTS);
		if (!$this->first_datetime || ($prevWeekDateTS >= $this->first_datetime)) {
			$cal_content .= '
				<td class="tx-wecdevo-calendarTextLeft">
				<div class="tx-wecdevo-calendarArrow">
				<a href="'.$this->getUrl($GLOBALS['TSFE']->id, $prevWeekDate, $currentIF).'">
				'.$this->cObj->fileResource($this->conf['leftArrowImg']).'
				</a>
				</div>
				</td>
				';
		}

		$showDayOfWeek = $startDayOfWeek;
		$showDayOfWeekTS = $startDayOfWeekTS;
		for ($i = 0; $i < 7; $i++) {
			$thisDate = date('mdy', $showDayOfWeekTS);
			if ($showDayOfWeekTS == $theDateTime) {
				$cal_content .= "<td class=\"tx-wecdevo-calendarTextSelected\">";
			} else {
				$cal_content .= "<td class=\"tx-wecdevo-calendarText\">";
			}
			$gotoURL = $this->getUrl($GLOBALS['TSFE']->id, $thisDate, $currentIF);

			$cal_content .= '<a href="'.$gotoURL.'" onmouseover="showtip(\''.date('F j', $showDayOfWeekTS).'\');" onmouseout="hidetip();" >' . $weekDayList[$i] . '</a>';

			$cal_content .= '</td>';

			$showDayOfWeek++;
			if ($showDayOfWeek > date('t', $showDayOfWeekTS)) // if go into next month, then set date right
			{
				$showDayOfWeek = 1;
			}
			$showDayOfWeekTS = mktime(12, 0, 0, date('m', $showDayOfWeekTS), date('d', $showDayOfWeekTS)+1, date('Y', $showDayOfWeekTS));
		}

		// Add next week arrow
		$cal_content .= "<td class=\"tx-wecdevo-calendarTextRight\">";
		$nextWeekDateTS = mktime(12, 0, 0, date('m', $startDayOfWeekTS), date('d', $startDayOfWeekTS) + 7, date('Y', $startDayOfWeekTS));
		$nextWeekDate = date('mdy', $nextWeekDateTS);

		// Add next-arrow link if not going beyond end of year
		$thisWeekTimeTS = mktime(12, 0, 0);
		if (!$this->last_datetime || ($nextWeekDateTS <= $this->last_datetime)) {
			$cal_content .= '
				<div class="tx-wecdevo-calendarArrow">
				<a href="'.$this->getUrl($GLOBALS['TSFE']->id, $nextWeekDate, $currentIF).'">
				'.$this->cObj->fileResource($this->conf['rightArrowImg']).'
				</a>
				</div>
				';
		}
		$cal_content .= '</td>';

		//
		// Close off the table
		// -------------------------------------
		$cal_content .= '</tr></table>';

		return $cal_content;
	}

	/**
	*==================================================================================
	* DISPLAY WEEK
	*
	*   Show given week (Week Of Mon,Day To Mon,Day)
	*   Can go to previous or next week.
	*
	* @param integer $theDateTime datetime_stamp
	* @param integer  $currentIF   current section
	* @return string week display content
	*==================================================================================
	*/
	function displayWeekCalendar($theDateTime, $currentIF) {
		$week_content = '';
		$thisDayOfWeek = date('w', $theDateTime); // 0 - 6
		$dayOfMonth = date('j', $theDateTime); // 1 -  31
		$startDayOfWeek = $dayOfMonth - $thisDayOfWeek; // handle if < 0
		$startDayOfWeekTS = mktime(12, 0, 0, date('m', $theDateTime), date('d', $theDateTime) - $thisDayOfWeek, date('Y', $theDateTime));

		if ($startDayOfWeek <= 0) // handle if previous month
		{
			$startDayOfWeekTS = mktime(12, 0, 0, date('m', $startDayOfWeekTS), date('d', $startDayOfWeekTS), date('Y', $startDayOfWeekTS));
			$startDayOfWeek = date('j', $startDayOfWeekTS);
		}
		$startWeekStr = date('M', $startDayOfWeekTS).' '.$startDayOfWeek;

		$endDayOfWeekTS = mktime(12, 0, 0, date('m', $theDateTime), date('d', $theDateTime) + (6 - $thisDayOfWeek), date('Y', $theDateTime));
		$endDayOfWeek = date('j', $endDayOfWeekTS);
		$endWeekStr = '';
		if ($endDayOfWeek < $startDayOfWeek) // if overlapped to next month, then put
		{
			$endWeekStr = date('M', $endDayOfWeekTS).' ';
		}
		$endWeekStr .= $endDayOfWeek;

		// SHOW CURRENT WEEK
		//---------------------------------------------------------------------------------------
		$week_content .= '<div class="tx-wecdevo-showWeek">';
		$week_content .= $this->pi_getLL('week_of', 'Week of').' '.$startWeekStr.' - '.$endWeekStr.', '.date('Y', $theDateTime).' ';
		$week_content .= '</div>';

		// SHOW LINKS TO PREV / CUR / NEXT
		//---------------------------------------------------------------------------------------
		$prevWeekTS = mktime(0, 0, 0, date('m', $theDateTime), date('d', $theDateTime) - 7, date('Y', $theDateTime));
		$nextWeekTS = mktime(0, 0, 0, date('m', $theDateTime), date('d', $theDateTime) + 7, date('Y', $theDateTime));
		$week_content .= '<div class="tx-wecdevo-showWeekLink">';

		if (!$this->first_datetime || ($prevWeekTS > $this->first_datetime)) {
			$gotoURL = $this->getUrl($GLOBALS['TSFE']->id, date('mdy', $prevWeekTS), $currentIF);
			$week_content .= ' <a href="'.$gotoURL.'">'.$this->pi_getLL('prev_week', 'prev').'</a>';
		}

		if (!$this->last_datetime || ($nextWeekTS < $this->last_datetime)) {
			$gotoURL = $this->getUrl($GLOBALS['TSFE']->id, date('mdy', $nextWeekTS), $currentIF);
			$week_content .= ' | <a href="'.$gotoURL.'">'.$this->pi_getLL('next_week', 'next').'</a>';
		}
		if ($startDayOfWeekTS > mktime() || $endDayOfWeekTS < mktime()) // if NOT this week
		{
			$gotoURL = $this->getUrl($GLOBALS['TSFE']->id, date('mdy', mktime()), $currentIF);
			$week_content .= ' | <a href="'.$gotoURL.'">'.$this->pi_getLL('cur_week', 'cur').'</a>';
		}
		$week_content .= '</div>';

		return $week_content;
	}

	/**
	*==================================================================================
	* DISPLAY MONTHLY CALENDAR
	*
	*   Show monthly calendar in popup
	*
	* @param integer $thisDateTS datetime_stamp
	* @return string button plus code to popup monthly calendar
	*==================================================================================
	*/
	function displayMonthlyCalendar($thisDateTS) {
		$gotoURL = $this->getUrl($GLOBALS['TSFE']->id, 0, $this->curSec);

		// include calendar popup code
		$GLOBALS['TSFE']->additionalHeaderData['tx_wecdevo'] .= '<script type="text/javascript" src="'.t3lib_extMgm::siteRelPath('wec_devo').'res/CalendarPopup.js"></script>';

		// add code to call calendar from Javascript
		$content .= '
			<script language="Javascript" type="text/javascript">
			var cal;
			window.onload = function() {
			 	cal = new CalendarPopup();
				if (cal)
					cal.setReturnFunction("calselect");
			}
			function calselect(y,m,d) {
				yStr = String(y); yStr = yStr.substr(2,2);
				mStr = String(m); if (m < 10) mStr = "0"+mStr;
				dStr = String(d); if (d < 10) dStr = "0"+dStr;
				var gotoURL = "'.t3lib_div::locationHeaderURL($gotoURL).'";
				if (gotoURL.indexOf(\'?\') > 0) { gotoURL += \'&\'; } else { gotoURL += \'?\'; }
				gotoURL += "tx_wecdevo[show_date]="+mStr+dStr+yStr;
				location.href = gotoURL;
			}			
			</script>
			';

		// create icon that when click on will show popup
		$content .= '<div class="tx-wecdevo-showMonth">';
		$content .= '<form name="popupcal" action="">';
		$calIcon = $this->cObj->fileResource($this->conf['monthCalIconFile']);
		if (!$calIcon) $calIcon = 'Calendar';
		$content .= '<input type="hidden" name="showmonthdate" value="" size="10" />
			<a href="#" onclick="cal.select(document.forms[\'popupcal\'].showmonthdate,\'anchor1\',\'MM/dd/yyyy\'); return false;"  name="anchor1" id="anchor1">'.$calIcon.'</a>';
		$content .= '</form>';
		$content .= '</div>';

		return $content;
	}

	/**
	*==================================================================================
	* DISPLAY TOPIC SELECTOR
	*
	*     Can choose a topic by dropdown menu. The weekly topic is read from database.
	*
	* @param integer $thisDateTS datetime_stamp
	* @return string code to display topics in dropdown menu
	*==================================================================================
	*/
	function displayTopicSelector($thisDateTS) {
		// Read in all topics
		//===================================================
//		$query = 'SELECT * FROM '.$this->devoTopicsTable.' WHERE pid IN ('.$this->pid_list.') AND deleted=0 ORDER BY num, show_date';
//		$res = $GLOBALS['TYPO3_DB']->sql(TYPO3_db, $query);
		$where = 'pid IN ('.$this->pid_list.') AND deleted=0';
		$orderBy = 'num, show_date';
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', $this->devoTopicsTable, $where, '', $orderBy, '');

		if (mysql_error()) t3lib_div::debug(array(mysql_error(), $query));
			$topic_count = $GLOBALS['TYPO3_DB']->sql_num_rows($res);

		if ($topic_count == 0) // if no topics, return nothing
		return "";

		$count = 0;
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$topicList[$count]['name'] = $row['topic'];
			$topicList[$count]['num'] = $row['num'];
			$topicList[$count]['show_date'] = $row['show_date'];
			$count++;
		}

		//
		// CREATE JAVASCRIPT  TO HANDLE GOING TO WEEKLY TOPIC
		//===================================================
		$gotoURL = $this->getUrl($GLOBALS['TSFE']->id, 0, $this->curSec);

		$wt_content .= '
			<script type="text/javascript">
			//<![CDATA[
			function gotoTopic(which) {
			var gotoURL = "'.t3lib_div::locationHeaderURL($gotoURL).'";
			if (gotoURL.indexOf(\'?\') > 0) { gotoURL += \'&\'; } else { gotoURL += \'?\'; }
			gotoURL += "tx_wecdevo[show_date]="+which.value;
			location.href = gotoURL;
			}
			//]]>
			</script>
			';

		// put header (TO DO: make this flexible what it is)
		$wt_content .= '<h4>'.$this->pi_getLL('archive_header', 'Archive:').'</h4>
			'.$this->pi_getLL('choose_topic', 'Choose topic:').' ';

		//
		// NOW BUILD THE OUTPUT
		//===================================================
		$wt_content .= '<select name="chooseTopic" class="tx-wecdevo-topic" onchange="gotoTopic(this);">';
		$this->curTopic = 0;
		$thisWeekNum = date('W', $thisDateTS+86400) - 1;

		for ($i = 0; $i < $count; $i++) {
			if ($topicList[$i]) {
				$firstOfYearDOW = date('w', mktime(0, 0, 0, 1, 1, date('y', $thisDateTS))) - 1;
				$timeStr = $topicList[$i]['show_date'] ? date('mdy', $topicList[$i]['show_date']) : date('mdy', mktime(0, 0, 0, 1, 7 * $topicList[$i]['num'] - $firstOfYearDOW, date('Y', $thisDateTS)));
				$wt_content .= '<option value="'.$timeStr.'"';
				$now = $thisDateTS ? $thisDateTS : mktime();
				$topicDate = $topicList[$i]['show_date'];
				// select it if today one falls after the previous one and before the next one

				if (($topicDate && ($now < $topicList[$i+1] || ($i == ($count-1))) && ($now >= $topicDate))
					|| (!$topicDate && ($thisWeekNum == $i))
				) {
					$wt_content .= ' SELECTED ';
					$this->curTopic = $topicList[$i]['name'];
				}
				$wt_content .= '>'.$topicList[$i]['name'].'</option>';
			}
		}
		$wt_content .= '</select>';

		return $wt_content;
	}

	/**
	*==================================================================================
	* DISPLAY USERS JOURNAL
	*
	*   shows a journal that you can edit and save. Each journal entry listed by day.
	*
	* @param string $datetime_stamp
	* @return string $j_content journal content
	*==================================================================================
	*/
	function displayJournal($thisDate) {
		$jsContent = '';
		if ($thisDate == "" || strlen($thisDate) < 6)
			return $jsContent;

		$entryStr = $this->loadJournal($thisDate);

		// show Save button (using RTE, it will have  Cut/Copy/Paste, etc. buttons)
		$jsButtonContent .= '<input name="Save" type="submit" value="'.$this->pi_getLL("journal_saveBtn","SAVE").'" class="tx-wecdevo-button" onclick="doSaveJournal();" onmouseover="this.className=\'tx-wecdevo-button tx-wecdevo-buttonHov\'" onmouseout="this.className=\'tx-wecdevo-button\'" />';

		// add Print Journal
		if ($this->config['allowPrintJournal'])
			$jsButtonContent .= '<input name="PrintJournal" type="button" value="'.$this->pi_getLL("journal_printBtn","Print").'"  class="tx-wecdevo-button" onclick="javascript:doPrintJournal();"  onmouseover="this.className=\'tx-wecdevo-button tx-wecdevo-buttonHov\'" onmouseout="this.className=\'tx-wecdevo-button\'" />';

		// create javascript to handle pushing the print or save buttons
		$jsButtonContent .= "
			<script language=\"JavaScript\">
			 //<![CDATA[
				var config;
				var useJournalRTE  = false;
				var showVerse = '".$this->curVerses."';

				function checkIfShouldSave(saveStr) {
					var test1Str, test2Str;
					if (useJournalRTE) {
						if (useJournalRTE == 3) {
							curJournalText = tinyMCE.activeEditor.getContent();
						}
						else {
							curJournalText = HTMLArea.getHTML(frames[0].document.body, false);
						}
						test1Str = prevJournalText.replace( /^\s+|\s+$/g, '' );// strip leading & trailing
						test2Str = curJournalText.replace( /^\s+|\s+$/g, '' );// strip leading & trailing
						test2Str = test2Str.replace(\"&amp;\",\"&\");
						test2Str = test2Str.replace(\"&gt;\",\">\");
						test2Str = test2Str.replace(\"&lt;\",\"<\");
						if ((test1Str == \"<br>\") || (test1Str == \"<br />\")) test1Str = \"\";
						test1Str = escape(test1Str);
						test1Str = test1Str.replace(/%0D%0A/g,\"%0A\");
						test1Str = unescape(test1Str)
					}
					else {
						test1Str = prevJournalText;
						test2Str = document.getElementsByName('tx_wecdevo[journal_entry]')[0].value;
					}
					if (test1Str != test2Str) {
						if (window.confirm(saveStr)) {
							doSaveJournal();
							return true;
						}
						return false;
					}
					return false;
				}

				function doprint(start,end) {
					windowLoc = window.location.href;
					if  ((st = windowLoc.indexOf(\"printstart\")) > 0)  // strip off params if already there
						windowLoc = windowLoc.substr(0,st-1);
					if (windowLoc.charAt(windowLoc.length-1) != '/') // add ending / if not there for realURL
						windowLoc += '/';
					if  ((st = windowLoc.indexOf('?')) > 0) // if already has a front param
						location.href=windowLoc+'&printstart='+start+'&printend='+end;
					else
						location.href=windowLoc+'?printstart='+start+'&printend='+end;
				}

				function doPrintJournal() {
					if (!checkIfShouldSave(\"".$this->pi_getLL('print_journal_saveBefore','Do you want to save before you print?')."\")) {
						printJournalMenu();
					}
				}

				function doSaveJournal() {
					document.journal_form.onsubmit(); // this forces HTMLArea to save to form's textarea value
					document.journal_form.submit();   // this forces the form to submit
				}

			//]]>
		   </script>
		";

		if ($extraBtnContent = $this->config['journal_extrabuttons']) {
			// replace any \\n with \n or any other double \\ characters
			$extraBtnContent = str_replace("\\\\","\\",$extraBtnContent);
//			$extraBtnContent = str_replace("'","\\'",$extraBtnContent);
			$jsButtonContent .= $extraBtnContent;
		}

		//Close FORM
		$jsEndForm = '</form>';

		// allow to specify the printjournal.php file or use one in extension
		if ($this->conf['printjournalfile'])
			$printJournalFile = $this->conf['printjournalfile'];
		else {
			$dn = dirname($_SERVER['PHP_SELF']);
			if (($dn != '/') && ($dn != '\\'))
				$printJournalFile = 'http://'.$_SERVER['HTTP_HOST'].$dn.'/';
			else
				$printJournalFile = 'http://'.$_SERVER['HTTP_HOST'].'/';
			$printJournalFile .= t3lib_extMgm::siteRelPath('wec_devo').'pi1/printjournal.php';
		}
		// allow to configure popup for printjournal
		if ($this->conf['printjournalfile_popup'])
			$printJournalFile_popup = $this->conf['printjournalfile_popup'];
		else
			$printJournalFile_popup = 'status=no, toolbar=no, location=no, width=350, height=300, left="+((screen.width - 350) /2)+", top="+((screen.height - 300) / 2)+" ';
		$jsJournalChanged = '
			<script type="text/javascript"  defer="1">
			//<![CDATA[
				var journalChanged = false;
				//var prevJournalText = document.journal_form.journal_entry.value;
				var prevJournalText = document.getElementsByName(\'tx_wecdevo[journal_entry]\')[0].value;

				function printJournalMenu() {
					printwin = window.open("'.$printJournalFile.'", "tjprintwin","'.$printJournalFile_popup.'");
					if (!printwin.opener) printwin.opener = self;
				}

				// this goes through all the interface elements and attaches an onclick check on them
				//
				var interfList = 0;
				var nextArrow = 0;
				if (document.getElementById)
				{
					interfList = document.getElementById("ddcolortabs").getElementsByTagName("a");
					if (document.getElementById("next_arrow"))
						nextArrow  = document.getElementById("next_arrow").getElementsByTagName("a");
				}

				if (interfList) 
				{
					for (var i=0; i < interfList.length; i++)
					{
						interfList[i].onclick = new Function (\'checkIfShouldSave("Do you want to save your journal first?")\');
					}
				}
				if (nextArrow) 
				{
					nextArrow[0].onclick = new Function (\'checkIfShouldSave("Do you want to save your journal first?")\');
				}
				
				var appVer = navigator.appVersion.toLowerCase();
				var iePos  = appVer.indexOf(\'msie\');
				if (iePos == -1) // only do this for non-IE browsers
				{
					if (prevJournalText.length == 0) // if nothing then do default HTMLAREA entry
						prevJournalText = "<br />\n";
					else
						prevJournalText = "\n"+prevJournalText;	// add a break because HTMLarea adds one
				}				
		 	//]]>
			</script>
		';
		
		// Now handle if other RTE is used...
		// Add Front-End htmlArea editing
		if(is_object($this->RTEObj) && $this->RTEObj->isAvailable()) {
			$this->RTEcounter++;
			$this->table = 'tx_wecdevo';
			$this->field = 'journal_entry';
			$this->formName = 'journal_form';
			$this->PA['itemFormElName'] = 'tx_wecdevo[journal_entry]';
			$this->PA['itemFormElValue'] = $this->html_entity_decode($entryStr);
			$this->thePidValue = $GLOBALS['TSFE']->id;
			$this->RTEObj->RTEdivStyle  = $this->conf['RTEfontSize'] ? 'font-size:'.$this->conf['RTEfontSize'].';' : '';
			$this->RTEObj->RTEdivStyle .= $this->conf['RTEheight']   ? 'height:'.$this->conf['RTEheight'].';'      : '';
			$this->RTEObj->RTEdivStyle .= $this->conf['RTEwidth']    ? 'width:'.$this->conf['RTEwidth'].';'        : '';
			$RTEitem = $this->RTEObj->drawRTE($this, $this->table, $this->field, $row=array(), $this->PA, $this->specConf, $this->thisConfig, $this->RTEtypeVal, '', $this->thePidValue);

			// PRE FORM
			$jsContent = $this->additionalJS_initial.'
				<script type="text/javascript">'. implode(chr(10), $this->additionalJS_pre).'
					</script>';
			$jsContent .= '<form name="journal_form" method="post" action="'.$this->pi_getPageLink($GLOBALS['TSFE']->id,"").'" onsubmit="'.implode(';', $this->additionalJS_submit).'">';
			$jsContent .= '
					<input type="hidden" name="no_cache" value="1" />
					<input type="hidden" name="tx_wecdevo[saveJournal]" value="1" />
					<input type="hidden" name="tx_wecdevo[journal_date]" value="'.$thisDate.'" />
					<input type="hidden" name="tx_wecdevo[show_date]" value="'.$this->curDay.'" />
					<input type="hidden" name="tx_wecdevo[section]" value="'.$this->curSec.'" />
			';
			// MAIN TEXTAREA
			$jsContent .= $RTEitem;
			$jsEndForm = '</form>';
			
			// POST FORM
			$jsRTEContent = '
				<script type="text/javascript">'. implode(chr(10), $this->additionalJS_post).'
					useJournalRTE = false;
					if (typeof(HTMLArea) !== "undefined")
						useJournalRTE = 2;
					else if (typeof(tinyMCE) !== "undefined")
						useJournalRTE = 3;
					</script>';
		}
		// use BG_HTMLAREA
		else {
			if ($this->userFirstName) {
				$jsContent .= '<div class="tx-wecdevo-journalName">'.$this->userFirstName.'\'s '.$this->pi_getLL('journal_title','Journal').'</div>';
			}
			else {
				$jsContent .= '<div class="tx-wecdevo-journalName">'.$this->pi_getLL('your_journal','Your Journal').'</div>';
			}

			//-----------------------------------------------------------------------------------------
			//	Show Journal in Form
			//-----------------------------------------------------------------------------------------
			$jsContent .= '
				<form name="journal_form" method="post" action="'.$this->pi_getPageLink($GLOBALS['TSFE']->id,"").'">
					<input type="hidden" name="no_cache" value="1" />
					<input type="hidden" name="tx_wecdevo[saveJournal]" value="1" />
					<input type="hidden" name="tx_wecdevo[journal_date]" value="'.$thisDate.'" />
					<input type="hidden" name="tx_wecdevo[show_date]" value="'.$this->curDay.'" />
					<input type="hidden" name="tx_wecdevo[section]" value="'.$this->curSec.'" />
			';

			// show Editor with any previous user data
			$jsContent .= '<textarea name="tx_wecdevo[journal_entry]" id="journal_entry" cols="35" rows="14" wrap="VIRTUAL" style="font-size:11px;width:275px;height:330px;">'.$entryStr.'</textarea>';
			
			// JavaScript to convert TextArea to BG_HTMLAREA RTE
			$jsRTEContent  =
			'<script type="text/javascript"  defer="1">
			//<![CDATA[
				agent = navigator.userAgent.toLowerCase();
				isMac = agent.indexOf("mac") != -1;
				isFF  = agent.indexOf("firefox") != -1;
				isOpera = agent.indexOf("opera") != -1;
				// if its a mac with firefox or it is NOT opera, then use RTE
				useJournalRTE = !isOpera && (!isMac || (isMac && isFF));
				if (useJournalRTE &&  (typeof(HTMLArea) === "undefined")) {
					useJournalRTE = false;
				}	
				if (useJournalRTE) {
					config  = new HTMLArea.Config();
				}
			';
			if ($this->conf['rte_code']) {
				$jsRTEContent .= $this->conf['rte_code'];
			}
			else {
				$jsRTEContent .= '
				if (config) {
					config.toolbar = [
						["bold", "italic",  "underline",  "separator",
						"fontname", "separator", "fontsize",
						"linebreak",
						"forecolor", "separator", "hilitecolor",  "separator",
						"copy", "cut", "paste", "separator",
						"undo", "redo", "separator",
						"insertorderedlist", "insertunorderedlist"
						]
					];

					config.fontname = {
						"Arial":	   "arial,helvetica,sans-serif",
						"Courier New": "courier new,courier,monospace",
						"Georgia":	   "georgia,times new roman,times,serif",
						"Tahoma":	   "tahoma,arial,helvetica,sans-serif",
						"Times New Roman": "times new roman,times,serif",
						"Verdana":	   "verdana,arial,helvetica,sans-serif",
						"Comic Sans":  "comic sans MS,helvetica,sans-serif"
					};

					config.pageStyle = \'body { background-color: '.$this->conf["RTEbackColor"].'; color: black; font-family: arial, verdana, sans-serif; font-size: 9pt; margin:4px; } \' +
			  				\'p { font-size:'.$this->conf["RTEfontSize"].'; margin:0px; } \' + \'ul { list-style-position: outside; padding-left:10px; margin-left:10px; margin-top:4px;margin-bottom:4px;} \' + \'ol { list-style-position: outside; padding-left:12px; margin-left:12px;margin-top:4px;margin-bottom:4px;} \';
				}
				';
			}
			$jsRTEContent .= '
				if (config) {
					config.width  = \''.$this->conf["RTEwidth"].'\';
					config.height = \''.$this->conf["RTEheight"].'\';
					config.statusBar = 0;

					if (useJournalRTE)
						HTMLArea.replace("journal_entry", config);
				}

			 //]]>
			</script>';
		}

		// Build the Journal Content
		$journalContent = $jsContent . $jsButtonContent . $jsEndForm . $jsRTEContent . $jsJournalChanged;
		
		return $journalContent;
	}

	/**
	* =====================================================================================
	* saveJournal
	*
	* Save the user journal data for that date
	*
	* save $theText to database with USERID + DATE
	* if exists, then overwrite otherwise create a new one
	*
	* @param string $thisDate
	* @param string $theText
	* @return void
	* =====================================================================================
	*/
	function saveJournal($thisDate, $theText) {
		$saveDate  = date('Y-m-d',mktime(12,0,0, substr($thisDate,0,2),substr($thisDate,2,2),substr($thisDate,4,2)));

		// see if any entries exist for given date
		// then either INSERT a new one or UPDATE existing one
//		$query = 'SELECT * FROM '.$this->devoJournalTable." WHERE post_date='".$saveDate."' AND owner_userid=".intval($this->userID).' AND pid IN('.$this->pid_list.') AND deleted=0';
//		$res = $GLOBALS['TYPO3_DB']->sql(TYPO3_db, $query);
		$where = "post_date='".$saveDate."' AND owner_userid=".intval($this->userID).' AND pid IN('.$this->pid_list.') AND deleted=0';
		$orderBy = '';
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', $this->devoJournalTable, $where, '', $orderBy, '');
		
		if (mysql_error()) t3lib_div::debug(array(mysql_error(),$query));

		$theText = htmlspecialchars($theText);
		$theText = $this->simpleEncrypt($theText, "JesusLovesYou");
		$count = $GLOBALS['TYPO3_DB']->sql_num_rows($res);
		$pidlist = t3lib_div::trimExplode(',',$this->pid_list);
		$saveToPID = $pidlist[0]; 
		if ($count <= 0) { // no entry exists so INSERT...
			$query = 'INSERT INTO '.$this->devoJournalTable." SET post_date='".$saveDate."', owner_userid=".$this->userID.", entry='".addslashes($theText)."', pid=".$saveToPID.', crdate='.mktime().', tstamp='.mktime();
		}
		else { // replace existing entry with new data...
			$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
			$query = 'UPDATE '.$this->devoJournalTable." SET entry='".addslashes($theText)."', tstamp=".mktime().' WHERE uid='.$row['uid'];
		}
		// then either ADD/INSERT or UPDATE
		$res = $GLOBALS['TYPO3_DB']->sql(TYPO3_db, $query);
		if (mysql_error())	t3lib_div::debug(array(mysql_error(),$query));
	}

	/**
	* =====================================================================================
	* loadJournal
	*
	* Load in user journal data for that date
	*
	* @param string $theDate
	* @return string $entryStr return the given journal entry
	* =====================================================================================
	*/
	function loadJournal($theDate) {
		$entryStr = '';

		$loadDate = date('Y-m-d', mktime(12, 0, 0, substr($theDate, 0, 2), substr($theDate, 2, 2), substr($theDate, 4, 2)));
		$pidlist = t3lib_div::trimExplode(',',$this->pid_list);
		$saveToPID = $pidlist[0];		
//		$query = 'SELECT * FROM '.$this->devoJournalTable." WHERE post_date='".$loadDate."' AND owner_userid=".intval($this->userID).' AND pid='.$saveToPID.' AND deleted=0 ';
//		$res = $GLOBALS['TYPO3_DB']->sql(TYPO3_db, $query);
		$where = "post_date='".$loadDate."' AND owner_userid=".intval($this->userID).' AND pid='.$saveToPID.' AND deleted=0';
		$orderBy = '';
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', $this->devoJournalTable, $where, '', $orderBy, '');
		if (mysql_error()) t3lib_div::debug(array(mysql_error(), $query));

		$count = $GLOBALS['TYPO3_DB']->sql_num_rows($res);

		if ($count > 0) { // if something in database then load it into input string
			while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) { // there should only be one row
				if ($row['entry']) {
					$entryStr = $row['entry'];
					$entryStr = $this->simpleDecrypt($entryStr, "JesusLovesYou");
				}
			}
		}

		return $entryStr;
	}

	/**
	*==================================================================================
	* simpleEncrypt: Encrypt using simple algorithm
	*
	*   This encryption is used on the journal to obscfucate the text that is saved
	*  in the database. We did not want to use mcrypt here because it is not always
	*   installed on all systems.
	*
	* @param string $data data to encrypt
	* @param string $passkey password key to encrypt with
	* @return string encrypted string
	*==================================================================================
	*/
	function simpleEncrypt($data, $passkey) {
		$encodeData = base64_encode($data);
		$eStr = "";
		for ($i = 0, $j = 0; $i < strlen($encodeData); $i++, $j++) {
			$md = ord(substr($encodeData, $i, 1)) + ord(substr($passkey, $j, 1));
			if ($j > strlen($passkey)) $j = 0;
			$eStr .= chr($md);
		}
		return $eStr;
	}

	/**
	*==================================================================================
	* simpleDecrypt: Decrypt using simple algorithm
	*
	*   This encryption is used on the journal to obscfucate the text that is saved
	*  in the database. We did not want to use mcrypt here because it is not always
	*   installed on all systems.
	*
	* @param string $decodeData data to encrypt
	* @param string $passkey password key to encrypt with
	* @return string decrypted string
	*==================================================================================
	*/
	function simpleDecrypt($decodeData, $passkey) {
		$decStr = "";
		for ($i = 0, $j = 0; $i < strlen($decodeData); $i++, $j++) {
			$md = ord(substr($decodeData, $i, 1)) - ord(substr($passkey, $j, 1));
			if ($j > strlen($passkey)) $j = 0;
			$decStr .= chr($md);
		}
		$decodeData = base64_decode($decStr);

		return $decodeData;
	}

	/**
	* =====================================================================================
	* printJournal
	*
	* Print user journal data for given date(s)
	*
	* @param string $startdate starting date for print journal
	* @param string $enddate ending date for print journal
	* @return string $printStr string for printed journal results
	* =====================================================================================
	*/
	function printJournal($startdate, $enddate) { // dates in MMDDYYYY format
		if ($startdate > 0 && $enddate > 0) {
			$startPostDate = date('Y-m-d', mktime(1, 0, 0, substr($startdate, 0, 2), substr($startdate, 2, 2), substr($startdate, 4, 4)));
			$endPostDate = date('Y-m-d', mktime(23, 59, 0, substr($enddate, 0, 2), substr($enddate, 2, 2), substr($enddate, 4, 4)));
//			$query = 'SELECT * FROM '.$this->devoJournalTable." WHERE post_date>='".$startPostDate."' AND post_date<='".$endPostDate."' AND owner_userid=".intval($this->userID).' AND pid IN('.$this->pid_list.') AND deleted=0 ORDER BY post_date';
//			$res = $GLOBALS['TYPO3_DB']->sql(TYPO3_db, $query);
			$where = "post_date>='".$startPostDate."' AND post_date<='".$endPostDate."' AND owner_userid=".intval($this->userID).' AND pid IN('.$this->pid_list.') AND deleted=0';
			$orderBy = 'post_date';
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', $this->devoJournalTable, $where, '', $orderBy, '');
			if (mysql_error()) t3lib_div::debug(array(mysql_error(), $query));
				$count = $GLOBALS['TYPO3_DB']->sql_num_rows($res);
			$printStr = '
				<html>
				<header>

				</header>
				<body>
				';
			$printStr .= '<a href="#" onclick="window.print();">Print</a> | <a href="javascript:history.go(-1)">Back</a>';

			if ($count > 0) {
				$printStr .= "<div width='650px'>";
				$printStr .= '<h1>'.$this->userFirstName."'s ".$this->pi_getLL('printJournal_title', 'Transformation Journal Entries').'</h1>';
				$printStr .= "<hr width=\"100%\">";
				while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
					// add heading...
					$dt = $row['post_date']; // format = YYYY-MM-DD
					$printStr .= '<b>'.date('M d, Y', mktime(0, 0, 0, substr($dt, 5, 2), substr($dt, 8, 2), substr($dt, 0, 4))).'</b>'.'<br>';

					// add data...
					$printStr .= $this->simpleDecrypt($row['entry'], "JesusLovesYou");

					// add separator
					$printStr .= "<hr width=\"100%\">";
				}
				$printStr .= '</div>';

				// convert from HTML encoded to normal HTML so looks good
				$printStr = $this->html_entity_decode($printStr,ENT_COMPAT,$GLOBALS['TSFE']->renderCharset);
			} else {
				$printStr .= '<br>'.$this->pi_getLL('printJournal_noentries', 'There are no entries to print');
			}

			$printStr .= '</body></html>';
			print($printStr);
		}
	}

	/**
	* =====================================================================================
	* ADD COMMENTS
	*
	* Add a comments to the page. This will load whatever page is in the display_comments_page Flexform
	*
	* @return string $commentsContent show the comments on a page
	* =====================================================================================
	*/
	 function addComments()  {
		$commentsPID = trim($this->config['comments_pid']);
		if (!$commentsPID) return "";

		// load page id based on page#
		$query = array(
				'table' => 'tt_content',
				'select.' => array(
				'pidInList' => $commentsPID,
				'orderBy' => 'sorting',
				'where' => 'colPos = 0 AND CType != "'.$extKey.'_pi1 "'.$this->cObj->enableFields('tt_content'),   // Avoid any loop
				'languageField' => 'sys_language_uid'
				)
		);
		$commentsContent = $GLOBALS['TSFE']->cObj->CONTENT($query);

		return $commentsContent;
	  }


	/**
	* =====================================================================================
	* addDiscussionPreview
	*
	* Add a discussion preview to the page. This will load whatever page is in the
	* discussion_preview_page Flexform
	*
	* @return string $previewContent return the content for adding a discussion preview
	* =====================================================================================
	*/
	 function addDiscussionPreview() {
		$previewPID = trim($this->config['preview_pid']);
		if (!$previewPID) return "";

		// load page id based on page#
		$query = array(
				'table' => 'tt_content',
				'select.' => array(
				'pidInList' => $previewPID,
				'orderBy' => 'sorting',
				'where' => 'colPos = 0 AND CType != "'.$extKey.'_pi1 "'.$this->cObj->enableFields('tt_content'),   // Avoid any loop
				'languageField' => 'sys_language_uid'
				)
		);
		$previewContent = $GLOBALS['TSFE']->cObj->CONTENT($query);

		return $previewContent;
	  }

	/**
	*==================================================================================
	*  Display RSS Feed
	*
	* 	@return string RSS feed content
	*==================================================================================
	*/
	function displayRSSFeed() {
		$rss_content = "";

		$rssTemplateFile = $this->conf['rssTemplateFile'];
		if (!$rssTemplateFile)
			return $this->pi_getLL('no_rss_template_file',"No RSS Template File configured.");

		// current page where forum is
		$gotoPageID = $this->id;

		// this is set for enabling relative URLs for images and links in the RSS feed.
		$sourceURL = $this->conf['xml.']['rss.']['source_url'] ?  $this->conf['xml.']['rss.']['source_url'] : t3lib_div::getIndpEnv('TYPO3_SITE_URL');

		// load in template
		$rssTemplateCode = $this->cObj->fileResource($rssTemplateFile);
		$rssTemplate = $this->cObj->getSubpart($rssTemplateCode, '###TEMPLATE_RSS2###');

		// fill in template
		$dataArray = array('CHANNEL_TITLE','CHANNEL_LINK','CHANNEL_DESCRIPTION','LANGUAGE','NAMESPACE_ENTRIES','COPYRIGHT','DOCS','CHANNEL_CATEGORY','MANAGING_EDITOR','WEBMASTER','CHANNEL_IMAGE','TTL');
		for ($i = 0; $i < count($dataArray); $i++) {
			$rssField = $dataArray[$i];
			$linkField = strtolower(str_replace('CHANNEL_','',$rssField));
			if ($val = $this->conf['xml.']['rss.'][strtolower($rssField)]) {
				$markerArray['###'.strtoupper($rssField).'###'] = '<'.$linkField.'>'.$val.'</'.$linkField.'>';
			}
		}
		$charset = ($GLOBALS['TSFE']->metaCharset?$GLOBALS['TSFE']->metaCharset:'iso-8859-1');
		$markerArray['###XML_CHARSET###'] = ' encoding="'.$charset.'"';

		// fill in defaults...if not set
		$markerArray['###GENERATOR###'] = $this->conf['xml.']['rss.']['generator'] ? $this->conf['xml.']['rss.']['generator'] : 'TYPO3 v4 CMS';
		$markerArray['###XMLNS###'] = $this->conf['xml.']['rss.']['xmlns'];
		$markerArray['###XMLBASE###'] = 'xml:base="'.$sourceURL.'"';
		$markerArray['###GEN_DATE###'] = date('D, d M Y h:i:s T');
		if (!$markerArray['###CHANNEL_TITLE###'])
			$markerArray['###CHANNEL_TITLE###'] = '<title>'. ($this->config['title'] ? $this->config['title'] : $this->conf['title']) .'</title>';
		if (!$markerArray['###CHANNEL_LINK###'])
			$markerArray['###CHANNEL_LINK###'] = '<link>'.$sourceURL.'</link>';
		$markerArray['###CHANNEL_GENERATOR###'] = '<generator>'.$markerArray['###GENERATOR###'].'</generator>';

		// grab item template
		$itemTemplate = $this->cObj->getSubpart($rssTemplateCode, '###ITEM###');

		// Grab entries
		//----------------------------------------------------------------------
		// Will grab from all specified sections and then sort them by date...
		//
		$pidList = t3lib_div::_GP('sp') ? t3lib_div::_GP('sp') : $this->pid_list;
		$order_by = 'starttime DESC';
		$where .= ' pid IN (' . $pidList . ')';
		$where .= $this->cObj->enableFields($this->devoContentTable);
		// handle languages
		$lang = ($l = $GLOBALS['TSFE']->sys_language_uid) ? $l : '0,-1';
		$limit = $this->config['num_previewRSS_items'] ? $this->config['num_previewRSS_items'] : 5;
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', $this->devoContentTable, $where, '', $order_by, $limit);
		if (mysql_error()) 
			t3lib_div::debug(array(mysql_error(), 'SELECT * FROM '.$this->devoContentTable.' WHERE '.$where.' ORDER BY '.$order_by.' LIMIT '.$limit));

		// Grab Section Names
		//-----------------------------------------------------------------------------
		$where = 'pid IN('.$pidList.') AND deleted=0 AND hidden=0';
		$orderBy = 'position';
		$res2 = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', $this->devoSectionTable, $where, '', $orderBy, '');
		while ($row_section = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res2)) {
			$sectionList[$row_section['uid']] = $row_section['name'];
		}
		
		// fill in item
		$item_content = "";
		$mostRecentMsgDate = 0;
	
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			// Generate Title From Section + Date
			$titleStr = $sectionList[$row['section']] . $this->pi_getLL('rssfeed_for', ' for ') . date('M d, Y',$row['starttime']);
			$itemMarker['###ITEM_TITLE###'] = '<title>'.$titleStr.'</title>';
			$urlParams = array();
			$hashParams = '';
			if ($this->config['allow_single_view'] != 0)
				$urlParams['tx_wecdiscussion']['single'] = $row['uid'];
			else
				$urlParams['tx_wecdiscussion']['showreply'] = $row['uid'];
			$itemMarker['###ITEM_LINK###'] = '<link>' . htmlspecialchars($this->getAbsoluteURL($gotoPageID,$urlParams,TRUE)) . '</link>';

			$msgText = $row['content'];
			if (is_array($this->conf['general_stdWrap.'])) {
				$msgText = str_replace('&nbsp;',' ',$msgText);
				$msgText = $this->cObj->stdWrap($this->html_entity_decode($msgText,ENT_QUOTES), $this->conf['general_stdWrap.']);
			}
			// if absRefPrefix set, then do transform so adds (because bug in TYPO3 and USER_INT)
			if (($absRefPrefix = $GLOBALS['TSFE']->config['config']['absRefPrefix']) && ($RTEImageStorageDir = $GLOBALS['TYPO3_CONF_VARS']['BE']['RTE_imageStorageDir'])) {
				$msgText = str_replace('"' .$RTEImageStorageDir, '"' . $absRefPrefix . $RTEImageStorageDir, $msgText);
			}
			// fill in item markers			
			$itemMarker['###ITEM_DESCRIPTION###'] = '<description>' . htmlspecialchars(stripslashes($msgText)) . '</description>';
			if ($row['email'])
				$itemMarker['###ITEM_AUTHOR###'] = '<author>' . htmlspecialchars(stripslashes($row['email'])) . ' ('.htmlspecialchars(stripslashes($row['name'])).')</author>';
			if (!empty($row['category']) && $this->categoryListByUID[$row['category']])
				$itemMarker['###ITEM_CATEGORY###'] = '<category>' . $this->categoryListByUID[$row['category']] . '</category>';
			$itemMarker['###ITEM_COMMENTS###'] = '';
			$itemMarker['###ITEM_ENCLOSURE###'] = '';
			$itemMarker['###ITEM_PUBDATE###'] = '<pubDate>' . date('D, d M Y H:i:s O',$row['starttime']) . '</pubDate>';
			$itemMarker['###ITEM_GUID###'] = '<guid isPermaLink="true">' . $this->getAbsoluteURL($gotoPageID,$urlParams, TRUE) . '</guid>';
			$itemMarker['###ITEM_SOURCE###'] = '<source url="' . $sourceURL . '">' . htmlspecialchars($row['subject']) . '</source>';
			if ($mostRecentMsgDate < $row['starttime'])
				$mostRecentMsgDate = $row['starttime'];

			// generate item info
			$item_content .= $this->cObj->substituteMarkerArrayCached($itemTemplate,$itemMarker,array(),array());
		}
		$subpartArray['###ITEM###'] = $item_content;
		if ($mostRecentMsgDate)
			$markerArray['###LAST_BUILD_DATE###'] = '<pubDate>' . date('D, d M Y H:i:s O', $mostRecentMsgDate) . '</pubDate>';

		// then substitute all the markers in the template into appropriate places
		$rss_content = $this->cObj->substituteMarkerArrayCached($rssTemplate,$markerArray,$subpartArray, array());

		// clear out any empty template fields (so if ###CONTENT1### is not substituted, will not display)
		$rss_content = preg_replace('/###.*?###/', '', $rss_content);

		// remove blank lines
		$rss_content = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $rss_content);

		// make certain tags XHTML compliant
		$rss_content = preg_replace("/<(img|hr|br|input)([^>]*)>/mi", "<$1$2 />", $rss_content);

		return $rss_content;
	}

	/**
	*==================================================================================
	*  Add RSS feed icon / text so can subscribe
	*
	*	@param  array  $marker array of markers (so can add to)
	* 	@return marker array
	*==================================================================================
*/
	function addSubscribeRSSFeed($marker) {
		if ($this->conf['rssFeedOn']) {
			$rssLink = $this->conf['xml.']['rss.']['link'];
			if (strpos($this->conf['xml.']['rss.']['link'],'http:') === FALSE) {
				if (strpos($rssLink,'+') === 0) {
					$curURL = $this->getAbsoluteURL($this->id,'',TRUE);
					$rssLink = strpos($curURL,'?') ? str_replace('+','&',$rssLink) : str_replace('+','?',$rssLink);
					$rssLink = $curURL . $rssLink;
				}
				$rssLink .= (strpos($rssLink,'?') ? '&' : '?') . 'sp=' . $this->pid_list;
			}

			$rssIcon = $this->conf['xml.']['rss.']['icon'];
			$rssImgConf['file'] = $rssIcon;
			$rssText = $this->pi_getLL('subscribe_to_feed','Subscribe to feed');

			$rssImgURLStr = $this->cObj->Image($rssImgConf); // run through imageMagick
			$marker['###RSSFEED_ICON1###'] = '<a href="'.$rssLink.'">'.$rssImgURLStr.'</a>';
			$marker['###RSSFEED_ICON2###'] = '<a href="'.$rssLink.'" style="text-decoration:none;">'.$rssImgURLStr.'</a>';
			$marker['###RSSFEED_TEXT###'] = '<a href="'.$rssLink.'" style="text-decoration:none;">'.$rssText.'</a>';
		}
		return $marker;
	}

	/**
	* Getting the URL to the given ID with all needed params
	* This will add extras, if they are set or allow to set "special" ones
	*
	* @param integer  $id: Page ID
	* @param string  $theDate: (MonthDayYear = 010205)
	* @param integer  $theSec: devo section
	* @param array  $extraParameters any extra parameters
	* @return string  $url: URL
	*/
	function getUrl($id, $theDate = 0, $theSec = 1, $extraParameters = '') {
		if ($theDate)
			$extraParameters['tx_wecdevo']['show_date'] = $theDate;
		$extraParameters['tx_wecdevo']['section'] = $theSec;
		if ($this->postvars['txtsz'] && !$extraParameters['tx_wecdevo']['txtsz'])
			$extraParameters['tx_wecdevo']['txtsz'] = $this->curTextSize;
		if ($this->postvars['txtpg'] && !$extraParameters['tx_wecdevo']['txtpg'])
			$extraParameters['tx_wecdevo']['txtpg'] = $this->curTextPaging;

		// now get the url
		$url = $this->pi_getPageLink($id, '', $extraParameters);
		$url = str_replace('&','&amp;', $url);
		return $url;
	}

	/**
	* Getting the full URL (ie. http://www.host.com/... to the given ID with all needed params
	* This function handles cross-site (on same server) links
	*
	* @param integer  $id: Page ID
	* @param string   $urlParameters: array of parameters to include in the url (i.e., "$urlParameters['action'] = 4" would append "&action=4")
	* @param boolean  $forceFullURL: if should create a full URL or just a relative one (http://www.site.com/test/... vs. /test/)
	* @return string  $url: URL
	*/
	function getAbsoluteURL($id, $extraParameters = '', $forceFullURL = FALSE) {
		// get the page url from TYPO system (realURL or simulated or not)
		$pageURL = $this->pi_getPageLink($id, '', $extraParameters);

		// if did not cross page boundaries, then generate url from info
		if ((strpos($pageURL,"http") === FALSE) || $forceFullURL) {
			// use the baseURL if given
			if ($GLOBALS["TSFE"]->config['config']['baseURL']) {
				$hostURL = $GLOBALS["TSFE"]->config['config']['baseURL'];
			}
			else if ($GLOBALS["TSFE"]->config['config']['absRefPrefix']) {
				$hostURL = $GLOBALS["TSFE"]->config['config']['absRefPrefix'];
			}
			// otherwise generate URL from PHP var
			else {
				$hostURL = (t3lib_div::getIndpEnv('TYPO3_SSL') ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/';
			}
			// build URL from host + page if not already has a full URL
			if (strpos($pageURL,"http") === FALSE) {
				$absURL =  $hostURL . $pageURL;
			}
			else { 
				$absURL = $pageURL;
			}
		}
		// crosses boundaries (likely different url on same server)
		else {
			$absURL = $pageURL;
		}

		
		//convert any ampersands
		$absURL = str_replace('&','&amp;', $absURL);
		return $absURL;
	}

	function html_entity_decode($str,$quoteStyle=ENT_COMPAT) {
		if (( version_compare( phpversion(), '5.0' ) < 0 ) && (strtolower($GLOBALS['TSFE']->renderCharset) == 'utf-8')) {
			$trans_tbl = get_html_translation_table (HTML_ENTITIES);
			$trans_tbl = array_flip ($trans_tbl);
			$source = strtr ($str, $trans_tbl);
			$source = utf8_encode($source);
		}
		else if (version_compare(phpversion(),"4.3.0") < 0) {
		    // replace numeric entities
		    $source = preg_replace('~&#x([0-9a-f]+);~ei', 'chr(hexdec("\\1"))', $str);
		    $source = preg_replace('~&#([0-9]+);~e', 'chr("\\1")', $source);
		    // replace literal entities
		    $trans_tbl = get_html_translation_table(HTML_ENTITIES);
		    $trans_tbl = array_flip($trans_tbl);
		    $source = strtr($str, $trans_tbl);
		}
		else {
			$source = html_entity_decode($str,$quoteStyle,$GLOBALS['TSFE']->renderCharset);
		}

		return $source;
	}

	/**
	* Get the WEC Cookie
	*
	* @param integer  $pid: Page ID to load
	* @return array  $data: data found in the cookie for the pid
	*/
	function getWECcookie($pid) {
		$data = 0;
		if (isset($_COOKIE['WEC'])) {
			$cData = t3lib_div::trimExplode('|', $_COOKIE['WEC']);
			for ($i = 0; $i < count($cData); $i++) {
				$thisCookie = t3lib_div::trimExplode('_', $cData[$i]);
				if ((count($thisCookie) > 1) && ($thisCookie[0] == $pid)) {
					$data = $thisCookie;
					break;
				}
			}
		}
		return $data;
	}

	/**
	* Set the WEC Cookie
	*
	* @param integer  $pid: Page ID to save
	* @param array  $data: array of data to store for this page
	* @return void
	*/
	function setWECcookie($pid, $data) {
		// get current cookie, then append the data
		$cookieFound = 0;
		$cookieCount = 0;
		$newCookie = '';
		if (isset($_COOKIE['WEC'])) {
			// parse out all the cookies
			$cData = t3lib_div::trimExplode('|', $_COOKIE['WEC']);
			$cookieCount = count($cData);
			for ($i = 0; $i < $cookieCount; $i++) {
				$curCookies[$i] = t3lib_div::trimExplode('_', $cData[$i]);
				if ((count($curCookies[$i]) > 1) && ($curCookies[$i][0] == $pid)) {
					// fill in new data
					for ($j = 0; $j < count($data); $j++)
					$curCookies[$i][$j+1] = $data[$j];
					$cookieFound = 1;
					break;
				}
			}
		}
		if (!$cookieFound) {
			$curCookies[$cookieCount][0] = $pid;
			for ($i = 0; $i < count($data); $i++)
			$curCookies[$cookieCount][$i+1] = $data[$i];
		}

		// build the new cookie
		for ($i = 0; $i < count($curCookies); $i++) {
			for ($j = 0; $j < count($curCookies[$i]); $j++) {
				$newCookie .= ($j != (count($curCookies[$i]) -1)) ? $curCookies[$i][$j]."_" :
				 $curCookies[$i][$j];
			}
			if ($i < (count($curCookies)-1)) $newCookie .= '|';
		}

		setcookie('WEC', $newCookie, time()+(60 * 60 * 24 * 365), '/');
	}

	/**
	 * getConfigVal: Return the value from either plugin flexform, typoscript, or default value, in that order
	 *
	 * @param	object		$Obj: Parent object calling this function
	 * @param	string		$ffField: Field name of the flexform value
	 * @param	string		$ffSheet: Sheet name where flexform value is located
	 * @param	string		$TSfieldname: Property name of typoscript value
	 * @param	array		$lConf: TypoScript configuration array from local scope
	 * @param	mixed		$default: The default value to assign if no other values are assigned from TypoScript or Plugin Flexform
	 * @return	mixed		Configuration value found in any config, or default
	 */
	function getConfigVal( &$Obj, $ffField, $ffSheet, $TSfieldname='', $lConf='', $default = '' ) {
		if (!$lConf && $Obj->conf) $lConf = $Obj->conf;
		if (!$TSfieldname) $TSfieldname = $ffField;

		//	Retrieve values stored in flexform and typoscript
		$ffValue = $Obj->pi_getFFvalue($Obj->cObj->data['pi_flexform'], $ffField, $ffSheet);
		$tsValue = $lConf[$TSfieldname];

		//	Use flexform value if present, otherwise typoscript value
		$retVal = $ffValue ? $ffValue : $tsValue;

			//	Return value if found, otherwise default
		return $retVal ? $retVal : $default;
	}	
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wec_devo/pi/class.tx_wecdevo_pi1.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wec_devo/pi/class.tx_wecdevo_pi1.php']);
}

?>
