<?php
/**
 * This file comes from the "Digital License Manager" WordPress plugin.
 * https://darkog.com/p/digital-license-manager/
 *
 * Copyright (C) 2020-present  Darko Gjorgjijoski. All Rights Reserved.
 * Copyright (C) 2020-present  IDEOLOGIX MEDIA DOOEL. All Rights Reserved.
 *
 * Digital License Manager is free software; you can redistribute it
 * and/or modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * Digital License Manager program is distributed in the hope that it
 * will be useful,but WITHOUT ANY WARRANTY; without even the implied
 * warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License v3
 * along with this program;
 *
 * If not, see: https://www.gnu.org/licenses/gpl-3.0.en.html
 *
 * Code written, maintained by Darko Gjorgjijoski (https://darkog.com)
 */

namespace IdeoLogix\DigitalLicenseManager\Utils;

use Exception;
use Spipu\Html2Pdf\Html2Pdf;

/**
 * MultiScriptHtml2Pdf - Extended Html2Pdf class with automatic script detection and font loading
 *
 * This class automatically detects various scripts (Chinese, Arabic, Hebrew, Thai, etc.)
 * in HTML content and loads appropriate fonts for proper PDF rendering.
 *
 */
class MultiScriptHtml2Pdf extends Html2Pdf {

	/**
	 * @var array Detected scripts in the current content
	 */
	private $detectedScripts = [];

	/**
	 * @var string Font directory path
	 */
	private $fontPath;

	/**
	 * @var array Font mapping for different scripts (based on actual TCPDF fonts)
	 */
	private $fontMap = [
		'cjk' => [
			'primary' => ['cid0cs', 'cid0ct', 'cid0jp', 'cid0kr', 'stsongstdlight', 'msungstdlight', 'kozminproregular', 'kozgopromedium', 'hysmyeongjostdmedium', 'dejavusans', 'freesans'],
			'files' => ['cid0cs.php', 'cid0ct.php', 'cid0jp.php', 'cid0kr.php', 'stsongstdlight.php', 'msungstdlight.php', 'kozminproregular.php', 'kozgopromedium.php', 'hysmyeongjostdmedium.php', 'dejavusans.php', 'freesans.php']
		],
		'arabic' => [
			'primary' => ['aealarabiya', 'aefurat', 'dejavusans', 'freesans'],
			'files' => ['aealarabiya.php', 'aefurat.php', 'dejavusans.php', 'freesans.php']
		],
		'hebrew' => [
			'primary' => ['dejavusans', 'freesans', 'freeserif'],
			'files' => ['dejavusans.php', 'freesans.php', 'freeserif.php']
		],
		'thai' => [
			'primary' => ['dejavusans', 'freesans', 'freeserif'],
			'files' => ['dejavusans.php', 'freesans.php', 'freeserif.php']
		],
		'devanagari' => [
			'primary' => ['dejavusans', 'freesans', 'freeserif'],
			'files' => ['dejavusans.php', 'freesans.php', 'freeserif.php']
		],
		'cyrillic' => [
			'primary' => ['dejavusans', 'dejavuserif', 'freesans', 'freeserif', 'times'],
			'files' => ['dejavusans.php', 'dejavuserif.php', 'freesans.php', 'freeserif.php', 'times.php']
		],
		'greek' => [
			'primary' => ['dejavusans', 'dejavuserif', 'freesans', 'freeserif', 'times'],
			'files' => ['dejavusans.php', 'dejavuserif.php', 'freesans.php', 'freeserif.php', 'times.php']
		],
		'armenian' => [
			'primary' => ['dejavusans', 'freesans', 'freeserif'],
			'files' => ['dejavusans.php', 'freesans.php', 'freeserif.php']
		],
		'georgian' => [
			'primary' => ['dejavusans', 'freesans', 'freeserif'],
			'files' => ['dejavusans.php', 'freesans.php', 'freeserif.php']
		],
		'tamil' => [
			'primary' => ['dejavusans', 'freesans'],
			'files' => ['dejavusans.php', 'freesans.php']
		],
		'bengali' => [
			'primary' => ['dejavusans', 'freesans'],
			'files' => ['dejavusans.php', 'freesans.php']
		],
		'gujarati' => [
			'primary' => ['dejavusans', 'freesans'],
			'files' => ['dejavusans.php', 'freesans.php']
		],
		'punjabi' => [
			'primary' => ['dejavusans', 'freesans'],
			'files' => ['dejavusans.php', 'freesans.php']
		],
		'telugu' => [
			'primary' => ['dejavusans', 'freesans'],
			'files' => ['dejavusans.php', 'freesans.php']
		],
		'kannada' => [
			'primary' => ['dejavusans', 'freesans'],
			'files' => ['dejavusans.php', 'freesans.php']
		],
		'malayalam' => [
			'primary' => ['dejavusans', 'freesans'],
			'files' => ['dejavusans.php', 'freesans.php']
		]
	];

