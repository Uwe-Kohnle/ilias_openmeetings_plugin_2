<?php
include_once("./Services/Repository/classes/class.ilObjectPluginGUI.php");

/**
* User  class for Openmeetings repository object.
*
* User  classes process GET and POST parameter and call
* application classes to fulfill certain tasks.
*
* @author Paul <ilias@gdconsulting.it>
* @author Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
*
* $Id$
*
* Integration into control structure:
* - The GUI class is called by ilRepositoryGUI
* - GUI classes used by this class are ilPermissionGUI (provides the rbac
*   screens) and ilInfoScreenGUI (handles the info screen).
*
* @ilCtrl_isCalledBy ilObjOpenmeetingsGUI: ilRepositoryGUI, ilAdministrationGUI, ilObjPluginDispatchGUI
* @ilCtrl_Calls ilObjOpenmeetingsGUI: ilPermissionGUI, ilInfoScreenGUI, ilObjectCopyGUI, ilCommonActionDispatcherGUI
*
*/
class ilObjOpenmeetingsGUI extends ilObjectPluginGUI
{
	/**
	* Initialisation
	*/
	protected function afterConstructor()
	{
		// anything needed after object has been constructed
		// - Openmeetings: append my_id GET parameter to each request
		//   $ilCtrl->saveParameter($this, array("my_id"));
		//$this->deactivateCreationForm(ilObject2GUI::CFORM_IMPORT);
		//$this->deactivateCreationForm(ilObject2GUI::CFORM_CLONE);
	}
	
	/**
	* Get type.
	*/
	final function getType()
	{
		return "xomv";
	}
	
	/**
	* Handles all commmands of this class, centralizes permission checks
	*/
	function performCommand($cmd)
	{
		$next_class = $this->ctrl->getNextClass($this);
		switch($next_class)
		{
			case 'ilcommonactiondispatchergui':
				require_once 'Services/Object/classes/class.ilCommonActionDispatcherGUI.php';
				$gui = ilCommonActionDispatcherGUI::getInstanceFromAjaxCall();
				return $this->ctrl->forwardCommand($gui);
				break;
		}

		switch ($cmd)
		{
			case "editProperties":		// list all commands that need write permission here
			case "delete":
			case "updateProperties":
			case "deleteRecording":
			case "deleteRecordingConfirmed":
				$this->checkPermission("write");
				$this->$cmd();
				break;
			
			case "showContent":			// list all commands that need read permission here
			//case "...":
			//case "...":
				$this->checkPermission("read");
				$this->$cmd();
				break;
		}
	}
	/**
	 * init create form
	 * @param  $a_new_type
	 */
	public function initCreateForm($a_new_type)
	{
		$form = parent::initCreateForm($a_new_type);
		// online
		$cb = new ilCheckboxInputGUI($this->lng->txt("online"), "online");
		$form->addItem($cb);

		$field = new ilRadioGroupInputGUI($this->txt('rm_type'), 'type');
		$op1 = new ilRadioOption($this->txt('rm_conference'), 'conference');
		$field->addOption($op1);
		// $field->setChecked($op);
		$op2 = new ilRadioOption($this->txt('rm_restricted'), 'presentation');
		$field->addOption($op2);
		$op3 = new ilRadioOption($this->txt('rm_interview'), 'interview');
		$field->addOption($op3);
		$field->setValue('conference');
		$form->addItem($field);

		return $form;
	}

	/**
	 *
	 * @global <type> $ilCtrl
	 * @global <type> $ilUser
	 * @param ilObj $newObj
	 */
	public function afterSave(ilObject $newObj)
	{
		global $ilCtrl, $ilUser;
		$form = $this->initCreateForm('xomv');
		$form->checkInput();
		$om = $newObj->getDefaultRoom( $form->getInput("type") );
		$newObj->setOpenmeetingsObject($om);
		$newObj->setOnline( $form->getInput("online") );
		$newObj->doUpdate();
		parent::afterSave($newObj);
	}
	/**
	* After object has been created -> jump to this command
	*/
	function getAfterCreationCmd()
	{
		return "editProperties";
	}

	/**
	* Get standard command
	*/
	function getStandardCmd()
	{
		return "showContent";
	}
	
//
// DISPLAY TABS
//
	
