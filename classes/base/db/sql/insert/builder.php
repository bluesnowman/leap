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
 * This class builds an SQL insert statement.
 *
 * @package Leap
 * @category SQL
 * @version 2011-12-12
 *
 * @abstract
 */
abstract class Base_DB_SQL_Insert_Builder extends DB_SQL_Builder {

	/**
	 * This variable stores the name of the SQL dialect being used.
	 *
	 * @access protected
	 * @var string
	 */
	protected $dialect = NULL;

	/**
	 * This variable stores a reference to the helper class that implements the expression
	 * interface.
	 *
	 * @access protected
	 * @var DB_SQL_Expression_Interface
	 */
	protected $helper = NULL;

	/**
	 * This variable stores the build data for the SQL statement.
	 *
	 * @access protected
	 * @var array
	 */
	protected $data = NULL;

	/**
	 * This constructor instantiates this class using the specified data source.
	 *
	 * @access public
	 * @param DB_DataSource $source             the data source to be used
	 */
	public function __construct(DB_DataSource $source) {
		$this->dialect = $source->get_resource_type();
		$helper = 'DB_' . $this->dialect . '_Expression';
		$this->helper = new $helper($source);
		$this->data = array();
		$this->data['into'] = NULL;
		$this->data['column'] = array();
	}

	/**
	 * This function sets which table will be modified.
	 *
	 * @access public
	 * @param string $table                     the database table to be modified
	 * @return DB_SQL_Insert_Builder            a reference to the current instance
	 */
	public function into($table) {
		$table = $this->helper->prepare_identifier($table);
		$this->data['into'] = $table;
		return $this;
	}

	/**
	 * This function sets the associated value with the specified column.
	 *
	 * @access public
	 * @param string $column                    the column to be set
	 * @param string $value                     the value to be set
	 * @return DB_SQL_Insert_Builder            a reference to the current instance
	 */
	public function column($column, $value) {
		$column = $this->helper->prepare_identifier($column);
		$value = $this->helper->prepare_value($value);
		$this->data['column'][$column] = $value;
		return $this;
	}

}
?>