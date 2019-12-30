<?php

namespace Cache\Utility;

class Compressor {

	/**
	 * @param string $content
	 * @return string
	 */
	public function compress($content) {
		// Removes HTML comments (not containing IE conditional comments).
		$content = preg_replace_callback('/<!--([\\s\\S]*?)-->/', [$this, '_commentIgnore'], $content);

		// Remove whitespace
		$content = preg_replace('/[\s]+/mu', ' ', $content);

		// Trim each line.
		$content = preg_replace('/^\\s+|\\s+$/m', '', $content);

		return $content;
	}

	/**
	 * @param array $m
	 * @return string
	 */
	protected function _commentIgnore(array $m) {
		return (strpos($m[1], '[') === 0 || strpos($m[1], '<![') !== false) ? $m[0] : '';
	}

}
