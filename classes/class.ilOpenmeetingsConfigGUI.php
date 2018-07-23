<?php

include_once("./Services/Component/classes/class.ilPluginConfigGUI.php");
 
/**
* openMeetings configuration user interface class
*
* @author Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
* @version $Id$
*
* @ilCtrl_Calls ilOpenmeetingsConfigGUI: ilCommonActionDispatcherGUI
*/
class ilOpenmeetingsConfigGUI extends ilPluginConfigGUI
{
	/**
	* Handles all commmands, default is "configure"
	*/
	public function performCommand($cmd)
	{

		switch ($cmd)
		{
			case "configure":
			case "save":
				$this->$cmd();
				break;

		}
	}

	/**
	 * Configure screen
	 */
	public function configure()
	{
		global $tpl;

		$this->initConfigurationForm();
		$this->getValues();
		$tpl->setContent($this->form->getHTML());
		
	}
	
	/**
	 * Init configuration form.
	 *
	 * @return object form object
	 */
	public function initConfigurationForm()
	{
		global $lng, $ilCtrl;

		// $pl = $this->getPluginObject();
		$this->getPluginObject()->includeClass('class.ilOpenmeetingsConfig.php');
		$this->object = ilOpenmeetingsConfig::getInstance();

		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$this->form = new ilPropertyFormGUI();
		
		$this->form->setTitle($this->getPluginObject()->txt("openmeetings_plugin_configuration"));
		$this->form->setFormAction($ilCtrl->getFormAction($this));
		$this->form->addCommandButton("save", $lng->txt("save"));

		foreach($this->object->getOmConfStruct() as $key => $item)
		{
			switch ($item["type"]) {
				case "a_text":
					$field = new ilRadioGroupInputGUI($this->getPluginObject()->txt('conf_'.$key), $key);
					for ($i=0; $i<count($item["options"]); $i++) {
						$op = new ilRadioOption($item["options"][$i], $item["options"][$i]);
						$field->addOption($op);
					}
					break;
				case "text":
					$field = new ilTextInputGUI($this->getPluginObject()->txt('conf_'.$key), $key);
					$field->setRequired(true);
					$field->setMaxLength($item["maxlength"]);
					$field->setInfo($this->plugin_object->txt('info_'.$key));
					break;
				case "integer":
					$field = new ilNumberInputGUI($this->getPluginObject()->txt('conf_'.$key), $key);
					$field->setRequired(true);
					$field->setMaxLength($item["maxlength"]);
					$field->setSize($item["maxlength"]);
					$field->setInfo($this->plugin_object->txt('info_'.$key));
					break;
				case "password":
					$field = new ilPasswordInputGUI($this->getPluginObject()->txt('conf_'.$key), $key);
					$field->setRequired(true);
					$field->setMaxLength($item["maxlength"]);
					$field->setRetype(false);
					$field->setInfo($this->plugin_object->txt('info_'.$key));
					break;
				case "bool":
					$field = new ilCheckboxInputGUI($this->getPluginObject()->txt('conf_'.$key), $key);
					$field->setInfo($this->plugin_object->txt('info_'.$key));
					break;
				default:
			}
			
			// // if(is_array($item["subelements"]))
			// // {
				// // foreach($item["subelements"] as $subkey => $subitem)
				// // {
					// // $subfield = new $subitem["type"]($this->plugin_object->txt('conf_'.$key . "_" . $subkey),$subkey);
					// // $subfield->setInfo($this->plugin_object->txt($subitem["info"]));
					// // $field->addSubItem($subfield);
				// // }
			// // }
			$this->form->addItem($field);
		}
		return $this->form;
	}
	
	public function getValues()
	{
		foreach($this->object->getOmConfStruct() as $key => $value)
		{
			$values[$key] = $this->object->getOmConf($key);
			// if(is_array($item["subelements"]))
			// {
				// foreach($item["subelements"] as $subkey => $subitem)
				// {
					// $values[$subkey] = $this->object->getOmConf($subkey);
				// }
			// }
		}
		$this->form->setValuesByArray($values);
	}

	/**
	 * Save form input
	 *
	 */
	public function save()
	{
		global $tpl, $lng, $ilCtrl;
	
		$pl = $this->getPluginObject();
		
		$form = $this->initConfigurationForm();
		if ($form->checkInput())
		{
			foreach($this->object->getOmConfStruct() as $key => $value)
			{
				$this->object->setOmConf($key, $this->form->getInput($key));
				// if(is_array($item["subelements"]))
				// {
					// foreach($item["subelements"] as $subkey => $subitem)
					// {
						// $this->object->setOmConf($subkey, $this->form->getInput($subkey));
					// }
				// }
			}
			$this->object->save();
			ilUtil::sendSuccess($pl->txt("saving_invoked"), true);
			$ilCtrl->redirect($this, "configure");
		}
		else
		{
			$form->setValuesByPost();
			$tpl->setContent($form->getHtml());
		}
	}
	


}
?>
