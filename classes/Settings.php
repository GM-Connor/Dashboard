<?php


/**
 * Class to handle settings
 */

class Settings {

	/**
	* Sets the object's properties using the values in the supplied array
	*
	* @param assoc The property values
	*/

	public function __construct(  ) {
		$this->data_dir = "data";
		$this->settings_file = $this->data_dir . "/settings.json";
		$this->list = array();
		$this->sep = '|';

		$this->updateSettings();
	}


	/**
	* Makes empty settings file
	*/

	private function makeSettingsFile() {

		if (!file_exists($this->data_dir))
			mkdir($this->data_dir, 0777, true);

		file_put_contents($this->settings_file, json_encode(array()));
	}


	/**
	* Read settings file
	*/

	private function getSettings() {

		if (!file_exists($this->settings_file))
			$this->makeSettingsFile();

		$settings_file_contents = file_get_contents($this->settings_file);

		// Parse JSON
		$settings = json_decode($settings_file_contents);
		
		return $settings;

	}

	/**
	* Update class settings variable
	*/

	private function updateSettings() {

		$this->list = $this->getSettings();

	}

	/**
	* Write settings file
	*/

	private function putSettings($arr) {

		if (!file_exists($this->settings_file))
			$this->makeSettingsFile();

		$settings_file_contents = file_put_contents($this->settings_file, json_encode($arr));

	}

	/**
	* Add setting
	*/

	public function addSetting($name, $value) {

		$setting = array(trim($name), trim(slugify($name, '_')), trim($value));

		if (is_array($this->list)) {
			array_push($this->list, $setting);
			$new_list = $this->list;
		}
		else
			$new_list = array($setting);

		$this->putSettings($new_list);

		$this->updateSettings();

	}

	/**
	* Remove setting
	*/

	public function removeSetting($name, $value='') {

		$new_list = array();

		foreach ($this->list as $setting) {
			if (!(($setting[1] == trim(slugify($name, '_'))) && ($value == '' || $setting[2] == $value))) {
				array_push($new_list, $setting);
			}
		}

		$this->putSettings($new_list);

		$this->updateSettings();

	}

	/**
	* Edit setting
	*/

	public function editSetting($name, $value, $old_value='') {

		$new_list = array();

		foreach ($this->list as $setting) {
			if ((($setting[1] == trim(slugify($name, '_'))) && ($old_value == '' || $setting[2] == $old_value))) {
				$setting[2] = trim($value);
			}
			array_push($new_list, $setting);
		}

		$this->putSettings($new_list);

		$this->updateSettings();

	}

	/**
	* Get setting value
	*/

	public function getSettingValue($setting_name) {

		$values = array();

		foreach ($this->list as $setting) {
			if ($setting[1] == trim(slugify($setting_name, '_'))) {
				array_push($values, $setting[2]);
			}
		}

		return $values;

	}

}

?>