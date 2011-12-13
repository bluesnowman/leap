<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Copyright 2011 Spadefoot
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
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
 * This class handles a PDO DB2 connection.
 *
 * @package Leap
 * @category DB2
 * @version 2011-12-12
 *
 * @see http://www.php.net/manual/en/ref.pdo-ibm.connection.php
 *
 * @abstract
 */
abstract class Base_DB_DB2_Connection_PDO extends DB_SQL_Connection_PDO {

	/**
	 * This function allows for the ability to open a connection using
	 * the configurations provided.
	 *
	 * @access public
	 * @throws Kohana_Database_Exception        indicates that there is problem with
	 *                                          the database connection
	 *
	 * @see http://www.php.net/manual/en/ref.pdo-ibm.connection.php
	 */
	public function open() {
		if ( ! $this->is_connected()) {
			$connection_string  = 'ibm:';
			$connection_string .= 'DRIVER={IBM DB2 ODBC DRIVER};';
			$connection_string .= 'DATABASE=' . $this->data_source->get_database() . ';';
			$connection_string .= 'HOSTNAME=' . $this->data_source->get_host_server() . ';';
			$connection_string .= 'PORT=' . $this->data_source->get_port() . ';';
			$connection_string .= 'PROTOCOL=TCPIP;';
			$username = $this->data_source->get_username();
			$password = $this->data_source->get_password();
			try {
				$this->connection = new PDO($connection_string, $username, $password);
			}
			catch (PDOException $ex) {
				$this->error = 'Message: Failed to establish connection. Reason: ' . $ex->getMessage();
				throw new Kohana_Database_Exception($this->error, array(':source' => $connection_string, ':username' => $username, 'password' => $password));
			}
			$this->link_id = DB_SQL_Connection_PDO::$counter++;
		}
	}

	/**
	 * This function escapes a string to be used in an SQL statement.
	 *
	 * @access public
	 * @param string $string                    the string to be escaped
	 * @return string                           the escaped string
	 */
	public function escape_string($string) {
		// TODO improve this escaping method
		$unpacked = unpack('H*hex', $string);
		$string = '0x' . $unpacked['hex'];
		return $string;
	}

}
?>