	/**
	 * @var bool Enable debug logging
	 */
	private $debugMode = false;

	/**
	 * Constructor
	 *
	 * @param string $orientation Page orientation (P=Portrait, L=Landscape)
	 * @param string $format Page format (A4, A3, Letter, etc.)
	 * @param string $language Document language
	 * @param string $fontPath Custom font directory path
	 * @param bool $debugMode Enable debug logging
	 */
	public function __construct($orientation = 'P', $format = 'A4', $language = 'EN', $fontPath = null, $debugMode = false) {
		parent::__construct($orientation, $format, $language);

		$this->fontPath = $fontPath ?: $this->getDefaultFontPath();
		$this->debugMode = $debugMode;

		// Setup security service
		$this->getSecurityService()->disableCheckAllowedHosts();

		$this->log('MultiScriptHtml2Pdf initialized with font path: ' . $this->fontPath);
	}

	/**
	 * Get default font path
	 */
	private function getDefaultFontPath() {
		return DLM_ABSPATH . '/vendor/tecnickcom/tcpdf/fonts/';
	}

	/**
	 * Detect scripts in text content
	 *
	 * @param string $text Text to analyze
	 * @return array Array of detected script names
	 */
	public function detectTextScripts($text) {
		// Remove HTML tags to check only text content
		$plainText = strip_tags($text);

		$detectedScripts = [];

		// Script detection patterns
		$scriptPatterns = [
			'cjk' => '/[\x{4e00}-\x{9fff}\x{3400}-\x{4dbf}\x{f900}-\x{faff}\x{3040}-\x{309f}\x{30a0}-\x{30ff}\x{ac00}-\x{d7af}]/u',
			'arabic' => '/[\x{0600}-\x{06ff}\x{0750}-\x{077f}\x{08a0}-\x{08ff}\x{fb50}-\x{fdff}\x{fe70}-\x{feff}]/u',
			'hebrew' => '/[\x{0590}-\x{05ff}\x{fb1d}-\x{fb4f}]/u',
			'thai' => '/[\x{0e00}-\x{0e7f}]/u',
			'devanagari' => '/[\x{0900}-\x{097f}]/u',
			'cyrillic' => '/[\x{0400}-\x{04ff}\x{0500}-\x{052f}]/u',
			'greek' => '/[\x{0370}-\x{03ff}]/u',
			'armenian' => '/[\x{0530}-\x{058f}]/u',
			'georgian' => '/[\x{10a0}-\x{10ff}]/u',
			'tamil' => '/[\x{0b80}-\x{0bff}]/u',
			'bengali' => '/[\x{0980}-\x{09ff}]/u',
			'gujarati' => '/[\x{0a80}-\x{0aff}]/u',
			'punjabi' => '/[\x{0a00}-\x{0a7f}]/u',
			'telugu' => '/[\x{0c00}-\x{0c7f}]/u',
			'kannada' => '/[\x{0c80}-\x{0cff}]/u',
			'malayalam' => '/[\x{0d00}-\x{0d7f}]/u'
		];

		foreach ($scriptPatterns as $script => $pattern) {
			if (preg_match($pattern, $plainText)) {
				$detectedScripts[] = $script;
			}
		}

		return array_unique($detectedScripts);
	}

	/**
	 * Get recommended fonts for detected scripts
	 *
	 * @param array $scripts Detected scripts
	 * @return array Array of recommended font names
	 */
	public function getFontsForScripts($scripts) {
		$recommendedFonts = [];

		foreach ($scripts as $script) {
			if (isset($this->fontMap[$script])) {
				$recommendedFonts = array_merge($recommendedFonts, $this->fontMap[$script]['primary']);
			}
		}

		return array_unique($recommendedFonts);
	}

	/**
	 * Setup fonts based on detected scripts
	 *
	 * @param string $content HTML content to analyze
	 * @return array Detected scripts
	 */
	public function setupFontsForContent($content) {
		$this->detectedScripts = $this->detectTextScripts($content);

		$this->log('Detected scripts: ' . implode(', ', $this->detectedScripts));

		// Always add basic Latin fonts
		$this->addBasicFonts();

		// Add script-specific fonts
		if (!empty($this->detectedScripts)) {
			$this->addScriptSpecificFonts($this->detectedScripts);
			$this->setOptimalDefaultFont($this->detectedScripts);
		} else {
			$this->setDefaultFont('helvetica');
		}

		return $this->detectedScripts;
	}

