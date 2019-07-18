<?php
// Copyright (c) 2016 Interfacelab LLC. All rights reserved.
//
// Released under the GPLv3 license
// http://www.gnu.org/licenses/gpl-3.0.html
//
// Uses code from:
// Persist Admin Notices Dismissal
// by Agbonghama Collins and Andy Fragen
//
// **********************************************************************
// This program is distributed in the hope that it will be useful, but
// WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
// **********************************************************************

namespace ILAB\MediaCloud\Documentation;

use ILAB\MediaCloud\Documentation\Markdown\MarkdownExtra;
use function ILAB\MediaCloud\Utilities\arrayPath;
use IvoPetkov\HTML5DOMDocument;
use IvoPetkov\HTML5DOMElement;

if (!defined('ABSPATH')) { header('Location: /'); die; }

class Docs {
	private $docPath;
	private $docURL;
	private $tocPath;
	private $versionInfo;
	private $toc = null;
	private $debugMode = false;
	private $currentPage;
	private $currentPagePath;
	private $canSearch = false;

	public function __construct() {
		$this->docPath = trailingslashit(ILAB_TOOLS_DIR).'docs';
		$this->docURL = trailingslashit(ILAB_TOOLS_URL).'docs';
		$this->tocPath = $this->docPath.'/toc.md';
		$this->debugMode = (defined('WP_ENV') && (WP_ENV != 'production'));
		$this->canSearch = (file_exists($this->docPath.'/docs.index') && extension_loaded('sqlite3'));

		if (!file_exists($this->tocPath)) {
			return;
		}

		$this->versionInfo = get_option('media_cloud_documentation_info', null);
		$this->toc = get_option('media_cloud_documentation_toc', null);
		if (empty($this->toc) || empty($this->versionInfo)) {
			$this->parseTOC();
		} else {
			$currentVersion = get_option('media_cloud_documentation_version', null);
			if ($currentVersion != MEDIA_CLOUD_VERSION) {
				$this->parseTOC();
			}
		}

		if (empty($this->toc)) {
			return;
		}

		add_action('admin_enqueue_scripts', function(){
			wp_enqueue_script('media-cloud-docs-js', ILAB_PUB_JS_URL.'/media-cloud-docs.js');
			wp_enqueue_script('media-cloud-docs-prism-js', ILAB_PUB_JS_URL.'/docs-prism.js');
			wp_enqueue_style('media-cloud-docs-css', ILAB_PUB_CSS_URL . '/media-cloud-docs.css' );
//
//			if (file_exists($this->currentConfig->dir.'docs.css')) {
//				wp_enqueue_style('media-cloud-docs-css-'.$this->currentDocs, $this->currentConfig->url.'/docs.css' );
//			}
		});

		if (is_admin()) {
			add_action('wp_ajax_media_cloud_render_doc_page', [$this,'renderAjaxPage']);
		}
	}

	//region Admin Menu
	public function registerAdminMenu($parentSlug) {
		if (!$this->loaded()) {
			return;
		}

		add_submenu_page($parentSlug, 'Media Cloud Documentation', 'Documentation', 'manage_options','media-cloud-docs', [$this,'renderMenuPage']);

	}
	//endregion

	//region Structural
	/**
	 * Gets child TOC entries for the current page
	 *
	 * @param $page
	 * @param $entries
	 * @return array|null
	 */
	private function getChildrenEntriesFor($page, $entries) {
		foreach($entries as $entry) {
			if ($entry['src'] == $page) {
				if (isset($entry['children'])) {
					return $entry['children'];
				} else {
					return [];
				}
			}

			if (isset($entry['children'])) {
				$res = $this->getChildrenEntriesFor($page, $entry['children']);
				if (is_array($res)) {
					return $res;
				}
			}
		}

		return null;
	}

