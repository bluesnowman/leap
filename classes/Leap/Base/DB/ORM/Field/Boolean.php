<?php

/**
 * Copyright © 2011–2013 Spadefoot Team.
 *
 * Unless otherwise noted, LEAP is licensed under the Apache License,
 * Version 2.0 (the "License"); you may not use this file except in
 * compliance with the License. You may obtain a copy of the License
 * at:
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

/**
 * This class represents a "boolean" field in a database table.
 *
 * @package Leap
 * @category ORM
 * @version 2012-12-05
 *
 * @abstract
 */
abstract class Base\DB\ORM\Field\Boolean extends DB\ORM\Field {

	/**
	 * This constructor initializes the class.
	 *
	 * @access public
	 * @param DB\ORM\Model $model                   a reference to the implementing model
	 * @param array $metadata                       the field's metadata
	 * @throws Throwable\Validation\Exception       indicates that the specified value does
	 *                                              not validate
	 */
	public function __construct(DB\ORM\Model $model, Array $metadata = array()) {
		parent::__construct($model, 'boolean');

		if (isset($metadata['savable'])) {
			$this->metadata['savable'] = (bool) $metadata['savable'];
		}

		if (isset($metadata['nullable'])) {
			$this->metadata['nullable'] = (bool) $metadata['nullable'];
		}

		if (isset($metadata['filter'])) {
			$this->metadata['filter'] = (string) $metadata['filter'];
		}

		if (isset($metadata['callback'])) {
			$this->metadata['callback'] = (string) $metadata['callback'];
		}

		$this->metadata['control'] = 'checkbox';

		if (isset($metadata['label'])) {
			$this->metadata['label'] = (string) $metadata['label'];
		}

		if (isset($metadata['default'])) {
			$default = $metadata['default'];
		}
		else if ( ! $this->metadata['nullable']) {
			$default = FALSE;
		}
		else {
			$default = NULL;
		}

		if ( ! ($default instanceof DB\SQL\Expression)) {
			if ($default !== NULL) {
				if (is_string($default)) {
					$default = strtolower($default);
					if (in_array($default, array('true', 't', 'yes', 'y', '1'))) {
						$default = TRUE;
					}
					else if (in_array($default, array('false', 'f', 'no', 'n', '0'))) {
						$default = FALSE;
					}
				}
				settype($default, $this->metadata['type']);
			}
			if ( ! $this->validate($default)) {
				throw new Throwable\Validation\Exception('Message: Unable to set default value for field. Reason: Value :value failed to pass validation constraints.', array(':value' => $default));
			}
		}

		$this->metadata['default'] = $default;
		$this->value = $default;
	}

	/**
	 * This function sets the value for the specified key.
	 *
	 * @access public
	 * @override
	 * @param string $key                           the name of the property
	 * @param mixed $value                          the value of the property
	 * @throws Throwable\Validation\Exception       indicates that the specified value does
	 *                                              not validate
	 * @throws Throwable\InvalidProperty\Exception  indicates that the specified property is
	 *                                              either inaccessible or undefined
	 */
	public function __set($key, $value) {
		switch ($key) {
			case 'value':
				if ( ! ($value instanceof DB\SQL\Expression)) {
					if ($value !== NULL) {
						if (is_string($value)) {
							$value = strtolower($value);
							if (in_array($value, array('true', 't', 'yes', 'y', '1'))) {
								$value = TRUE;
							}
							else if (in_array($value, array('false', 'f', 'no', 'n', '0'))) {
								$value = FALSE;
							}
						}
						settype($value, $this->metadata['type']);
						if ( ! $this->validate($value)) {
							throw new Throwable\Validation\Exception('Message: Unable to set the specified property. Reason: Value :value failed to pass validation constraints.', array(':value' => $value));
						}
					}
					else if ( ! $this->metadata['nullable']) {
						$value = $this->metadata['default'];
					}
				}
				if (isset($this->metadata['callback']) AND ! $this->model->{$this->metadata['callback']}($value)) {
					throw new Throwable\Validation\Exception('Message: Unable to set the specified property. Reason: Value :value failed to pass validation constraints.', array(':value' => $value));
				}
				$this->metadata['modified'] = TRUE;
				$this->value = $value;
			break;
			case 'modified':
				$this->metadata['modified'] = (bool) $value;
			break;
			default:
				throw new Throwable\InvalidProperty\Exception('Message: Unable to set the specified property. Reason: Property :key is either inaccessible or undefined.', array(':key' => $key, ':value' => $value));
			break;
		}
	}

}