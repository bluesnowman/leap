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
	 * This class handles a standard MySQL connection.
	 *
	 * @access public
	 * @class
	 * @package Leap\Plugin\DB\MySQL\Connection
	 * @version 2014-04-30
	 *
	 * @see http://www.php.net/manual/en/book.mysql.php
	 */
	class Standard extends \Leap\Core\DB\SQL\Connection\Standard {

		/**
		 * This destructor ensures that the connection is closed.
		 *
		 * @access public
		 * @override
		 */
		public function __destruct() {
			if (is_resource($this->resource)) {
				@mysql_close($this->resource);
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
		 * @see http://dev.mysql.com/doc/refman/5.0/en/commit.html
		 * @see http://php.net/manual/en/function.mysql-query.php
		 */
		public function begin_transaction() {
			$this->execute(new \Leap\Core\DB\SQL\Command('START TRANSACTION;'));
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
				if ( ! @mysql_close($this->resource)) {
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
		 * @see http://dev.mysql.com/doc/refman/5.0/en/commit.html
		 * @see http://php.net/manual/en/function.mysql-query.php
		 */
		public function commit() {
			$this->execute(new \Leap\Core\DB\SQL\Command('COMMIT;'));
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
			$command = @mysql_query($sql->text, $this->resource);
			if ($command === FALSE) {
				throw new \Leap\Core\Throwable\SQL\Exception('Message: Failed to execute SQL statement. Reason: :reason', array(':reason' => @mysql_error($this->resource)));
			}
			$this->sql = $sql;
			@mysql_free_result($command);
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
				$id = @mysql_insert_id($this->resource);
				if ($id === FALSE) {
					throw new \Leap\Core\Throwable\SQL\Exception('Message: Failed to fetch the last insert id. Reason: :reason', array(':reason' => @mysql_error($this->resource)));
				}
				return $id;
			}
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
				$username = $this->data_source->username;
				$password = $this->data_source->password;
				$port = $this->data_source->port;
				$host = ($port ? "{$host}:{$port}" : $host);
				$this->resource = ($this->data_source->is_persistent())
					? @mysql_pconnect($host, $username, $password)
					: @mysql_connect($host, $username, $password, TRUE);
				if ($this->resource === FALSE) {
					throw new \Leap\Core\Throwable\Database\Exception('Message: Failed to establish connection. Reason: :reason', array(':reason' => @mysql_error()));
				}
				if ( ! @mysql_select_db($this->data_source->database, $this->resource)) {
					throw new \Leap\Core\Throwable\Database\Exception('Message: Failed to connect to database. Reason: :reason', array(':reason' => @mysql_error($this->resource)));
				}
				if ( ! empty($this->data_source->charset) AND ! @mysql_set_charset(strtolower($this->data_source->charset), $this->resource)) {
					throw new \Leap\Core\Throwable\Database\Exception('Message: Failed to set character set. Reason: :reason', array(':reason' => @mysql_error($this->resource)));
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

			$string = "'" . mysql_real_escape_string($string, $this->resource) . "'";

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
		 * @see http://php.net/manual/en/function.mysql-query.php
		 */
		public function rollback() {
			$this->execute(new \Leap\Core\DB\SQL\Command('ROLLBACK;'));
		}

	}

}