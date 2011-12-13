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
 * This class builds a PostgreSQL insert statement.
 *
 * @package Leap
 * @category PostgreSQL
 * @version 2011-12-12
 *
 * @see http://www.postgresql.org/docs/8.3/interactive/sql-insert.html
 *
 * @abstract
 */
abstract class Base_DB_PostgreSQL_Insert_Builder extends DB_SQL_Insert_Builder {

	/**
	 * This function returns the SQL statement.
	 *
	 * @access public
	 * @param boolean $terminated           whether to add a semi-colon to the end
	 *                                      of the statement
	 * @return string                       the SQL statement
	 */
	public function statement($terminated = TRUE) {
		$sql = "INSERT INTO {$this->data['into']}";

		if ( ! empty($this->data['column'])) {
			$columns = implode(', ', array_keys($this->data['column']));
			$values = implode(', ', array_values($this->data['column']));
			$sql .= " ({$columns}) VALUES ({$values})";
		}

		if ($terminated) {
			$sql .= ';';
		}

		return $sql;
	}

}
?>