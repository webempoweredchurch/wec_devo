#
# Table structure for table 'tx_wecdevo_content'
#
CREATE TABLE tx_wecdevo_content (
	uid int(11) unsigned DEFAULT '0' NOT NULL auto_increment,
	pid int(11) unsigned DEFAULT '0' NOT NULL,
	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	starttime int(11) unsigned DEFAULT '0' NOT NULL,
	endtime int(11) unsigned DEFAULT '0' NOT NULL,
	type int(11) DEFAULT '0' NOT NULL,
	section int(11) DEFAULT '0' NOT NULL,
	content text NOT NULL,
	scripture_ref tinytext NOT NULL,
	scripture text NOT NULL,
	questions text NOT NULL,
	prayer text NOT NULL,
	application text NOT NULL,
	end_content text NOT NULL,
	audio_file tinytext NOT NULL,
	video_file tinytext NOT NULL,

	PRIMARY KEY (uid),
	KEY parent (pid)
);


#
# Table structure for table 'tx_wecdevo_section'
#
CREATE TABLE tx_wecdevo_section (
	uid int(11) unsigned DEFAULT '0' NOT NULL auto_increment,
	pid int(11) unsigned DEFAULT '0' NOT NULL,
	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,	
	name tinytext NOT NULL,
	position int(11) DEFAULT '0' NOT NULL,
	start_at int(11) unsigned DEFAULT '0' NOT NULL,
	time_period int(11) unsigned DEFAULT '0' NOT NULL,
	template tinytext NOT NULL,
	has_comments tinyint(3) unsigned DEFAULT '0' NOT NULL,
	subscriber_only tinyint(3) unsigned DEFAULT '0' NOT NULL,
    data_uid int(11) unsigned DEFAULT '0' NOT NULL,
    next_sequence int(11) unsigned DEFAULT '0' NOT NULL,

	PRIMARY KEY (uid),
	KEY parent (pid)
);



#
# Table structure for table 'tx_wecdevo_journal'
#
CREATE TABLE tx_wecdevo_journal (
	uid int(11) unsigned DEFAULT '0' NOT NULL auto_increment,
	pid int(11) unsigned DEFAULT '0' NOT NULL,
	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,	
	owner_userid int(11) DEFAULT '0' NOT NULL,
	entry text NOT NULL,
	post_date date DEFAULT '0000-00-00' NOT NULL,

	PRIMARY KEY (uid),
	KEY parent (pid)
);



#
# Table structure for table 'tx_wecdevo_users'
#
CREATE TABLE tx_wecdevo_users (
	uid int(11) unsigned DEFAULT '0' NOT NULL auto_increment,
	pid int(11) unsigned DEFAULT '0' NOT NULL,
	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	user_uid int(11) DEFAULT '0' NOT NULL,
	email tinytext NOT NULL,
	lastlogin_date int(11) DEFAULT '0' NOT NULL,
	receive_by_email tinyint(3) unsigned DEFAULT '0' NOT NULL,
	how_paid tinytext NOT NULL,
	paidto_date int(11) DEFAULT '0' NOT NULL,
	usergroup int(11) DEFAULT '0' NOT NULL,
	misc tinytext NOT NULL,

	PRIMARY KEY (uid),
	KEY parent (pid)
);



#
# Table structure for table 'tx_wecdevo_topics'
#
CREATE TABLE tx_wecdevo_topics (
	uid int(11) unsigned DEFAULT '0' NOT NULL auto_increment,
	pid int(11) unsigned DEFAULT '0' NOT NULL,
	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,	
	topic tinytext NOT NULL,
	show_date int(11) DEFAULT '0' NOT NULL,
	num int(11) DEFAULT '0' NOT NULL,

	PRIMARY KEY (uid),
	KEY parent (pid)
);