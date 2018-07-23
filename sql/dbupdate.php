<#1>
<?php
$fields_data = array(
	'id' => array(
		'type' => 'integer',
		'length' => 8,
		'notnull' => true
	),
	'is_online' => array(
		'type' => 'integer',
		'length' => 1,
		'notnull' => false
	),
	'rmid' => array(
		'type' => 'integer',
		'length' => 8,
		'notnull' => false
	),
	'rmtypes' => array(
		'type' => 'integer',
		'length' => 1,
		'notnull' => false
	),
	'rmcomment' => array(
		'type' => 'text',
		'length' => 256,
		'fixed' => false,
		'notnull' => false
	),
	'rmparticipants' => array(
		'type' => 'integer',
		'length' => 4,
		'notnull' => false
	),
	'rmispublic' => array(
		'type' => 'integer',
		'length' => 1,
		'notnull' => false
	),
	'rmappointment' => array(
		'type' => 'integer',
		'length' => 1,
		'notnull' => false
	),
	'rmisdemo' => array(
		'type' => 'integer',
		'length' => 1,
		'notnull' => false
	),
	'rmdemotime' => array(
		'type' => 'integer',
		'length' => 8,
		'notnull' => false
	),
	'rmismoderated' => array(
		'type' => 'integer',
		'length' => 1,
		'notnull' => false
	)
	
);

$ilDB->createTable("rep_robj_xomv_data", $fields_data);
$ilDB->addPrimaryKey("rep_robj_xomv_data", array("id"));

$fields_conn = array(
        'id' => array(
                'type' => 'integer',
                'length' => 4,
                'notnull' => true
        ),
        'svrurl' => array(
                'type' => 'text',
                'length' => 256,
                'notnull' => true
        ),
        'svrport' => array(
                'type' => 'integer',
                'length' => 8,
                'notnull' => true
        ),
        'svrusername' => array(
                'type' => 'text',
                'length' => 256,
                'notnull' => true
        ),
        'svrpassword' => array(
                'type' => 'text',
                'length' => 256,
                'notnull' => true
        )
);

