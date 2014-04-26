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

namespace Leap\Core\DB\SQL\Delete {

	/**
	 * This class builds an SQL delete statement.
	 *
	 * @abstract
	 * @access public
	 * @class
	 * @package Leap\Core\DB\SQL\Delete
	 * @version 2014-01-26
	 */
	abstract class Builder extends \Leap\Core\DB\SQL\Builder {

		/**
		 * This constructor instantiates this class using the specified data source.
		 *
		 * @access public
		 * @param \Leap\Core\DB\DataSource $data_source     the data source to be used
		 */
		public function __construct(\Leap\Core\DB\DataSource $data_source) {
			$this->dialect = $data_source->dialect;
			$precompiler = '\\Leap\\Plugin\\DB\\' . $this->dialect . '\\Precompiler';
			$this->precompiler = new $precompiler($data_source);
			$this->reset();
		}

		/**
		 * This method sets which table will be modified.
		 *
		 * @access public
		 * @param string $table                             the database table to be modified
		 * @return \Leap\Core\DB\SQL\Delete\Builder         a reference to the current instance
		 */
		public function from($table) {
			$this->data['from'] = $this->precompiler->prepare_identifier($table);
			return $this;
		}

		/**
		 * This method sets a "limit" constraint on the statement.
		 *
		 * @access public
		 * @param integer $limit                            the "limit" constraint
		 * @return \Leap\Core\DB\SQL\Delete\Builder         a reference to the current instance
		 */
		public function limit($limit) {
			$this->data['limit'] = $this->precompiler->prepare_natural($limit);
			return $this;
		}

		/**
		 * This method sets an "offset" constraint on the statement.
		 *
		 * @access public
		 * @param integer $offset                           the "offset" constraint
		 * @return \Leap\Core\DB\SQL\Delete\Builder         a reference to the current instance
		 */
		public function offset($offset) {
			$this->data['offset'] = $this->precompiler->prepare_natural($offset);
			return $this;
		}

		/**
		 * This method sets how a column will be sorted.
		 *
		 * @access public
		 * @param string $column                            the column to be sorted
		 * @param string $ordering                          the ordering token that signals whether the
		 *                                                  column will sorted either in ascending or
		 *                                                  descending order
		 * @param string $nulls                             the weight to be given to null values
		 * @return \Leap\Core\DB\SQL\Delete\Builder         a reference to the current instance
		 */
		public function order_by($column, $ordering = 'ASC', $nulls = 'DEFAULT') {
			$this->data['order_by'][] = $this->precompiler->prepare_ordering($column, $ordering, $nulls);
			return $this;
		}

		/**
		 * This method resets the current builder.
		 *
		 * @access public
		 * @return \Leap\Core\DB\SQL\Delete\Builder        a reference to the current instance
		 */
		public function reset() {
			$this->data = array(
				'from' => NULL,
				'limit' => 0,
				'offset' => 0,
				'order_by' => array(),
				'where' => array(),
			);
			return $this;
		}

		/**
		 * This method adds a "where" constraint.
		 *
		 * @access public
		 * @param string $column                            the column to be constrained
		 * @param string $operator                          the operator to be used
		 * @param string $value                             the value the column is constrained with
		 * @param string $connector                         the connector to be used
		 * @return \Leap\Core\DB\SQL\Delete\Builder         a reference to the current instance
		 * @throws \Leap\Core\Throwable\SQL\Exception       indicates an invalid SQL build instruction
		 */
		public function where($column, $operator, $value, $connector = 'AND') {
			$operator = $this->precompiler->prepare_operator($operator, 'COMPARISON');
			if (($operator == \Leap\Core\DB\SQL\Operator::_BETWEEN_) OR ($operator == \Leap\Core\DB\SQL\Operator::_NOT_BETWEEN_)) {
				if ( ! is_array($value)) {
					throw new \Leap\Core\Throwable\SQL\Exception('Message: Invalid build instruction. Reason: Operator requires the value to be declared as an array.', array(':column' => $column, ':operator' => $operator, ':value' => $value, ':connector' => $connector));
				}
				$column = $this->precompiler->prepare_identifier($column);
				$value0 = $this->precompiler->prepare_value($value[0]);
				$value1 = $this->precompiler->prepare_value($value[1]);
				$connector = $this->precompiler->prepare_connector($connector);
				$this->data['where'][] = array($connector, "{$column} {$operator} {$value0} AND {$value1}");
			}
			else {
				if ((($operator == \Leap\Core\DB\SQL\Operator::_IN_) OR ($operator == \Leap\Core\DB\SQL\Operator::_NOT_IN_)) AND ! is_array($value)) {
					throw new \Leap\Core\Throwable\SQL\Exception('Message: Invalid build instruction. Reason: Operator requires the value to be declared as an array.', array(':column' => $column, ':operator' => $operator, ':value' => $value, ':connector' => $connector));
				}
				if ($value === NULL) {
					switch ($operator) {
						case \Leap\Core\DB\SQL\Operator::_EQUAL_TO_:
							$operator = \Leap\Core\DB\SQL\Operator::_IS_;
						break;
						case \Leap\Core\DB\SQL\Operator::_NOT_EQUIVALENT_:
							$operator = \Leap\Core\DB\SQL\Operator::_IS_NOT_;
						break;
					}
				}
				$column = $this->precompiler->prepare_identifier($column);
				$escape = (in_array($operator, array(\Leap\Core\DB\SQL\Operator::_LIKE_, \Leap\Core\DB\SQL\Operator::_NOT_LIKE_)))
					? '\\\\'
					: NULL;
				$value = $this->precompiler->prepare_value($value, $escape);
				$connector = $this->precompiler->prepare_connector($connector);
				$this->data['where'][] = array($connector, "{$column} {$operator} {$value}");
			}
			return $this;
		}

		/**
		 * This method either opens or closes a "where" group.
		 *
		 * @access public
		 * @param string $parenthesis                       the parenthesis to be used
		 * @param string $connector                         the connector to be used
		 * @return \Leap\Core\DB\SQL\Delete\Builder                    a reference to the current instance
		 */
		public function where_block($parenthesis, $connector = 'AND') {
			$parenthesis = $this->precompiler->prepare_parenthesis($parenthesis);
			$connector = $this->precompiler->prepare_connector($connector);
			$this->data['where'][] = array($connector, $parenthesis);
			return $this;
		}

	}

}