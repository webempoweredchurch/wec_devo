<?php
if (!defined ("TYPO3_MODE")) 	die ("Access denied.");

  ## Extending TypoScript from static template uid=43 to set up userdefined tag:
t3lib_extMgm::addTypoScript($_EXTKEY,"editorcfg","
	tt_content.CSS_editor.ch.tx_wecdevo_pi1 = < plugin.tx_wecdevo_pi1.CSS_editor
",43);

t3lib_extMgm::addPItoST43($_EXTKEY,"pi1/class.tx_wecdevo_pi1.php","_pi1","list_type",1);

t3lib_extMgm::addUserTSConfig('	options.saveDocNew.tx_wecdevo_content=1');

// Add default Page TSonfig RTE configuration for clearing Scripture, Prayer, and Content fields
t3lib_extMgm::addPageTSConfig('<INCLUDE_TYPOSCRIPT: source="FILE:EXT:' . $_EXTKEY . '/res/pageTSConfig.txt">');
?>