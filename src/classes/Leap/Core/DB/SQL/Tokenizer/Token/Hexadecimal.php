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

namespace Leap\Core\DB\SQL\Tokenizer\Token {

	/**
	 * This class represents the rule definition for a "hexadecimal" token, which the tokenizer will use
	 * to tokenize a string.
	 *
	 * @access public
	 * @class
	 * @package Leap\Core\DB\SQL\Tokenizer\Token
	 * @version 2014-05-11
	 */
	class Hexadecimal extends \Leap\Core\DB\SQL\Tokenizer\Token {

		/**
		 * This method return a tuple representing the token discovered.
		 *
		 * @access public
		 * @param string &$statement                                the string to be analyzed
		 * @param integer &$position                                the current position being analyzed
		 * @param integer $strlen                                   the length of the string
		 * @return array                                            a tuple representing the token
		 *                                                          discovered
		 */
		public function process(&$statement, &$position, $strlen) {
			$char = static::char_at($statement, $position, $strlen);
			if ($char == '0') {
				$lookahead = $position + 1;
				$next = static::char_at($statement, $lookahead, $strlen);
				if (($next == 'x') OR ($next == 'X')) {
					do {
						$lookahead++;
						$next = static::char_at($statement, $lookahead, $strlen);
					}
					while (($next >= '0') AND ($next <= '9'));
					$size = $lookahead - $position;
					$token = substr($statement, $position, $size);
					$tuple = array(
						'type' => \Leap\Core\DB\SQL\Tokenizer\TokenType::hexadecimal(),
						'token' => $token,
					);
					$position = $lookahead;
					// var_dump($token);
					return $tuple;
				}
			}
			return null;
		}

	}

}