	/**
	 * Searches the TOC for the current page
	 *
	 * @param $entries
	 * @param $results
	 * @return bool
	 */
	private function searchForCurrentPage($entries, &$results) {
		foreach($entries as $entry) {
			if ($entry['src'] == $this->currentPage) {
				$results[] = [
					'title' => $entry['title'],
					'src' => $entry['src'],
					'anchor' => arrayPath($entry, 'anchor', null)
				];

				return true;
			}

			if (isset($entry['children'])) {
				if ($this->searchForCurrentPage($entry['children'],$results)) {
					$results[] = [
						'title' => $entry['title'],
						'src' => $entry['src'],
						'anchor' => arrayPath($entry, 'anchor', null)
					];

					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Returns the breadcrumb trail for the current page
	 *
	 * @return array
	 */
	private function getTrailForCurrentPage() {
		$title = arrayPath($this->versionInfo, 'title', 'Documentation');

		$result = [
			[
				'title' => $title,
				'src' => 'index'
			]
		];

		if ($this->currentPage == 'index') {
			return $result;
		}

		$searchResults = [];
		$this->searchForCurrentPage($this->toc, $searchResults);
		$searchResults = array_reverse($searchResults);

		return array_merge($result, $searchResults);
	}
	//endregion

	//region Page rendering
	private function loadCurrentPage() {
		if (empty($this->toc)) {
			return false;
		}

		// Get the currently requested page
		$this->currentPage = 'index';
		if (isset($_GET['doc-page'])) {
			$this->currentPage = $_GET['doc-page'];
		} else if (isset($_POST['doc-page'])) {
			$this->currentPage = $_POST['doc-page'];
		}

		$pagePath = trailingslashit($this->docPath).$this->currentPage.'.md';
		if (!file_exists($pagePath)) {
			return false;
		}

		$this->currentPagePath = $pagePath;
		return true;
	}

	/**
	 * Converts TOC entries to HTML
	 * @param $entries
	 * @return string
	 */
	private function convertEntriesToHTML($entries) {
		$html = '';
		foreach($entries as $entry) {
			if ($entry['src'] == 'index') {
				continue;
			}

			$url = admin_url('admin.php?page=media-cloud-docs&doc-page='.$entry['src']);
			$anchor = arrayPath($entry, 'anchor', '');
			if (!empty($anchor)) {
				$anchor = '#'.$anchor;
			}

			$hasChildren = !empty($entry['children']);
			$html .= "<li class='".(($hasChildren) ? 'has-children' : '')."'><a href='{$url}{$anchor}'>{$entry['title']}</a>";
			if (isset($entry['children'])) {
				$html .= '<ul class="child-entries">';
				$html .= $this->convertEntriesToHTML($entry['children']);
				$html .= '</ul>';
			}
			$html .= "</li>";
		}

		return $html;
	}

	/**
	 * Renders the breadcrumbs for the current page
	 *
	 * @return string
	 */
	private function renderBreadcrumbs() {
		$trailResults = $this->getTrailForCurrentPage();

		$dropdown = "<ul class='toc-menu'><li>".file_get_contents(ILAB_PUB_IMG_DIR.'/ui-icon-toc.svg')."<ul class='child-entries'>";
		$dropdown .= $this->convertEntriesToHTML($this->toc);
		$dropdown .= "</ul></li></ul>";

		$result = '<div class="media-cloud-docs-breadcrumbs">'.$dropdown.'<ul class="crumbs">';
		for($i = 0; $i < count($trailResults); $i++) {
			if ($i == count($trailResults) - 1) {
				$result .= "<li>{$trailResults[$i]['title']}</li>";
			} else {
				$result .= "<li><a href='".admin_url('admin.php?page=media-cloud-docs&doc-page='.$trailResults[$i]['src'])."'>{$trailResults[$i]['title']}</a></li>";
			}
		}
		$result .= '</ul></div>';

		return $result;
	}

	/**
	 * Renders the header
	 *
	 * @return string
	 */
	private function renderHeader() {
		$searchText = (isset($_POST['search-text'])) ? $_POST['search-text'] : null;

		$result = "<div class='media-cloud-docs-header".(($this->canSearch) ? ' media-cloud-docs-has-search' : '')."'>";
		if (isset($this->versionInfo['logo'])) {
			$title = isset($this->versionInfo['title']) ? $this->versionInfo['title'] : 'Documentation';
			$logoSrc = trailingslashit($this->docURL).$this->versionInfo['logo'];
			$logoWidth = $this->versionInfo['logo-width'];
			$logoHeight = $this->versionInfo['logo-height'];

			$result .= "<img src='".ILAB_PUB_IMG_URL."/icon-cloud-w-type.svg'><span>$title</span>";
		} else {
			$result .= "";
		}

		if ($this->canSearch) {
			$result .= "<div class='media-cloud-docs-search'><form method='POST'><input type='hidden' name='action' value='docs-search'><input type='search' class='newtag form-input-tip ui-autocomplete-input' name='search-text' ".(($searchText) ? " value='$searchText'" : "")." placeholder='Search ...'><input type='submit' value='Search' class='button-primary'></form></div>";
		}

		$result .= "</div>";

		return $result;
	}

	private function renderPage() {
		if (!$this->loadCurrentPage()) {
			return '';
		}

		$result = '';

		$text = file_get_contents($this->currentPagePath);

		$parser = new MarkdownExtra();

		$parser->header_id_func = function($val) {
			return sanitize_title($val);
		};

		$parser->url_filter_func = function($url) {
			// other doc links
			if (preg_match("/.*\.md/", $url)) {
				$url = str_replace('.md', '', $url);
				$hash = '';
				if (strpos($url, '#') !== false) {
					$urlParts = explode('#', $url);
					$url = $urlParts[1];
					$hash = '#'.$urlParts[2];
				}
				return admin_url("admin.php?page=media-cloud-docs&doc-page={$url}{$hash}");
			}

			// admin links
			if (preg_match("/admin:(.*)/", $url, $matches)) {
				return admin_url($matches[1]);
			}

			// images local to the doc
			$matches = [];
			if (preg_match("/(^[^\/]{1}.*\.(?:jpg|png|jpeg|svg))/", $url, $matches)) {
				if (strpos($url, 'http://') === 0) {
					return $url;
				}

				if (strpos($url, 'https://') === 0) {
					return $url;
				}

				return trailingslashit($this->docURL).$url;
			}

			return $url;
		};

		// Process embeds
		$embeds = [];
		if (preg_match_all("/\@\s*\[[^]]*\]\s*\(([^)]*)\)/m", $text, $embeds)) {
			for($i = 0; $i < count($embeds[1]); $i++) {
				$embedCode = wp_oembed_get($embeds[1][$i]);
				$isVideo = preg_match ("/\b(?:vimeo\.com|youtube\.com|youtu\.be|dailymotion\.com)\b/i", $embeds[1][$i]);
				$text = str_replace($embeds[0][$i], "<div class='embed-container".(($isVideo) ? ' embed-video':'')."'>$embedCode</div>", $text);
			}
		}

		// Process toc
		$tocs = [];
		if (preg_match_all("/@toc\(([^)]*)\)/m", $text, $tocs)) {
			for($i = 0; $i < count($tocs[1]); $i++) {
				$tocPage = $tocs[1][$i];
				if (empty($tocPage)) {
					$tocPage = $this->currentPage;
				}

				if ($tocPage == 'index') {
					$entries = $this->toc;
				} else {
					$entries = $this->getChildrenEntriesFor($tocPage, $this->toc);
				}

				if (!empty($entries) && is_array($entries) && (count($entries) > 0)) {
					$tocHTML = '<ul class="toc">';
					$tocHTML .= $this->convertEntriesToHTML($entries);
					$tocHTML .='</ul>';
					$text = str_replace($tocs[0][$i], $tocHTML, $text);
				}
			}
		}

		// Convert to HTML
		$html = $parser->transform($text);

		$result .= $this->renderHeader();
		$result .= $this->renderBreadcrumbs();

		$tocList = $this->convertEntriesToHTML($this->toc);

		$result .= "<div class='media-cloud-docs-container'><div class='media-cloud-docs-toc'>$tocList</div><div class='media-cloud-docs-body line-numbers'>$html</div></div>";

		return $result;
	}

	private function renderSearchResults() {
		if (!$this->loadCurrentPage()) {
			return '';
		}
	}

	public function renderAjaxPage() {
		if ($this->canSearch && isset($_POST['search-text'])) {
			$page = $this->renderSearchResults();
		} else {
			$page = $this->renderPage();
		}

		if (empty($page)) {
			wp_send_json(['status' => 'error'], 400);
		}

		wp_send_json(['status' => 'ok', 'html' => $page]);
	}

	public function renderMenuPage() {
		if ($this->canSearch && isset($_POST['search-text'])) {
			echo $this->renderSearchResults();
		} else {
			echo $this->renderPage();
		}
	}
	//endregion

	//region TOC Loading
	private function parseTOCHeader(&$toc) {
		preg_match('/<!---(.+?)--->/ms', $toc, $matches);
		if (empty($matches)) {
			return false;
		}

		$header = $matches[1];
		if (empty($header)) {
			return false;
		}

		$toc = str_replace($matches[0],'', $toc);

		preg_match_all('/^([aA-zZ-]+)\s*:\s*[\'"]*(.+?)[\'"]*$/ms', $header, $headerFields, PREG_SET_ORDER);
		if (empty($headerFields)) {
			return false;
		}

		$this->versionInfo = [];
		foreach($headerFields as $headerField) {
			$this->versionInfo[$headerField[1]] = $headerField[2];
		}

		return true;
	}

	private function parseTOC() {
		$this->toc = null;

		$toc = file_get_contents($this->tocPath);
		if (empty($toc)) {
			return;
		}

		if (!$this->parseTOCHeader($toc)) {
			return;
		}

		$parser = new MarkdownExtra();
		$html = $parser->transform($toc);


		$htmlDoc = new HTML5DOMDocument();
		$htmlDoc->loadHTML($html);

		/** @var HTML5DOMElement $ul */
		$ul = $htmlDoc->querySelector('ul');
		$this->toc = $this->parseList($ul);


		if (!$this->debugMode) {
			update_option('media_cloud_documentation_version', MEDIA_CLOUD_VERSION);
			update_option('media_cloud_documentation_info', $this->versionInfo);
			update_option('media_cloud_documentation_toc', $this->toc);
		}
	}

	private function parseList($ul) {
		$result = [];
		/** @var HTML5DOMElement $li */
		foreach($ul->childNodes as $li) {
			if ($li->nodeName != 'li') {
				continue;
			}

			$tocEntry = [];
			/** @var HTML5DOMElement $childNode */
			foreach($li->childNodes as $childNode) {
				if ($childNode->nodeType == XML_TEXT_NODE) {
					$text = trim($childNode->textContent);
					if (empty($text)) {
						continue;
					}

					$tocEntry["title"] = $text;
				} else if ($childNode->nodeType == XML_ELEMENT_NODE) {
					if ($childNode->nodeName == 'ul') {
						$tocEntry['children'] = $this->parseList($childNode);
					} else if ($childNode->nodeName == 'a') {
						$text = trim($childNode->textContent);
						$tocEntry['title'] = $text;

						$href = $childNode->getAttribute('href');
						$anchor = null;
						if (strpos($href, '#') !== false) {
							$hrefParts = explode('#', $href);
							$href = str_replace('.md', '', $hrefParts[0]);
							$anchor = $hrefParts[1];
						} else {
							$href = str_replace('.md', '', $href);
						}

						$tocEntry['src'] = $href;
						if (!empty($anchor)) {
							$tocEntry['anchor'] = $anchor;
						}
					}
				}
			}

			if (!empty($tocEntry)) {
				$result[] = $tocEntry;
			}
		}

		return $result;
	}
	//endregion

	public function loaded() {
		return !empty($this->toc);
	}
}