	/**
	* Set tabs
	*/
	function setTabs()
	{
		global $ilTabs, $ilCtrl, $ilAccess;
		
		// tab for the "show content" command
		if ($ilAccess->checkAccess("read", "", $this->object->getRefId()))
		{
			$ilTabs->addTab("content", $this->txt("content"), $ilCtrl->getLinkTarget($this, "showContent"));
		}

		// standard info screen tab
		$this->addInfoTab();

		// a "properties" tab
		if ($ilAccess->checkAccess("write", "", $this->object->getRefId()))
		{
			$ilTabs->addTab("properties", $this->txt("properties"), $ilCtrl->getLinkTarget($this, "editProperties"));
		}

		// standard permission tab
		$this->addPermissionTab();
	}

	/**
	* Edit Properties. This commands uses the form class to display an input form.
	*/
	function editProperties()
	{
		global $tpl, $ilTabs;

		$ilTabs->activateTab("properties");
		$this->initPropertiesForm();
		$this->getPropertiesValues();
		$tpl->setContent($this->form->getHTML());
	}
	
	function selectItemCheck($item) {//weg
		include_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/Openmeetings/classes/class.ilOpenmeetingsConfig.php");
		$settings = ilOpenmeetingsConfig::getInstance();
		if ($settings->getAllowUpdate($item) && ($item == "isDemoRoom" || $item == "ispublic" || $settings->getAllowUpdate("om2x") == true)) {
			$hd = new ilCheckboxInputGUI($this->lng->txt("rep_robj_xomv_rm".$item), "rm".$item);
			$hd->setInfo($this->lng->txt("rep_robj_xomv_info_".$item));
		} else {
			$hd = new ilHiddenInputGUI("rm".$item);
		}
		$hd->setValue(1);
		return $hd;
	}
	
	/**
	* Init  form.
	*
	* @param        int        $a_mode        Edit Mode
	*/
	public function initPropertiesForm()
	{
		global $ilCtrl;
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$this->form = new ilPropertyFormGUI();
		
		$omselect = $this->object->getOmSelectForGui();
		$pluginTxt = 'rep_robj_xomv_';

		// title
		$ti = new ilTextInputGUI($this->txt("title"), "title");
		$ti->setRequired(true);
		$this->form->addItem($ti);
				
		// description
		$ta = new ilTextAreaInputGUI($this->txt("description"), "desc");
		$this->form->addItem($ta);
		
		// online
		$cb = new ilCheckboxInputGUI($this->lng->txt("online"), "online");
		$this->form->addItem($cb);
		
		// // rmid
		// $hd = new ilHiddenInputGUI("rmid");
		// $hd->setValue(30);
		// $this->form->addItem($hd);
		$field = new ilRadioGroupInputGUI($this->txt('rm_type'), 'type');
		$op1 = new ilRadioOption($this->txt('rm_conference'), 'conference');
		$field->addOption($op1);
		// $field->setChecked($op);
		$op2 = new ilRadioOption($this->txt('rm_restricted'), 'presentation');
		$field->addOption($op2);
		$op3 = new ilRadioOption($this->txt('rm_interview'), 'interview');
		$field->addOption($op3);
		
		// rmparticipants
		$options_ref = array(2,4,6,8,10,12,14,16,20,25);
		$hd = new ilSelectInputGUI($this->txt('capacity'), 'capacity_conference');
		for ($i = 0; $i < count($options_ref); $i++) {
			$options[$options_ref[$i]] = $options_ref[$i];
		}
		$hd->setOptions($options);
		$hd->setInfo($this->txt('info_capacity_conference'));
		$op1->addSubItem($hd);

		$options_ref = array(2,4,6,8,10,12,14,16,20,25,32,50,100,150); //,200,500,1000
		$hd = new ilSelectInputGUI($this->txt('capacity'), 'capacity');
		for ($i = 0; $i < count($options_ref); $i++) {
			$options[$options_ref[$i]] = $options_ref[$i];
		}
		$hd->setOptions($options);
		$hd->setInfo($this->txt('info_capacity_presentation'));
		$op2->addSubItem($hd);

		$this->form->addItem($field);

		if ($omselect['moderated']) {
			$field = new ilCheckboxInputGUI($this->txt('set_moderated'), 'moderated');
			$field->setInfo($this->txt('set_info_moderated'));
			$this->form->addItem($field);
		}

		if ($omselect['demo']) {
			$field = new ilCheckboxInputGUI($this->txt('set_demo'), 'demo');
			$field->setInfo($this->txt('set_info_demo'));
			$this->form->addItem($field);
		}
		
		if ($omselect['isPublic']) {
			$field = new ilCheckboxInputGUI($this->txt('set_isPublic'), 'isPublic');
			$field->setInfo($this->txt('set_info_isPublic'));
			$this->form->addItem($field);
		}

		if ($omselect['allowUserQuestions']) {
			$field = new ilCheckboxInputGUI($this->txt('set_allowUserQuestions'), 'allowUserQuestions');
			$field->setInfo($this->txt('set_info_allowUserQuestions'));
			$this->form->addItem($field);
		}

		if ($omselect['audioOnly']) {
			$field = new ilCheckboxInputGUI($this->txt('set_audioOnly'), 'audioOnly');
			$field->setInfo($this->txt('set_info_audioOnly'));
			$this->form->addItem($field);
		}

		if ($omselect['allowRecording']) {
			$field = new ilCheckboxInputGUI($this->txt('set_allowRecording'), 'allowRecording');
			$field->setInfo($this->txt('set_info_allowRecording'));
			$cb = new ilCheckboxInputGUI($this->txt('set_waitForRecording'), 'waitForRecording');
			$cb->setInfo($this->txt('set_info_waitForRecording'));
			$field->addSubItem($cb);
			$this->form->addItem($field);
		}

		$this->form->addCommandButton("updateProperties", $this->txt("save"));
	                
		$this->form->setTitle($this->txt("edit_properties"));
		$this->form->setFormAction($ilCtrl->getFormAction($this));
	}
	
