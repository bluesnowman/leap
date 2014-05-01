<?php

/**
 * Copyright © 2011–2014 Spadefoot Team.
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

namespace Leap\Plugin\DB\MySQL\Connection {

	/**
	 * This class handles an improved MySQL connection.
	 *
	 * @access public
	 * @class
	 * @package Leap\Plugin\DB\MySQL\Connection
	 * @version 2014-04-30
	 *
	 * @see http://www.php.net/manual/en/book.mysqli.php
	 */
	class Improved extends \Leap\Core\DB\SQL\Connection\Standard {

		/**
		 * This destructor ensures that the connection is closed.
		 *
		 * @access public
		 * @override
		 */
		public function __destruct() {
			if ($this->resource !== NULL) {
				@mysqli_close($this->resource);
			}
		}

		/**
		 * This method begins a transaction.
		 *
		 * @access public
		 * @override
		 * @throws \Leap\Core\Throwable\SQL\Exception               indicates that the executed
		 *                                                          statement failed
		 *
		 * @see http://www.php.net/manual/en/mysqli.autocommit.php
		 */
		public function begin_transaction() {
			if ( ! $this->is_connected()) {
				throw new \Leap\Core\Throwable\SQL\Exception('Message: Failed to begin SQL transaction. Reason: Unable to find connection.');
			}
			$command = @mysqli_autocommit($this->resource, FALSE);
			if ($command === FALSE) {
				throw new \Leap\Core\Throwable\SQL\Exception('Message: Failed to begin SQL transaction. Reason: :reason', array(':reason' => @mysqli_error($this->resource)));
			}
			$this->sql = new \Leap\Core\DB\SQL\Command('START TRANSACTION;');
		}

		/**
		 * This method closes an open connection.
		 *
		 * @access public
		 * @override
		 * @return boolean                                          whether an open connection was closed
		 */
		public function close() {
			if ($this->is_connected()) {
				if ( ! @mysqli_close($this->resource)) {
					return FALSE;
				}
				$this->resource = NULL;
			}
			return TRUE;
		}

		/**
		 * This method commits a transaction.
		 *
		 * @access public
		 * @override
		 * @throws \Leap\Core\Throwable\SQL\Exception               indicates that the executed
		 *                                                          statement failed
		 *
		 * @see http://www.php.net/manual/en/mysqli.commit.php
		 */
		public function commit() {
			if ( ! $this->is_connected()) {
				throw new \Leap\Core\Throwable\SQL\Exception('Message: Failed to commit SQL transaction. Reason: Unable to find connection.');
			}
			$command = @mysqli_commit($this->resource);
			if ($command === FALSE) {
				throw new \Leap\Core\Throwable\SQL\Exception('Message: Failed to commit SQL transaction. Reason: :reason', array(':reason' => @mysqli_error($this->resource)));
			}
			@mysqli_autocommit($this->resource, TRUE);
			$this->sql = new \Leap\Core\DB\SQL\Command('COMMIT;');
		}

		/**
		 * This method processes an SQL statement that will NOT return data.
		 *
		 * @access public
		 * @override
		 * @param \Leap\Core\DB\SQL\Command $sql                    the SQL statement
		 * @throws \Leap\Core\Throwable\SQL\Exception               indicates that the executed
		 *                                                          statement failed
		 */
		public function execute(\Leap\Core\DB\SQL\Command $sql) {
			if ( ! $this->is_connected()) {
				throw new \Leap\Core\Throwable\SQL\Exception('Message: Failed to execute SQL statement. Reason: Unable to find connection.');
			}
			$command = @mysqli_query($this->resource, $sql->text);
			if ($command === FALSE) {
				throw new \Leap\Core\Throwable\SQL\Exception('Message: Failed to execute SQL statement. Reason: :reason', array(':reason' => @mysqli_error($this->resource)));
			}
			$this->sql = $sql;
			@mysqli_free_result($command);
		}

		/**
		 * This method returns the last insert id.
		 *
		 * @access public
		 * @override
		 * @param string $table                                     the table to be queried
		 * @param string $column                                    the column representing the table's id
		 * @return integer                                          the last insert id
		 * @throws \Leap\Core\Throwable\SQL\Exception               indicates that the query failed
		 */
		public function get_last_insert_id($table = NULL, $column = 'id') {
			if ( ! $this->is_connected()) {
				throw new \Leap\Core\Throwable\SQL\Exception('Message: Failed to fetch the last insert id. Reason: Unable to find connection.');
			}
			if (is_string($table)) {
				$sql = $this->sql;
				$precompiler = \Leap\Core\DB\SQL::precompiler($this->data_source);
				$table = $precompiler->prepare_identifier($table);
				$column = $precompiler->prepare_identifier($column);
				$id = (int) $this->query(new \Leap\Core\DB\SQL\Command("SELECT MAX({$column}) AS `id` FROM {$table};"))->get('id', 0);
				$this->sql = $sql;
				return $id;
			}
			else {
				$id = @mysqli_insert_id($this->resource);
				if ($id === FALSE) {
					throw new \Leap\Core\Throwable\SQL\Exception('Message: Failed to fetch the last insert id. Reason: :reason', array(':reason' => @mysqli_error($this->resource)));
				}
				return $id;
			}
		}

		/**
		 * This method is for determining whether a connection is established.
		 *
		 * @access public
		 * @override
		 * @return boolean                                          whether a connection is established
		 */
		public function is_connected() {
			return ! empty($this->resource);
		}

		/**
		 * This method opens a connection using the data source provided.
		 *
		 * @access public
		 * @override
		 * @throws \Leap\Core\Throwable\Database\Exception          indicates that there is problem with
		 *                                                          opening the connection
		 */
		public function open() {
			if ( ! $this->is_connected()) {
				$host = $this->data_source->host;
				if ($this->data_source->is_persistent()) {
					$host = 'p:' . $host;
				}
				$username = $this->data_source->username;
				$password = $this->data_source->password;
				$database = $this->data_source->database;
				$this->resource = @mysqli_connect($host, $username, $password, $database);
				if ($this->resource === FALSE) {
					throw new \Leap\Core\Throwable\Database\Exception('Message: Failed to establish connection. Reason: :reason', array(':reason' => @mysqli_connect_error()));
				}
				if ( ! empty($this->data_source->charset) AND ! @mysqli_set_charset($this->resource, strtolower($this->data_source->charset))) {
					throw new \Leap\Core\Throwable\Database\Exception('Message: Failed to set character set. Reason: :reason', array(':reason' => @mysqli_error($this->resource)));
				}
			}
		}

		/**
		 * This method escapes a string to be used in an SQL statement.
		 *
		 * @access public
		 * @override
		 * @param string $string                                    the string to be escaped
		 * @param char $escape                                      the escape character
		 * @return string                                           the quoted string
		 * @throws \Leap\Core\Throwable\SQL\Exception               indicates that no connection could
		 *                                                          be found
		 */
		public function quote($string, $escape = NULL) {
			if ( ! $this->is_connected()) {
				throw new \Leap\Core\Throwable\SQL\Exception('Message: Failed to quote/escape string. Reason: Unable to find connection.');
			}

			$string = "'" . mysqli_real_escape_string($this->resource, $string) . "'";

			if (is_string($escape) OR ! empty($escape)) {
				$string .= " ESCAPE '{$escape}'";
			}

			return $string;
		}

		/**
		 * This method rollbacks a transaction.
		 *
		 * @access public
		 * @override
		 * @throws \Leap\Core\Throwable\SQL\Exception               indicates that the executed
		 *                                                          statement failed
		 *
		 * @see http://www.php.net/manual/en/mysqli.rollback.php
		 */
		public function rollback() {
			if ( ! $this->is_connected()) {
				throw new \Leap\Core\Throwable\SQL\Exception('Message: Failed to rollback SQL transaction. Reason: Unable to find connection.');
			}
			$command = @mysqli_rollback($this->resource);
			if ($command === FALSE) {
				throw new \Leap\Core\Throwable\SQL\Exception('Message: Failed to rollback SQL transaction. Reason: :reason', array(':reason' => @mysqli_error($this->resource)));
			}
			@mysqli_autocommit($this->resource, TRUE);
			$this->sql = new \Leap\Core\DB\SQL\Command('ROLLBACK;');
		}

	}

}