	/**
	 * Add basic Latin fonts
	 */
	private function addBasicFonts() {
		$baseFonts = [
			'helvetica' => 'helvetica.php',
			'times' => 'times.php',
			'courier' => 'courier.php',
			'dejavusans' => 'dejavusans.php',
			'dejavuserif' => 'dejavuserif.php',
			'freesans' => 'freesans.php',
			'freeserif' => 'freeserif.php'
		];

		foreach ($baseFonts as $fontName => $fontFile) {
			$this->addFontIfExists($fontName, $fontFile);
		}
	}

	/**
	 * Add script-specific fonts
	 *
	 * @param array $scripts Detected scripts
	 */
	private function addScriptSpecificFonts($scripts) {
		foreach ($scripts as $script) {
			if (isset($this->fontMap[$script])) {
				$fonts = $this->fontMap[$script]['primary'];
				$files = $this->fontMap[$script]['files'];

				foreach ($fonts as $index => $fontName) {
					if (isset($files[$index])) {
						$this->addFontIfExists($fontName, $files[$index]);
					}
				}
			}
		}
	}

	/**
	 * Add font if file exists
	 *
	 * @param string $fontName Font name
	 * @param string $fontFile Font file name
	 * @return bool Success status
	 */
	private function addFontIfExists($fontName, $fontFile) {
		$fullPath = $this->fontPath . $fontFile;

		if (file_exists($fullPath)) {
			try {
				$this->addFont($fontName, '', $fontFile);
				$this->log("Successfully added font: $fontName");
				return true;
			} catch (Exception $e) {
				$this->log("Failed to add font $fontName: " . $e->getMessage());
				return false;
			}
		} else {
			$this->log("Font file not found: $fullPath");
			return false;
		}
	}

	/**
	 * Set optimal default font based on detected scripts
	 *
	 * @param array $scripts Detected scripts
	 */
	private function setOptimalDefaultFont($scripts) {
		// Priority order for setting default font
		$fontPriority = [];

		foreach ($scripts as $script) {
			if (isset($this->fontMap[$script])) {
				$fontPriority = array_merge($fontPriority, $this->fontMap[$script]['primary']);
			}
		}

		// Add fallback fonts
		$fontPriority = array_merge($fontPriority, ['dejavusans', 'freesans', 'helvetica', 'times']);

		// Try to set the first available font
		foreach (array_unique($fontPriority) as $fontName) {
			$fontFile = $this->getFontFileForName($fontName);
			if ($fontFile && file_exists($this->fontPath . $fontFile)) {
				$this->setDefaultFont($fontName);
				$this->log("Set default font to: $fontName");
				return;
			}
		}

		// Fallback to Helvetica
		$this->setDefaultFont('helvetica');
		$this->log("Fallback to default font: helvetica");
	}

	/**
	 * Get font file name for font name
	 *
	 * @param string $fontName Font name
	 * @return string|null Font file name
	 */
	private function getFontFileForName($fontName) {
		foreach ($this->fontMap as $script => $fonts) {
			$index = array_search($fontName, $fonts['primary']);
			if ($index !== false && isset($fonts['files'][$index])) {
				return $fonts['files'][$index];
			}
		}

		// Check basic fonts
		$basicFonts = [
			'helvetica' => 'helvetica.php',
			'times' => 'times.php',
			'courier' => 'courier.php',
			'dejavusans' => 'dejavusans.php',
			'dejavuserif' => 'dejavuserif.php',
			'freesans' => 'freesans.php',
			'freeserif' => 'freeserif.php'
		];

		return isset($basicFonts[$fontName]) ? $basicFonts[$fontName] : null;
	}

	/**
	 * Generate CSS font families for detected scripts
	 *
	 * @param array $scripts Detected scripts (optional, uses last detected if not provided)
	 * @return string CSS font-family declarations
	 */
	public function generateFontFamilyCSS($scripts = null) {
		$scripts = $scripts ?: $this->detectedScripts;

		$fontStacks = [
			'cjk' => '"STSong", "MS Song", "SimSun", "DejaVu Sans", "Free Sans", monospace',
			'arabic' => '"AE_AlArabiya", "AE_Furat", "DejaVu Sans", "Free Sans", sans-serif',
			'hebrew' => '"DejaVu Sans", "Free Sans", "Free Serif", sans-serif',
			'thai' => '"DejaVu Sans", "Free Sans", "Free Serif", sans-serif',
			'devanagari' => '"DejaVu Sans", "Free Sans", "Free Serif", sans-serif',
			'cyrillic' => '"DejaVu Sans", "DejaVu Serif", "Free Sans", "Times", sans-serif',
			'greek' => '"DejaVu Sans", "DejaVu Serif", "Free Sans", "Times", sans-serif'
		];

		$css = '<style>';
		$css .= 'body, * { font-family: ';

		if (!empty($scripts)) {
			$families = [];
			foreach ($scripts as $script) {
				if (isset($fontStacks[$script])) {
					$families[] = $fontStacks[$script];
				}
			}
			if (!empty($families)) {
				$css .= implode(', ', array_unique($families));
			} else {
				$css .= '"DejaVu Sans", "Helvetica", sans-serif';
			}
		} else {
			$css .= '"Helvetica", "DejaVu Sans", sans-serif';
		}

		$css .= '; }';
		$css .= '</style>';

		return $css;
	}

