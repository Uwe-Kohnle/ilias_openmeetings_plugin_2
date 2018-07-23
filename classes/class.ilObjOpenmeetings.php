<?php
/**
* Application class for Openmeetings repository object.
*
* @author  Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
*
* @version $Id$
*/

include_once('./Services/Repository/classes/class.ilObjectPlugin.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/Openmeetings/lib/OmGateway.php');


class ilObjOpenmeetings extends ilObjectPlugin
{
	/**
	* Constructor
	*
	* @access	public
	*/
	public function __construct($a_ref_id = 0)
	{
		$this->getConfigValues();
		parent::__construct($a_ref_id);
	}
	

	/**
	* Get type.
	*/
	public final function initType()
	{
		$this->setType('xomv');
	}
	
	/**
	*/
	private $omConfig	= array();
	private $omSelect	= array();
	private $omDebug	= false;
	
	protected function getConfigValues() {
		include_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/Openmeetings/classes/class.ilOpenmeetingsConfig.php');
		$settings = ilOpenmeetingsConfig::getInstance();
		
		$this->omConfig = array(
			  'protocol'	=> $settings->getOmConf('svrprotocol')
			, 'host'		=> $settings->getOmConf('svrurl')
			, 'port'		=> $settings->getOmConf('svrport')
			, 'context'		=> $settings->getOmConf('svrappname')
			, 'user'		=> $settings->getOmConf('svrusername')
			, 'pass'		=> $settings->getOmConf('svrpassword')
			, 'module'		=> 'ILIAS'
		);
		
		if ($settings->getOmConf('debug') == 1) {
			$this->omDebug = true;
		}
		
		$this->omSelect = array(
			  'demo'			=> $settings->getOmConf('isDemoRoom')
			, 'isPublic'		=> $settings->getOmConf('ispublic')
			, 'allowUserQuestions'	=> $settings->getOmConf('allowUserQuestions')
			, 'audioOnly'		=> $settings->getOmConf('isaudioonly')
			, 'allowRecording'	=> $settings->getOmConf('allowrecording')
			, 'moderated'		=> false
		);
	}
	
	/**
	*
	*/
	public function getOmConfig() {
		return $this->omConfig;
	}

	public function getModuleKey() {
		$iliasDomain = substr(ILIAS_HTTP_PATH,7);
		if (substr($iliasDomain,0,1) == "\/") $iliasDomain = substr($iliasDomain,1);
		if (substr($iliasDomain,0,4) == "www.") $iliasDomain = substr($iliasDomain,4);
		return $iliasDomain.';'.CLIENT_ID;
	}
	
	public function getOmName() {
		return $this->getTitle().' ('.$this->getModuleKey().';'.$this->getRefId().')';
	}


	public function getDefaultRoom($rmtype) {
		include_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/Openmeetings/classes/class.ilOpenmeetingsConfig.php');
		$settings = ilOpenmeetingsConfig::getInstance();

		global $ilUser, $tree;
		$parent_type = 'grp';
		$parent_ref_id = $tree->checkForParentType($this->getRefId(), $parent_type);
		if(!$parent_ref_id) {
			$parent_type = 'crs';
			$parent_ref_id = $tree->checkForParentType($this->getRefId(), $parent_type);
		}				
		if(!$parent_ref_id) {
			$parent_type = 'obj';
			$parent_ref_id=$tree->getParentId($this->getRefId());
		}
		$parent_obj = ilObject::_lookupObjectId($parent_ref_id);
		$parent_title = ilObject::_lookupTitle($parent_obj);
		
		$comment = ilUtil::shortenText(
			'created by '.$ilUser->getlogin().' in '.$parent_type.' with ref_id '.$tree->getParentId($this->getRefId()).' and title: '.$parent_title
			, 255);
		
		$isModeratedRoom = false;
		if ($parent_type == 'grp' || $parent_type == 'crs') $isModeratedRoom = true;

		$max_user = 150;
		if ($rmtype == 'conference') $max_user = 12;//25
		else if ($rmtype == 'interview') $max_user = 2;

		return array(
				'id' => null
				, 'name'			=> $this->getOmName()
				, 'comment'			=> $comment
				, 'type'			=> $rmtype
				, 'capacity'		=> $max_user
				, 'appointment'		=> false
				, 'isPublic'		=> $settings->getOmConf('defPublic')
				, 'demo'			=> $settings->getOmConf('defDemoRoom')
				, 'closed'			=> false //
				, 'externalId'		=> $this->getModuleKey().';'.$this->getRefId()
				// , 'externalType'	=> 'ILIAS'
				// , 'redirectUrl'		=> ILIAS_HTTP_PATH.'/ilias.php?client='.CLIENT_ID.'&baseClass=ilObjPluginDispatchGUI&cmd=forward&forwardCmd=showContent&ref_id='.$this->getRefId()//nicht zuverlaessig in 4.0.2; ohne Verknüpfung
				, 'moderated'		=> $isModeratedRoom
				, 'allowUserQuestions'=> $settings->getOmConf('defallowuserquestions')
				, 'allowRecording'	=> $settings->getOmConf('defallowrecording')
				, 'waitForRecording'=> $settings->getOmConf('defwaitforrecording')
				, 'audioOnly'		=> $settings->getOmConf('defisaudioonly')
		);
	}

