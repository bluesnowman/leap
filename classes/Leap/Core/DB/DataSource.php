<?php

/**
 * Copyright © 2011–2015 Spadefoot Team.
 *
 * Unless otherwise noted, Leap is licensed under the Apache License,
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

namespace Leap\Core\DB {

	/**
	 * This class wraps the connection's configurations.
	 *
	 * @access public
	 * @class
	 * @package Leap\Core\DB
	 * @version 2015-08-23
	 */
	class DataSource extends \Leap\Core\Object {

		/**
		 * This constant represents a master instance of a database.
		 *
		 * @access public
		 * @const integer
		 */
		const MASTER_INSTANCE = 0;

		/**
		 * This constant represents a slave instance of a database.
		 *
		 * @access public
		 * @const integer
		 */
		const SLAVE_INSTANCE = 1;

		/**
		 * This variable stores the settings for the data source.
		 *
		 * @access protected
		 * @var array
		 */
		protected $settings;

		/**
		 * This method loads the configurations.
		 *
		 * @access public
		 * @param mixed $config                                     the data source configurations
		 * @throws \Leap\Core\Throwable\InvalidArgument\Exception   indicates a data type mismatch
		 * @throws \Leap\Core\Throwable\InvalidProperty\Exception   indicates that the database group is undefined
		 */
		public function __construct($config) {
			if (empty($config)) {
				$id = 'Database.default';
				if (($config = \Leap\Core\Config::query($id)) === NULL) {
					throw new \Leap\Core\Throwable\InvalidProperty\Exception('Message: Unable to load data source. Reason: Database group :id is undefined.', array(':id' => $id));
				}
				$this->init($config, $id);
			}
			else if (is_string($config)) {
				$id = 'Database.' . $config;
				if (($config = \Leap\Core\Config::query($id)) === NULL) {
					throw new \Leap\Core\Throwable\InvalidProperty\Exception('Message: Unable to load data source. Reason: Database group :id is undefined.', array(':id' => $id));
				}
				$this->init($config, $id);
			}
			else if (is_array($config)) {
				$this->init($config);
			}
			else if (is_object($config) AND ($config instanceof \Leap\Core\DB\DataSource)) {
				$this->settings = $config->settings;
			}
			else {
				throw new \Leap\Core\Throwable\InvalidArgument\Exception('Message: Unable to load data source. Reason: Data type :type is mismatched.', array(':type' => gettype($config)));
			}
		}

		/**
		 * This method releases any internal references to an object.
		 *
		 * @access public
		 */
		public function __destruct() {
			parent::__destruct();
			unset($this->settings);
		}

		/**
		 * This method returns the value associated with the specified property.
		 *
		 * @access public
		 * @override
		 * @param string $name                                      the name of the property
		 * @return mixed                                            the value of the property
		 * @throws \Leap\Core\Throwable\InvalidProperty\Exception   indicates that the specified property is
		 *                                                          either inaccessible or undefined
		 */
		public function __get($name) {
			switch ($name) {
				case 'cache':
				case 'charset':
				case 'database':
				case 'dialect':
				case 'driver':
				case 'hostname':
				case 'id':
				case 'password':
				case 'port':
				case 'type':
				case 'username':
				case 'results':
				case 'role':
					return $this->settings[$name];
				default:
					throw new \Leap\Core\Throwable\InvalidProperty\Exception('Message: Unable to get the specified property. Reason: Property :key is either inaccessible or undefined.', array(':key' => $name));
			}
		}

		/**
		 * This method determines whether a specific property has been set.
		 *
		 * @access public
		 * @override
		 * @param string $name                                      the name of the property
		 * @return boolean                                          indicates whether the specified property
		 *                                                          has been set
		 */
		public function __isset($name) {
			if (isset($this->settings[$name]) AND ($name != 'persistent')) {
				return (FALSE === empty($this->settings[$name]));
			}
			return FALSE;
		}

		/**
		 * This method handles the initialization of the data source's settings.
		 *
		 * @access protected
		 * @param array $settings                                   the settings to be used
		 * @param string $id                                        the data source's id
		 */
		protected function init($settings, $id = NULL) {
			$this->settings = array();

			if ($id === NULL) {
				// TODO Verify that config id does not already exist in the "database.php" config file.
				$this->settings['id'] = (isset($settings['id']))
					? (string) $settings['id']
					: 'unique_id.' . uniqid();
			}
			else {
				$this->settings['id'] = (string) $id;
			}

			$cache = array();
			$cache['enabled'] = (isset($settings['caching'])) ? (bool) $settings['caching'] : FALSE;
			$cache['lifetime'] = (class_exists('\\Kohana')) ? \Kohana::$cache_life : 60;
			$cache['force'] = FALSE;
			$this->settings['cache'] = (object) $cache;

			$this->settings['charset'] = (isset($settings['charset']))
				? (string) str_replace('-', '', strtolower($settings['charset'])) // e.g. utf8
				: '';

			$this->settings['database'] = (isset($settings['connection']['database']))
				? (string) $settings['connection']['database']
				: '';

			$this->settings['dialect'] = (isset($settings['dialect']))
				? (string) $settings['dialect']
				: 'MySQL';

			$this->settings['driver'] = (isset($settings['driver']))
				? (string) $settings['driver']
				: 'Standard';

			$this->settings['hostname'] = (isset($settings['connection']['hostname']))
				? (string) $settings['connection']['hostname']
				: '';

			$this->settings['persistent'] = (isset($settings['connection']['persistent']))
				? (bool) $settings['connection']['persistent']
				: FALSE;

			$this->settings['password'] = (isset($settings['connection']['password']))
				? (string) $settings['connection']['password']
				: '';

			$this->settings['port'] = (isset($settings['connection']['port']))
				? (string) $settings['connection']['port']
				: '';

			$this->settings['type'] = (isset($settings['type']))
				? (string) $settings['type']
				: 'SQL';

			$this->settings['username'] = (isset($settings['connection']['username']))
				? (string) $settings['connection']['username']
				: '';

			$this->settings['results'] = (isset($settings['results']))
				? (array) $settings['results']
				: array();

			$this->settings['role'] = (isset($settings['connection']['role']))
				? (string) $settings['connection']['role']
				: '';
		}

		/**
		 * This method determines whether the connection is persistent.
		 *
		 * @access public
		 * @return boolean                                          whether the connection is persistent
		 */
		public function is_persistent() {
			return $this->settings['persistent'];
		}

		////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

		/**
		 * This variable stores an array of singleton instances of this class.
		 *
		 * @access protected
		 * @static
		 * @var array
		 */
		protected static $instances = array();

		/**
		 * This method returns a singleton instance of this class.
		 *
		 * @access public
		 * @static
		 * @param mixed $config                                     the data source configurations
		 * @return \Leap\Core\DB\DataSource                         a singleton instance of this class
		 */
		public static function instance($config = 'default') {
			if (is_string($config)) {
				if ( ! isset(static::$instances[$config])) {
					static::$instances[$config] = new \Leap\Core\DB\DataSource($config);
				}
				return static::$instances[$config];
			}
			else if (is_object($config) AND ($config instanceof \Leap\Core\DB\DataSource)) {
				$id = $config->id;
				if ( ! isset(static::$instances[$id])) {
					static::$instances[$id] = $config;
				}
				return $config;
			}
			else if (is_array($config) AND isset($config['id'])) {
				$id = $config['id'];
				if ( ! isset(static::$instances[$id])) {
					static::$instances[$id] = new \Leap\Core\DB\DataSource($config);
				}
				return static::$instances[$id];
			}
			else {
				$data_source = new \Leap\Core\DB\DataSource($config);
				static::$instances[$data_source->id] = $data_source;
				return $data_source;
			}
		}

	}

}