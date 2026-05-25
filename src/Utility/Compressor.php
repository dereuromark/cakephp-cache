<?php

namespace Cache\Utility;

class Compressor {

	/**
	 * @param string $content
	 * @return string
	 */
	public function compress($content) {
		// Removes HTML comments (not containing IE conditional comments).
		$content = (string)preg_replace_callback('/<!--([\\s\\S]*?)-->/', $this->_commentIgnore(...), $content);

		// Remove whitespace
		$content = (string)preg_replace('/[\s]+/mu', ' ', $content);

		// Trim each line.
		$content = (string)preg_replace('/^\\s+|\\s+$/m', '', $content);

		return $content;
	}

	/**
	 * @param array $m
	 * @return string
	 */
	protected function _commentIgnore(array $m) {
		return (str_starts_with((string)$m[1], '[') || str_contains((string)$m[1], '<![')) ? $m[0] : '';
	}

}