	/**
	* Create object
	*/
	public function doCreate() {
	}
	
	/**
	* Create/update object
	*/
	public function doUpdate() {
		global $ilDB;
		$gateway = new OmGateway($this->getOmConfig());
		$input = $this->getOpenmeetingsObject();
		if ($gateway->login()) {
			$rmNum = $gateway->updateRoom($input);
		} else {
			if ($this->omDebug) $GLOBALS['ilLog']->write(__METHOD__.': Could not login SOAP-User to Openmeetings '.$e->getCode());
			echo 'Could not login SOAP-User to Openmeetings, check your Openmeetings Module Configuration';
			// print_r($this->getOmConfig());
			exit();
		}
		$this->setrmId($rmNum);
		$a_data = array(
			  'id'			=> array('integer', $this->getId())
			, 'is_online'	=> array('integer', $this->getOnline())
			, 'rmid'		=> array('integer', $rmNum)
			, 'rmcomment'	=> array('text', $input['comment'])
			, 'rmparticipants' => array('integer', $input['capacity'])
			, 'rmispublic'	=> array('integer', $input['isPublic'])
			, 'rmisdemo'	=> array('integer', $input['demo'])
			, 'rmismoderated' => array('integer', $input['moderated'])
			, 'rmtype'		=> array('text', $input['type'])
		);
		// check if data exisits decide to update or insert
		$result = $ilDB->query('SELECT id FROM rep_robj_xomv_data WHERE id='.$ilDB->quote($this->getId(),'integer') );
		$num = $ilDB->numRows($result);
		if($num == 0){
			$ilDB->insert('rep_robj_xomv_data', $a_data);
		} else {
			$ilDB->update('rep_robj_xomv_data', $a_data, array('id' => array('integer', $this->getId())));
		}
	}
	
	/**
	* Read data from db
	*/
	public function doRead() {
		global $ilDB;
		
		$set = $ilDB->query("SELECT is_online,rmid FROM rep_robj_xomv_data ".
			" WHERE id = ".$ilDB->quote($this->getId(), "integer")
			);
		while ($rec = $ilDB->fetchAssoc($set))
		{
			$this->setOnline($rec['is_online']);
			$this->setrmId($rec['rmid']);
		}
	}
	/**
	* Read data from openmeetings
	*/
	public function omRead() {
		$gateway = new OmGateway($this->getOmConfig());
		if ($gateway->login()) {
			$om = $gateway->getRoom($this->getrmId());
			//example 4.0.2: Array ( [id] => 18 [name] => testa (192.168.56.103/ilias51;inno3;157) [comment] => [type] => conference [capacity] => 25 [appointment] => [isPublic] => [demo] => [closed] => [externalId] => 192.168.56.103/ilias51;inno3;157 [externalType] => ILIAS [redirectUrl] => [moderated] => [allowUserQuestions] => 1 [allowRecording] => [waitForRecording] => [audioOnly] => ) 
			if ($om == -1) {
				if ($this->omDebug) $GLOBALS['ilLog']->write(__METHOD__.': Could not load data for room '.$this->getrmId().' '.$e->getCode());
				echo 'Could not load data for room '.$this->getrmId();
				exit();
			} else {
				if ($this->omDebug) $GLOBALS['ilLog']->write(__METHOD__.': Could load data for room '.$this->getrmId());
				$this->setOpenmeetingsObject($om);
			}
		} else {
			if ($this->omDebug) $GLOBALS['ilLog']->write(__METHOD__.': Could not login SOAP-User to Openmeetings '.$e->getCode());
			echo 'Could not login SOAP-User to Openmeetings, check your Openmeetings Module Configuration';
			// print_r($this->getOmConfig());
			exit();
		}
		return true;
	}
	