$ilDB->createTable("rep_robj_xomv_conn", $fields_conn);
$ilDB->addPrimaryKey("rep_robj_xomv_conn", array("id"));
?>
<#2>
<?php
if ( !$ilDB->tableColumnExists('rep_robj_xomv_conn', 'isdemoroom') ) {
	$ilDB->addTableColumn('rep_robj_xomv_conn', 'isdemoroom', array(
		'type' => 'integer',
		'length'  => 1,
		'notnull' => true,
		'default' => 0
	));
}
if ( !$ilDB->tableColumnExists('rep_robj_xomv_conn', 'ispublic') ) {
	$ilDB->addTableColumn('rep_robj_xomv_conn', 'ispublic', array(
		'type' => 'integer',
		'length'  => 1,
		'notnull' => true,
		'default' => 0
	));
}
if ( !$ilDB->tableColumnExists('rep_robj_xomv_conn', 'om2x') ) {
	$ilDB->addTableColumn('rep_robj_xomv_conn', 'om2x', array(
		'type' => 'integer',
		'length'  => 1,
		'notnull' => true,
		'default' => 0
	));
}
if ( !$ilDB->tableColumnExists('rep_robj_xomv_conn', 'allowuserquestions') ) {
	$ilDB->addTableColumn('rep_robj_xomv_conn', 'allowuserquestions', array(
		'type' => 'integer',
		'length'  => 1,
		'notnull' => true,
		'default' => 1
	));
}
if ( !$ilDB->tableColumnExists('rep_robj_xomv_conn', 'isaudioonly') ) {
	$ilDB->addTableColumn('rep_robj_xomv_conn', 'isaudioonly', array(
		'type' => 'integer',
		'length'  => 1,
		'notnull' => true,
		'default' => 1
	));
}
if ( !$ilDB->tableColumnExists('rep_robj_xomv_conn', 'hidetopbar') ) {
	$ilDB->addTableColumn('rep_robj_xomv_conn', 'hidetopbar', array(
		'type' => 'integer',
		'length'  => 1,
		'notnull' => true,
		'default' => 1
	));
}
if ( !$ilDB->tableColumnExists('rep_robj_xomv_conn', 'hidechat') ) {
	$ilDB->addTableColumn('rep_robj_xomv_conn', 'hidechat', array(
		'type' => 'integer',
		'length'  => 1,
		'notnull' => true,
		'default' => 1
	));
}
if ( !$ilDB->tableColumnExists('rep_robj_xomv_conn', 'hideactivitiesandactions') ) {
	$ilDB->addTableColumn('rep_robj_xomv_conn', 'hideactivitiesandactions', array(
		'type' => 'integer',
		'length'  => 1,
		'notnull' => true,
		'default' => 1
	));
}
if ( !$ilDB->tableColumnExists('rep_robj_xomv_conn', 'hidefilesexplorer') ) {
	$ilDB->addTableColumn('rep_robj_xomv_conn', 'hidefilesexplorer', array(
		'type' => 'integer',
		'length'  => 1,
		'notnull' => true,
		'default' => 1
	));
}
if ( !$ilDB->tableColumnExists('rep_robj_xomv_conn', 'hideactionsmenu') ) {
	$ilDB->addTableColumn('rep_robj_xomv_conn', 'hideactionsmenu', array(
		'type' => 'integer',
		'length'  => 1,
		'notnull' => true,
		'default' => 1
	));
}
if ( !$ilDB->tableColumnExists('rep_robj_xomv_conn', 'hidescreensharing') ) {
	$ilDB->addTableColumn('rep_robj_xomv_conn', 'hidescreensharing', array(
		'type' => 'integer',
		'length'  => 1,
		'notnull' => true,
		'default' => 1
	));
}
if ( !$ilDB->tableColumnExists('rep_robj_xomv_conn', 'hidewhiteboard') ) {
	$ilDB->addTableColumn('rep_robj_xomv_conn', 'hidewhiteboard', array(
		'type' => 'integer',
		'length'  => 1,
		'notnull' => true,
		'default' => 1
	));
}
?>
<#3>
<?php
if ( !$ilDB->tableColumnExists('rep_robj_xomv_conn', 'svrappname') ) {
	$ilDB->addTableColumn('rep_robj_xomv_conn', 'svrappname', array(
		'type' => 'text',
		'length'  => 32,
		'notnull' => true,
		'default' => 'openmeetings'
	));
}
?>
<#4>
<?php
if ( !$ilDB->tableColumnExists('rep_robj_xomv_conn', 'svrprotocol') ) {
	$ilDB->addTableColumn('rep_robj_xomv_conn', 'svrprotocol', array(
		'type' => 'text',
		'length'  => 5,
		'notnull' => true,
		'default' => 'https'
	));
}
if ( !$ilDB->tableColumnExists('rep_robj_xomv_conn', 'debug') ) {
	$ilDB->addTableColumn('rep_robj_xomv_conn', 'debug', array(
		'type' => 'integer',
		'length'  => 1,
		'notnull' => true,
		'default' => 0
	));
}
if ( !$ilDB->tableColumnExists('rep_robj_xomv_conn', 'svrversion') ) {
	$ilDB->addTableColumn('rep_robj_xomv_conn', 'svrversion', array(
		'type' => 'text',
		'length'  => 10,
		'notnull' => true,
		'default' => '4.0'
	));
}
if ( !$ilDB->tableColumnExists('rep_robj_xomv_conn', 'defdemoroom') ) {
	$ilDB->addTableColumn('rep_robj_xomv_conn', 'defdemoroom', array(
		'type' => 'integer',
		'length'  => 1,
		'notnull' => true,
		'default' => 0
	));
}
if ( !$ilDB->tableColumnExists('rep_robj_xomv_conn', 'defpublic') ) {
	$ilDB->addTableColumn('rep_robj_xomv_conn', 'defpublic', array(
		'type' => 'integer',
		'length'  => 1,
		'notnull' => true,
		'default' => 0
	));
}
if ( !$ilDB->tableColumnExists('rep_robj_xomv_conn', 'defallowuserquestions') ) {
	$ilDB->addTableColumn('rep_robj_xomv_conn', 'defallowuserquestions', array(
		'type' => 'integer',
		'length'  => 1,
		'notnull' => true,
		'default' => 1
	));
}
if ( !$ilDB->tableColumnExists('rep_robj_xomv_conn', 'defisaudioonly') ) {
	$ilDB->addTableColumn('rep_robj_xomv_conn', 'defisaudioonly', array(
		'type' => 'integer',
		'length'  => 1,
		'notnull' => true,
		'default' => 0
	));
}
if ( !$ilDB->tableColumnExists('rep_robj_xomv_conn', 'allowrecording') ) {
	$ilDB->addTableColumn('rep_robj_xomv_conn', 'allowrecording', array(
		'type' => 'integer',
		'length'  => 1,
		'notnull' => true,
		'default' => 1
	));
}
if ( !$ilDB->tableColumnExists('rep_robj_xomv_conn', 'defallowrecording') ) {
	$ilDB->addTableColumn('rep_robj_xomv_conn', 'defallowrecording', array(
		'type' => 'integer',
		'length'  => 1,
		'notnull' => true,
		'default' => 0
	));
}
if ( !$ilDB->tableColumnExists('rep_robj_xomv_conn', 'defwaitforrecording') ) {
	$ilDB->addTableColumn('rep_robj_xomv_conn', 'defwaitforrecording', array(
		'type' => 'integer',
		'length'  => 1,
		'notnull' => true,
		'default' => 0
	));
}



