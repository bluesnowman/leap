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

namespace Leap\Plugin\DB\SQLite\Connection {

	/**
	 * This class handles a standard SQLite connection.
	 *
	 * @access public
	 * @class
	 * @package Leap\Plugin\DB\SQLite\Connection
	 * @version 2015-08-31
	 *
	 * @see http://www.php.net/manual/en/ref.sqlite.php
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
				@sqlite_close($this->resource);
			}
			parent::__destruct();
		}

		/**
		 * This method begins a transaction.
		 *
		 * @access public
		 * @override
		 * @throws \Leap\Core\Throwable\SQL\Exception               indicates that the executed
		 *                                                          statement failed
		 *
		 * @see http://www.sqlite.org/lang_transaction.html
		 * @see http://en.wikibooks.org/wiki/SQL_Dialects_Reference/Transactions
		 */
		public function begin_transaction() {
			$this->execute(new \Leap\Core\DB\SQL\Command('BEGIN IMMEDIATE TRANSACTION;'));
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
				if ( ! @sqlite_close($this->resource)) {
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
		 * @see http://www.sqlite.org/lang_transaction.html
		 * @see http://en.wikibooks.org/wiki/SQL_Dialects_Reference/Transactions
		 */
		public function commit() {
			$this->execute(new \Leap\Core\DB\SQL\Command('COMMIT TRANSACTION;'));
		}

		/**
		 * This method processes an SQL command that will NOT return data.
		 *
		 * @access public
		 * @override
		 * @param \Leap\Core\DB\SQL\Command $command                the SQL command to be queried
		 * @throws \Leap\Core\Throwable\SQL\Exception               indicates that the executed
		 *                                                          statement failed
		 */
		public function execute(\Leap\Core\DB\SQL\Command $command) {
			if ( ! $this->is_connected()) {
				throw new \Leap\Core\Throwable\SQL\Exception('Message: Failed to execute SQL command. Reason: Unable to find connection.');
			}
			$error = NULL;
			$handle = @sqlite_exec($this->resource, $command->text, $error);
			if ($handle === FALSE) {
				throw new \Leap\Core\Throwable\SQL\Exception('Message: Failed to execute SQL command. Reason: :reason', array(':reason' => $error));
			}
			$this->command = $command;
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
				$command = $this->command;
				$precompiler = \Leap\Core\DB\SQL::precompiler($this->data_source);
				$table = $precompiler->prepare_identifier($table);
				$column = $precompiler->prepare_identifier($column);
				$id = (int) $this->query(new \Leap\Core\DB\SQL\Command("SELECT MAX({$column}) AS \"id\" FROM {$table};"))->get('id', 0);
				$this->command = $command;
				return $id;
			}
			else {
				$id = @sqlite_last_insert_rowid($this->resource);
				if ($id === FALSE) {
					throw new \Leap\Core\Throwable\SQL\Exception('Message: Failed to fetch the last insert id. Reason: :reason', array(':reason' => sqlite_error_string(sqlite_last_error($this->resource))));
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
		 *
		 * @see http://www.sqlite.org/pragma.html#pragma_encoding
		 * @see http://stackoverflow.com/questions/263056/how-to-change-character-encoding-of-a-pdo-sqlite-connection-in-php
		 */
		public function open() {
			if ( ! $this->is_connected()) {
				$connection_string = $this->data_source->database;
				$error = NULL;
				$this->resource = ($this->data_source->is_persistent())
					? @sqlite_popen($connection_string, 0666, $error)
					: @sqlite_open($connection_string, 0666, $error);
				if ($this->resource === FALSE) {
					throw new \Leap\Core\Throwable\Database\Exception('Message: Failed to establish connection. Reason: :reason', array(':reason' => $error));
				}
				// "Once an encoding has been set for a database, it cannot be changed."
			}
		}

		/**
		 * This method escapes a string to be used in an SQL command.
		 *
		 * @access public
		 * @override
		 * @param string $string                                    the string to be escaped
		 * @param char $escape                                      the escape character
		 * @return string                                           the quoted string
		 * @throws \Leap\Core\Throwable\SQL\Exception               indicates that no connection could
		 *                                                          be found
		 *
		 * @see http://www.php.net/manual/en/function.sqlite-escape-string.php
		 */
		public function quote($string, $escape = NULL) {
			if ( ! $this->is_connected()) {
				throw new \Leap\Core\Throwable\SQL\Exception('Message: Failed to quote/escape string. Reason: Unable to find connection.');
			}

			$string = "'" . sqlite_escape_string($string) . "'";

			if (is_string($escape) OR ! empty($escape)) {
				$string .= " ESCAPE '{$escape[0]}'";
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
		 * @see http://www.sqlite.org/lang_transaction.html
		 * @see http://en.wikibooks.org/wiki/SQL_Dialects_Reference/Transactions
		 */
		public function rollback() {
			$this->execute(new \Leap\Core\DB\SQL\Command('ROLLBACK TRANSACTION;'));
		}

	}

}