	/**
	* Delete data from db
	*/
	public function doDelete() {
		$gateway = new OmGateway($this->getOmConfig());
		if ($gateway->login()) {
			$om = $gateway->deleteRoom($this->getrmId());//check: records weg?
			if ($om == -1) {
				if ($this->omDebug) $GLOBALS['ilLog']->write(__METHOD__.': failure deleting room '.$this->getrmId().' '.$e->getCode());
				echo 'failure deleting room '.$this->getrmId();
				exit();
			}
		} else {
			if ($this->omDebug) $GLOBALS['ilLog']->write(__METHOD__.': Could not login SOAP-User to Openmeetings '.$e->getCode());
			echo 'Could not login SOAP-User to Openmeetings, check your Openmeetings Module Configuration';
			exit();
		}
		global $ilDB;
		$ilDB->manipulate("DELETE FROM rep_robj_xomv_data WHERE id = ".$ilDB->quote($this->getId(), "integer"));
	}
	
	public function doCloneObject($new_obj, $a_target_id, $a_copy_id = 0)
	{
		$this->doClone($new_obj, $a_target_id, $a_copy_id);
	}
	/**
	* Do Cloning
	*/
	public function doClone($new_obj, $a_target_id, $a_copy_id) //deactivated
	{
		global $lng;
		$this->omRead();
		$titletmp = $this->getTitle().' ('.$this->lng->txt('copy').')';
		$new_obj->setTitle($titletmp);
		$new_obj->setOpenmeetingsObject($this->getOpenmeetingsObject());
		$new_obj->setOpenmeetingsItem('name', $new_obj->getOmName());
		$new_obj->setOpenmeetingsItem('id', null);
		$new_obj->doUpdate();
	}
	
	/**
	* Delete Recording
	*/
	public function deleteRecording($recordId) {
		$gateway = new OmGateway($this->getOmConfig());
		if ($gateway->login()) {
			$om = $gateway->deleteRecording($recordId);
			if ($this->omDebug) $GLOBALS['ilLog']->write(__METHOD__.': Could delete recording '.$recordId);
		} else {
			if ($this->omDebug) $GLOBALS['ilLog']->write(__METHOD__.': Could not login SOAP-User to Openmeetings '.$e->getCode());
			echo 'Could not login SOAP-User to Openmeetings, check your Openmeetings Module Configuration';
			exit();
		}
		return true;
	}
	