	/**
	* Get values for edit properties form
	*/
	function getPropertiesValues()
	{
		$this->object->omRead();
		$values = $this->object->getOpenmeetingsObject();
		$values["title"] = $this->object->getTitle();
		$values["desc"] = $this->object->getDescription();
		$values["online"] = $this->object->getOnline();
		// $values["rmid"] = $this->object->getrmId();

		$capacity = $values["capacity"];
		if ($capacity > 25) $values["capacity_conference"] = 25;
		else $values["capacity_conference"] = $capacity;

		$this->form->setValuesByArray($values);
		
	}
	
	/**
	* Update properties
	*/
	public function updateProperties()
	{

		global $tpl, $lng, $ilCtrl;
		$this->initPropertiesForm();
		if ($this->form->checkInput())
		{
			$this->object->omRead();
			$this->object->setTitle($this->form->getInput("title"));
			$this->object->setDescription($this->form->getInput("desc"));
			$this->object->setOnline($this->form->getInput("online"));
			// $this->object->setrmId($this->form->getInput("rmid"));
			$omselect = $this->object->getOmSelectForGui();
			$this->object->setOpenmeetingsItem('name', $this->object->getOmName());
			foreach($this->object->getOpenmeetingsObject() as $key => $value)
			{
				$input = $this->form->getInput($key);
				switch ($key) {
					case 'moderated':
					case 'demo':
					case 'isPublic':
					case 'allowUserQuestions':
					case 'audioOnly':
						if ($omselect[$key]) {
							$this->object->setOpenmeetingsItem($key, ($input==1) );
						}
						break;
					case 'allowRecording':
					case 'waitForRecording':
						if ($omselect['allowRecording']) {
							$this->object->setOpenmeetingsItem($key, ($input==1) );
						}
						break;
					default:
						if ($input != null) {
							$this->object->setOpenmeetingsItem($key, $input);
						}
				}
			}
			//corrections
			if ($this->form->getInput('type') == 'conference') {
				$this->object->setOpenmeetingsItem('capacity', $this->form->getInput('capacity_conference'));
			} else if ($this->form->getInput('type') == 'interview') {
				$this->object->setOpenmeetingsItem('capacity', 2);
			}
			$returnVal = $this->object->update();
			ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
			$ilCtrl->redirect($this, "editProperties");
		}

		$this->form->setValuesByPost();
		$tpl->setContent($this->form->getHtml());
	}


