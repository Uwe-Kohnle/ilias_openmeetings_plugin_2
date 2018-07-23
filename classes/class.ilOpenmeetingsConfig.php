<?php
/**
 * Openmeetings configuration class
 * @author  Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @version $Id$
 *
 */
class ilOpenmeetingsConfig
{
	private static $instance = null;
	
	private $omconf = array (
			  'svrprotocol'	=> 'http'
			, 'svrurl'		=> 'yourserver.de'
			, 'svrport'		=> 5080
			, 'svrappname'	=> 'openmeetings'
			, 'svrusername'	=> ''
			, 'svrpassword'	=> ''
			, 'svrversion'	=> '>4.0.1'
			, 'debug'		=> false
			, 'isDemoRoom'	=> false
			, 'defDemoRoom'	=> false
			, 'ispublic'	=> false
			, 'defPublic'	=> false
			, 'allowUserQuestions'		=> false
			, 'defallowuserquestions'	=> true
			, 'isaudioonly'			=> false
			, 'defisaudioonly'		=> false
			, 'allowrecording'		=> true
			, 'defallowrecording'	=> false
			, 'defwaitforrecording'	=> true
		);

	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->read();
	}
	
	/**
	 * Get singleton instance
	 * 
	 * @return ilOpenmeetingsConfig
	 */
	public static function getInstance()
	{
		if(self::$instance)
		{
			return self::$instance;
		}
		return self::$instance = new ilOpenmeetingsConfig();
	}

	public function getOmConfStruct() {
		$omstruct = array();
		foreach($this->omconf as $key => $value)  {
			switch ($key) {
				case 'svrprotocol':
					$omstruct[$key] = array('type'=>'a_text', 'maxlength'=>5, 'options'=>array('http','https'));
					break;
				case 'svrversion':
					$omstruct[$key] = array('type'=>'a_text', 'maxlength'=>5, 'options'=>array('>4.0.11'));
					break;
				case 'svrurl':
				case 'svrusername':
					$omstruct[$key] = array('type'=>'text', 'maxlength'=>256);
					break;
				case 'svrappname':
					$omstruct[$key] = array('type'=>'text', 'maxlength'=>32);
					break;
				case 'svrport':
					$omstruct[$key] = array('type'=>'integer', 'maxlength'=>5);
					break;
				case 'svrpassword':
					$omstruct[$key] = array('type'=>'password', 'maxlength'=>256);
					break;
				default:
					$omstruct[$key] = array("type"=>"bool");
			}
		}
		return $omstruct;
	}
	

	public function getOmConf($a_val) {
		return $this->omconf[$a_val];
	}
	public function setOmConf($a_item,$a_val) {
		$this->omconf[$a_item] = $a_val;
	}
	

	/**
	* 
	*/
	public function save()
	{
		global $ilDB;
		$a_data = array('id' => array('integer', 1));
		foreach($this->getOmConfStruct() as $key => $item)  {
			$dbtype = "integer";
			switch ($item["type"]){
				case "text":
				case "a_text":
				case "password":
					$dbtype = "text";
					break;
				case "bool":
					$dbtype = "integer";
					break;
				default:
					$dbtype = $item["type"];
			}
			$a_data[strtolower($key)] = array($dbtype,$this->getOmConf($key));
		}
		// check if data exisits decide to update or insert
		$result = $ilDB->query("SELECT * FROM rep_robj_xomv_conn");
		$num = $ilDB->numRows($result);
		if($num == 0){
			$ilDB->insert('rep_robj_xomv_conn', $a_data);
		} else {
			$ilDB->update('rep_robj_xomv_conn', $a_data, array('id' => array('integer', 1))); //not necessary now
		}
	}

	/**
	*
	*/
	public function read()
	{
		global $ilDB;
		$result = $ilDB->query("SELECT * FROM rep_robj_xomv_conn");
		while ($record = $ilDB->fetchAssoc($result)) {
			foreach($this->getOmConfStruct() as $key => $value)  {
				$this->setOmConf($key,$record[strtolower($key)]);
			}
		}
    }
}


?>