	/**
	 * Enhanced writeHTML method with automatic script detection
	 *
	 * @param string $html HTML content
	 * @param bool $autoAddCSS Automatically add CSS font declarations
	 */
	public function writeHTMLWithScriptDetection($html, $autoAddCSS = true) {
		// Detect scripts and setup fonts
		$detectedScripts = $this->setupFontsForContent($html);

		// Add CSS font declarations if requested
		if ($autoAddCSS && !empty($detectedScripts)) {
			$fontCSS = $this->generateFontFamilyCSS($detectedScripts);
			$html = $fontCSS . $html;
		}

		// Call parent writeHTML method
		$this->writeHTML($html);

		return $detectedScripts;
	}

	/**
	 * Get detected scripts from last analysis
	 *
	 * @return array Detected scripts
	 */
	public function getDetectedScripts() {
		return $this->detectedScripts;
	}

	/**
	 * Set font directory path
	 *
	 * @param string $path Font directory path
	 */
	public function setFontPath($path) {
		$this->fontPath = rtrim($path, '/') . '/';
	}

	/**
	 * Get current font directory path
	 *
	 * @return string Font directory path
	 */
	public function getFontPath() {
		return $this->fontPath;
	}

	/**
	 * Enable or disable debug mode
	 *
	 * @param bool $enabled Debug mode status
	 */
	public function setDebugMode($enabled) {
		$this->debugMode = $enabled;
	}

	/**
	 * Log debug messages
	 *
	 * @param string $message Log message
	 */
	private function log($message) {
		if ($this->debugMode) {
			error_log('[MultiScriptHtml2Pdf] ' . $message);
		}
	}

	/**
	 * Get supported scripts
	 *
	 * @return array Array of supported script names
	 */
	public function getSupportedScripts() {
		return array_keys($this->fontMap);
	}

	/**
	 * Test script detection with sample text
	 *
	 * @param string $text Text to test
	 * @return array Detection results
	 */
	public function testScriptDetection($text = null) {
		$testStrings = [
			'English text' => 'Hello World',
			'Chinese Simplified' => '你好世界',
			'Chinese Traditional' => '你好世界',
			'Japanese' => 'こんにちは世界',
			'Korean' => '안녕하세요 세계',
			'Arabic' => 'مرحبا بالعالم',
			'Hebrew' => 'שלום עולם',
			'Thai' => 'สวัสดีโลก',
			'Hindi' => 'नमस्ते दुनिया',
			'Russian' => 'Привет мир',
			'Greek' => 'Γεια σου κόσμε',
			'Mixed CJK+Arabic+Hebrew' => 'Hello 你好 مرحبا שלום 안녕하세요'
		];

		if ($text) {
			$testStrings['Custom'] = $text;
		}

		$results = [];
		foreach ($testStrings as $name => $testText) {
			$detected = $this->detectTextScripts($testText);
			$results[$name] = [
				'text' => $testText,
				'detected_scripts' => $detected,
				'recommended_fonts' => $this->getFontsForScripts($detected),
				'will_render_correctly' => $this->checkFontAvailability($detected)
			];
		}

		return $results;
	}

	/**
	 * Check if fonts are available for detected scripts
	 *
	 * @param array $scripts Detected scripts
	 * @return array Availability status for each script
	 */
	private function checkFontAvailability($scripts) {
		$availability = [];

		foreach ($scripts as $script) {
			$fonts = $this->getFontsForScripts([$script]);
			$hasFont = false;

			foreach ($fonts as $font) {
				$fontFile = $this->getFontFileForName($font);
				if ($fontFile && file_exists($this->fontPath . $fontFile)) {
					$hasFont = true;
					break;
				}
			}

			$availability[$script] = $hasFont ? 'Available' : 'Limited Support';
		}

		return $availability;
	}
}