	/**
	* Show content
	*/
	function showContent()
	{
		global $tpl, $ilTabs, $ilCtrl, $ilAccess;
		$ilTabs->clearTargets();
		$ilTabs->activateTab("content");//necessary...
		
		$my_tpl = new ilTemplate("./Customizing/global/plugins/Services/Repository/RepositoryObject/Openmeetings/templates/tpl.Openmeetingsclient.html", true, true);
		$a_tmp = $this->object->getCmdUrlToShowContentAndRecordings();
		$cmdURL = $a_tmp[0];
		$recURL = $a_tmp[1];
		$aRecordings = $a_tmp[2];
		$my_tpl->setVariable("omHint", $this->txt('om_hint'));
		$my_tpl->setVariable("cmdURL", $cmdURL);
		$my_tpl->setVariable("recURL", $recURL);
		$my_tpl->setVariable("omRecDelURL", $ilCtrl->getLinkTarget($this, 'deleteRecording'));
		if ($ilAccess->checkAccess("write", "", $this->object->getRefId())) {
			$my_tpl->setVariable("omRecDelAllowed", 'true');
		} else {
			$my_tpl->setVariable("omRecDelAllowed", 'false');
		}
		$my_tpl->setVariable("omRecLngRecord", $this->txt('record_name'));
		$my_tpl->setVariable("omRecLngTime", $this->txt('record_time'));
		$my_tpl->setVariable("omRecLngWidth", $this->txt('record_width'));
		$my_tpl->setVariable("omRecLngHeight", $this->txt('record_height'));
		$my_tpl->setVariable("omRecLngNone", $this->txt('record_none'));
		$my_tpl->setVariable("omRecLngDelete", $this->lng->txt('delete'));
		$my_tpl->setVariable("omRecordings", $aRecordings);
		$my_tpl->setVariable("omStartOption", $this->txt('om_start_option'));
		$my_tpl->setVariable("omStartIframe", $this->txt('om_start_iframe'));
		$my_tpl->setVariable("omStartIframeOnly", $this->txt('om_start_iframe_only'));
		$my_tpl->setVariable("omStartWindow", $this->txt('om_start_window'));
		$my_tpl->setVariable("omStartWindowOnly", $this->txt('om_start_window_only'));
		$my_tpl->setVariable("omStartDisable", $this->txt('om_start_disable'));
		$my_tpl->setVariable("omStartDisabled", $this->txt('om_start_disabled'));
		$my_tpl->setVariable("omReload", $this->txt('om_reload'));
		$my_tpl->setVariable("omStartIframeAuto", $this->txt('om_start_iframe_auto'));
		$my_tpl->setVariable("omStartWindowAuto", $this->txt('om_start_window_auto'));
		$my_tpl->setVariable("omStartStop", $this->txt('om_start_stop'));
		$my_tpl->setVariable("omWindowBlocked", $this->txt('om_window_blocked'));
		$my_tpl->setVariable("omWindowStarted", $this->txt('om_window_started'));
		$my_tpl->setVariable("omWindowClosed", $this->txt('om_window_closed'));
		$tpl->setContent($my_tpl->get());
	}
	
    /**
     * Show a confirmation screen to delete a rocording
     */
    function deleteRecording()
    {
        global $ilCtrl, $lng, $tpl, $ilTabs;
		$ilTabs->clearTargets();

        require_once('./Services/Utilities/classes/class.ilConfirmationGUI.php');

        $gui = new ilConfirmationGUI();
        $gui->setFormAction($ilCtrl->getFormAction($this));
        $gui->setHeaderText($this->txt('delete_recording'));
        $gui->addItem('recordId', $_GET['recordId'], $_GET['recordName']);
        $gui->setConfirm($lng->txt('delete'), 'deleteRecordingConfirmed');
        $gui->setCancel($lng->txt('cancel'), 'showContent');

        $tpl->setContent($gui->getHTML());
    }

    /**
     * Delete a type after confirmation
     */
    function deleteRecordingConfirmed()
    {
        global $ilCtrl, $lng, $ilTabs;
		$ilTabs->clearTargets();

		$omresult = $this->object->deleteRecording($_POST['recordId']);
        ilUtil::sendSuccess($this->txt('recording_deleted'), true);
        $ilCtrl->redirect($this, 'showContent');
    }



}
?>