	/**
	* get cmdURL and other to show Content 
	*/
	public function getCmdUrlToShowContentAndRecordings() {
		global $ilUser, $ilCtrl, $ilDB, $ilAccess;
		$rm_ID = $this->getrmId();
		if ($rm_ID == null) {echo 'no room id';die();}
		$user_id=$ilUser->getID();
		$user_login = $ilUser->getlogin();
		$user_firstname = $ilUser->getFirstname();
		$user_lastname = $ilUser->getLastname();
		$user_language = $ilUser->getCurrentLanguage();
		$user_email = $ilUser->getEmail();//Hinweis!!
		$user_image = substr($ilUser->getPersonalPicturePath($a_size = 'xsmall', $a_force_pic = true),2);
		if (substr($user_image,0,2) == './') $user_image = substr($user_image,2);
		$user_image = ILIAS_HTTP_PATH.'/'.$user_image;

		$recordings = array();
		$rec2gui = array();
		
		$gateway = new OmGateway($this->getOmConfig());
		if ($gateway->login()) {
			$om = $gateway->getRoom($this->getrmId());
			if ($om == -1) {
				if ($this->omDebug) $GLOBALS['ilLog']->write(__METHOD__.': Could not load data for room '.$this->getrmId().' '.$e->getCode());
				echo 'Could not load data for room '.$this->getrmId();
				exit();
			} else {
				$this->setOpenmeetingsObject($om);
				$recordings = $gateway->getRecordings();
				if ($this->omDebug) $GLOBALS['ilLog']->write(__METHOD__.': Could load data (and recordings) for room '.$this->getrmId());
			}
		} else {
			if ($this->omDebug) $GLOBALS['ilLog']->write(__METHOD__.': Could not login SOAP-User to Openmeetings '.$e->getCode());
			echo 'Could not login SOAP-User to Openmeetings, check your Openmeetings Module Configuration';
			exit();
		}
		
		for ($i=0; $i<count($recordings); $i++) {
			$rec = $recordings[$i];
			$timediff = strtotime($rec['end'])-strtotime($rec['start']);
			$timestr = ''.floor($timediff/3600).':'.floor($timediff/60%60).':'.floor($timediff%60);
			$rec2gui[] = array($rec['id'],$rec['name'],$timestr,$rec['width'],$rec['height']);
		}
		
		$moderator=false;
		if ($om['moderated'] == 1) {
			if ($ilAccess->checkAccess("write", "", $this->getRefId())) {
				$moderator=true;
			}
		}
		// print_r($user_login.'<br>'.$user_firstname.'<br>'.$user_lastname.'<br>'.$user_image.'<br>'.$user_email.'<br>'.$user_id);
		$omusr = $gateway->getUser($user_login,$user_firstname,$user_lastname,$user_image, $user_email,$user_id);
		$options = array("roomId" => $om['id'], "moderator" => $moderator, "allowRecording" => $om['allowRecording']);
		$secHash = $gateway->getSecureHash($omusr, $options);
		//analog languages.xml
		//in 4.0.2 ohne Effekt - und: User laesst sich nicht mit Sprache anlegen
		$a_lang = array(
			  'en' => 1
			, 'de' => 2
			, 'fr' => 4
			, 'it' => 5
			, 'pt' => 6
			, 'es' => 8
		);
		$omlang = 1;
		if (array_key_exists($user_language, $a_lang)) $omlang = $a_lang[$ilUser->prefs['language']];

		$cmdURL = $gateway->getUrl() . '/hash?&secure=' . $secHash . '&language='.$omlang;
		$recUrl = $gateway->getUrl() . '/recordings/mp4/';
		return array($cmdURL,$recUrl,json_encode($rec2gui));
	}
	/**
	* Set/Get Methods for our Openmeetings properties
	*/
	public function ilBoolToOm($a_val) {
		if ($a_val == 1) return "true";
		return "false";
	}
	public function omBoolToIl($a_val) {
		if ($a_val == "true") return 1;
		return 0;
	}

	/**
	* Set online
	*
	* @param	boolean		online
	*/
	public function setOnline($a_val)
	{
		$this->online = $a_val;
	}
	
	/**
	* Get online
	*
	* @return	boolean		online
	*/
	public function getOnline()
	{
		return $this->online;
	}
			


	public function setrmId($a_val){
		$this->rmid = $a_val;
	}

	public function getrmId(){
		return $this->rmid;
	}

	public function setOpenmeetingsObject($a_val) {
		$this->openmeetingsObject = $a_val;
	}
	public function getOpenmeetingsObject() {
		return $this->openmeetingsObject;
	}
	public function setOpenmeetingsItem($a_item,$a_val) {
		$this->openmeetingsObject[$a_item] = $a_val;
	}

	public function getOmSelectForGui() {
		global $tree;
		$parent_type = 'grp';
		$parent_ref_id = $tree->checkForParentType($this->getRefId(), $parent_type);
		if(!$parent_ref_id) {
			$parent_type = 'crs';
			$parent_ref_id = $tree->checkForParentType($this->getRefId(), $parent_type);
		}				
		if(!$parent_ref_id) {
			$parent_type = 'obj';
		}
		
		$omselect = $this->omSelect;
		if ($parent_type == 'grp' || $parent_type == 'crs') {
			$omselect['moderated'] = true;
		}

		return $omselect;
	}
}
?>
