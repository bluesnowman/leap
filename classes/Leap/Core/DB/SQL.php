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
	 * This class provides a shortcut way to get the appropriate SQL builder class.
	 *
	 * @access public
	 * @class
	 * @package Leap\Core\DB
	 * @version 2014-07-03
	 */
	class SQL extends \Leap\Core\Object {

		/**
		 * This method returns an instance of the \Leap\Core\DB\SQL\Delete\Proxy.
		 *
		 * @access public
		 * @static
		 * @param mixed $config                                     the data source configurations
		 * @return \Leap\Core\DB\SQL\Delete\Proxy                   an instance of the class
		 */
		public static function delete($config = 'default') {
			$data_source = \Leap\Core\DB\DataSource::instance($config);
			$object = new \Leap\Core\DB\SQL\Delete\Proxy($data_source);
			return $object;
		}

		/**
		 * This method will wrap a string so that it can be processed by a query
		 * builder.
		 *
		 * @access public
		 * @static
		 * @param string $expr                                      the raw SQL expression
		 * @param array $params                                     an associated array of parameter
		 *                                                          key/values pairs
		 * @return \Leap\Core\DB\SQL\Expression                     the wrapped expression
		 */
		public static function expr($expr, Array $params = array()) {
			$object = new \Leap\Core\DB\SQL\Expression($expr, $params);
			return $object;
		}

		/**
		 * This method returns an instance of the \Leap\Core\DB\SQL\Insert\Proxy.
		 *
		 * @access public
		 * @static
		 * @param mixed $config                                     the data source configurations
		 * @return \Leap\Core\DB\SQL\Insert\Proxy                   an instance of the class
		 */
		public static function insert($config = 'default') {
			$data_source = \Leap\Core\DB\DataSource::instance($config);
			$object = new \Leap\Core\DB\SQL\Insert\Proxy($data_source);
			return $object;
		}

		/**
		 * This method returns an instance of the appropriate pre-compiler for the
		 * specified data source/config.
		 *
		 * @access public
		 * @static
		 * @param mixed $config                                     the data source configurations
		 * @return \Leap\Core\DB\SQL\Precompiler                    an instance of the pre-compiler
		 */
		public static function precompiler($config = 'default') {
			$data_source = \Leap\Core\DB\DataSource::instance($config);
			$data_type = '\\Leap\\Plugin\\DB\\' . $data_source->dialect . '\\Precompiler';
			$object = new $data_type($data_source);
			return $object;
		}

		/**
		 * This method returns an instance of the appropriate schema resource for the specified
		 * data source/config.
		 *
		 * @access public
		 * @static
		 * @param mixed $config                                     the data source configurations
		 * @return \Leap\Core\DB\Schema                             an instance of the schema resource
		 */
		public static function schema($config = 'default') {
			$data_source = \Leap\Core\DB\DataSource::instance($config);
			$data_type = '\\Leap\\Plugin\\DB\\' . $data_source->dialect . '\\Schema';
			$object = new $data_type($data_source);
			return $object;
		}

		/**
		 * This method returns an instance of the \Leap\Core\DB\SQL\Select\Proxy.
		 *
		 * @access public
		 * @static
		 * @param mixed $config                                     the data source configurations
		 * @param array $columns                                    the columns to be selected
		 * @return \Leap\Core\DB\SQL\Select\Proxy                   an instance of the class
		 */
		public static function select($config = 'default', Array $columns = array()) {
			$data_source = \Leap\Core\DB\DataSource::instance($config);
			$object = new \Leap\Core\DB\SQL\Select\Proxy($data_source, $columns);
			return $object;
		}

		/**
		 * This method returns an instance of the \Leap\Core\DB\SQL\Update\Proxy.
		 *
		 * @access public
		 * @static
		 * @param mixed $config                                     the data source configurations
		 * @return \Leap\Core\DB\SQL\Update\Proxy                   an instance of the class
		 */
		public static function update($config = 'default') {
			$data_source = \Leap\Core\DB\DataSource::instance($config);
			$object = new \Leap\Core\DB\SQL\Update\Proxy($data_source);
			return $object;
		}

	}

}