if ( $ilDB->tableColumnExists('rep_robj_xomv_conn', 'om2x') ) {
	$ilDB->dropTableColumn('rep_robj_xomv_conn', 'om2x');
}
if ( $ilDB->tableColumnExists('rep_robj_xomv_conn', 'hidetopbar') ) {
	$ilDB->dropTableColumn('rep_robj_xomv_conn', 'hidetopbar');
}
if ( $ilDB->tableColumnExists('rep_robj_xomv_conn', 'hidechat') ) {
	$ilDB->dropTableColumn('rep_robj_xomv_conn', 'hidechat');
}
if ( $ilDB->tableColumnExists('rep_robj_xomv_conn', 'hideactivitiesandactions') ) {
	$ilDB->dropTableColumn('rep_robj_xomv_conn', 'hideactivitiesandactions');
}
if ( $ilDB->tableColumnExists('rep_robj_xomv_conn', 'hidefilesexplorer') ) {
	$ilDB->dropTableColumn('rep_robj_xomv_conn', 'hidefilesexplorer');
}
if ( $ilDB->tableColumnExists('rep_robj_xomv_conn', 'hideactionsmenu') ) {
	$ilDB->dropTableColumn('rep_robj_xomv_conn', 'hideactionsmenu');
}
if ( $ilDB->tableColumnExists('rep_robj_xomv_conn', 'hidescreensharing') ) {
	$ilDB->dropTableColumn('rep_robj_xomv_conn', 'hidescreensharing');
}
if ( $ilDB->tableColumnExists('rep_robj_xomv_conn', 'hidewhiteboard') ) {
	$ilDB->dropTableColumn('rep_robj_xomv_conn', 'hidewhiteboard');
}

if ( !$ilDB->tableColumnExists('rep_robj_xomv_data', 'rmtype') ) {
	$ilDB->addTableColumn('rep_robj_xomv_data', 'rmtype', array(
		'type' => 'text',
		'length'  => 20,
		'notnull' => true,
		'default' => 'conference'
	));
}
if ( $ilDB->tableColumnExists('rep_robj_xomv_data', 'rmtypes') ) {
	$ilDB->dropTableColumn('rep_robj_xomv_data', 'rmtypes');
}
if ( $ilDB->tableColumnExists('rep_robj_xomv_data', 'rmappointment') ) {
	$ilDB->dropTableColumn('rep_robj_xomv_data', 'rmappointment');
}
if ( $ilDB->tableColumnExists('rep_robj_xomv_data', 'rmdemotime') ) {
	$ilDB->dropTableColumn('rep_robj_xomv_data', 'rmdemotime');
}
?>