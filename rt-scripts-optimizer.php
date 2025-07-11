<?php

/**
 * Plugin Name: RT Scripts Optimizer
 * Description: Loading scripts via worker thread for boosting up the site speed. Keeps the main thread idle for users to interact as quickly as possible.
 * Author: rtCamp, pradeep910
 * Plugin URI:  https://rtcamp.com
 * Author URI:  https://rtcamp.com
 * Version: 0.1
 * Text Domain: rt-script-optimizer
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 *
 * @package RT_Script_Optimizer
 */

define('RT_SCRIPTS_OPTIMIZER_DIR_PATH', untrailingslashit(plugin_dir_path(__FILE__)));
define('RT_SCRIPTS_OPTIMIZER_DIR_URL', untrailingslashit(plugin_dir_url(__FILE__)));

// Include settings options page.
require_once RT_SCRIPTS_OPTIMIZER_DIR_PATH . '/includes/settings-page.php';

// Skip if it is WP Backend.
if (is_admin()) {
	return;
}

// Skip if it is customizer preview.
if (isset($_REQUEST['customize_changeset_uuid'])) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended
	return;
}

// Variable to store the scripts to be excluded.
$skip_js = array(
	'lodash',
	'wp-dom-ready',
	'wp-hooks',
	'wp-i18n',
);
/**
 * Outputs inline scripts and styles in the <head> section to enable JavaScript worker thread execution and AMP boilerplate CSS for optimized loading.
 *
 * Injects a worker thread script for deferred JavaScript execution if JS optimizations are enabled. Optionally outputs AMP boilerplate CSS to reduce Cumulative Layout Shift (CLS) if the relevant option is enabled and CSS optimizations are active. Skips output on AMP pages.
 */
function rt_head_scripts()
{

	// If AMP page request, return nothing.
	if (function_exists('amp_is_request') && amp_is_request()) {
		return null;
	}

	if ('1' !== get_option('rt_scripts_optimizer_disable_js_optimizations')) {
?>
		<script type="text/worker" id="rtpwa">onmessage=function(e){var o=new Request(e.data,{mode:"no-cors",redirect:"follow"});fetch(o)};</script>
		<script type="text/javascript">
			var x = new Worker("data:text/javascript;base64," + btoa(document.getElementById("rtpwa").textContent));
		</script>
	<?php
	}

	$load_amp_css = get_option('rt_scripts_optimizer_load_amp_boilerplate_style');

	if ('1' === $load_amp_css && '1' !== get_option('rt_scripts_optimizer_disable_css_optimizations')) {
	?>
		<!-- Load the amp-boiler plate to show content after 0.5 seconds. Helps with CLS issue. Use selector (.site-content) of the content area after your <header> tag, so header displays always. -->
		<style amp-boilerplate>
			@-webkit-keyframes -amp-start {
				from {
					visibility: hidden
				}

				to {
					visibility: visible
				}
			}

			@-moz-keyframes -amp-start {
				from {
					visibility: hidden
				}

				to {
					visibility: visible
				}
			}

			@-ms-keyframes -amp-start {
				from {
					visibility: hidden
				}

				to {
					visibility: visible
				}
			}

			@-o-keyframes -amp-start {
				from {
					visibility: hidden
				}

				to {
					visibility: visible
				}
			}

			@keyframes -amp-start {
				from {
					visibility: hidden
				}

				to {
					visibility: visible
				}
			}

			.site-content {
				-webkit-animation: -amp-start 1s steps(1, end) 0s 1 normal both;
				-moz-animation: -amp-start 1s steps(1, end) 0s 1 normal both;
				-ms-animation: -amp-start 1s steps(1, end) 0s 1 normal both;
				animation: -amp-start 1s steps(1, end) 0s 1 normal both
			}

			@media (min-width: 768px) {
				.site-content {
					-webkit-animation: -amp-start 0.5s steps(1, end) 0s 1 normal both;
					-moz-animation: -amp-start 0.5s steps(1, end) 0s 1 normal both;
					-ms-animation: -amp-start 0.5s steps(1, end) 0s 1 normal both;
					animation: -amp-start 0.5s steps(1, end) 0s 1 normal both
				}
			}
		</style>
		<noscript>
			<style amp-boilerplate>
				.site-content {
					-webkit-animation: none;
					-moz-animation: none;
					-ms-animation: none;
					animation: none
				}
			</style>
		</noscript>
	<?php
	}
}
add_action('wp_head', 'rt_head_scripts', 0);

/**
 * Outputs a footer script that defers execution of JavaScript until user interaction.
 *
 * Adds event listeners for user interactions (mouse, keyboard, touch, scroll) and, upon the first interaction, loads all deferred scripts marked with `type="text/rtscript"` by converting them to standard `<script>` tags. After loading, it dispatches key DOM events to simulate normal page load behavior. Skips output if on an AMP page or if JavaScript optimizations are disabled.
 */
function rt_footer_scripts()
{

	// Skip if it is backend or AMP page.
	if (function_exists('amp_is_request') && amp_is_request()) {
		return null;
	}

	if ('1' === get_option('rt_scripts_optimizer_disable_js_optimizations')) {
		return;
	}
	?>
	<script type="text/javascript">
		const t = ["mouseover", "keydown", "touchmove", "touchstart", "scroll"];
		t.forEach(function(t) {
			window.addEventListener(t, e, {
				passive: true
			})
		});

		function e() {
			n();
			t.forEach(function(t) {
				window.removeEventListener(t, e, {
					passive: true
				})
			})
		}

		function c(t, e, n) {
			if (typeof n === "undefined") {
				n = 0
			}
			t[n](function() {
				n++;
				if (n === t.length) {
					e()
				} else {
					c(t, e, n)
				}
			})
		}

		function u() {
			var t = document.createEvent("Event");
			t.initEvent("DOMContentLoaded", true, true);
			window.dispatchEvent(t);
			document.dispatchEvent(t);
			var e = document.createEvent("Event");
			e.initEvent("readystatechange", true, true);
			window.dispatchEvent(e);
			document.dispatchEvent(e);
			var n = document.createEvent("Event");
			n.initEvent("load", true, true);
			window.dispatchEvent(n);
			document.dispatchEvent(n);
			var o = document.createEvent("Event");
			o.initEvent("show", true, true);
			window.dispatchEvent(o);
			document.dispatchEvent(o);
			var c = window.document.createEvent("UIEvents");
			c.initUIEvent("resize", true, true, window, 0);
			window.dispatchEvent(c);
			document.dispatchEvent(c);
		}

		function rti(t, e) {
			var n = document.createElement("script");
			n.type = "text/javascript";
			if (t.src) {
				n.onload = e;
				n.onerror = e;
				n.src = t.src;
				n.id = t.id
			} else {
				n.textContent = t.innerText;
				n.id = t.id
			}
			t.parentNode.removeChild(t);
			document.body.appendChild(n);
			if (!t.src) {
				e()
			}
		}

		function n() {
			var t = document.querySelectorAll("script");
			var n = [];
			var o;
			[].forEach.call(t, function(e) {
				o = e.getAttribute("type");
				if (o == "text/rtscript") {
					n.push(function(t) {
						rti(e, t)
					})
				}
			});
			c(n, u)
		}
	</script>
	<?php
}
add_action('wp_footer', 'rt_footer_scripts');

/**
 * Modifies script tags to defer their execution by changing their type and ID for worker thread offloading.
 *
 * Scripts not excluded by handle or path are given `type="text/rtscript"` and a unique ID, allowing them to be loaded by a worker thread after user interaction. Returns the original tag for excluded scripts, AMP pages, or when JS optimizations are disabled.
 *
 * @param string $tag    The original `<script>` tag.
 * @param string $handle The script's registered handle.
 * @param string $src    The script's source URL.
 * @return string The modified or original script tag.
 */
function rt_scripts_handler($tag, $handle, $src)
{

	if ('1' === get_option('rt_scripts_optimizer_disable_js_optimizations')) {
		return $tag;
	}

	global $skip_js;

	if (function_exists('amp_is_request') && amp_is_request()) {
		return $tag;
	}

	/**
	 * Checks if the plugin has to be disabled.
	 *
	 * Return true if it has to be disabled.
	 *
	 * @return bool.
	 */
	$disable_rt_optimzer = apply_filters('disable_rt_scripts_optimizer', false);

	if ($disable_rt_optimzer) {
		return $tag;
	}

	$handles_option_array = explode(',', get_option('rt_scripts_optimizer_exclude_handles'));
	$paths_option_array   = explode(',', get_option('rt_scripts_optimizer_exclude_paths'));

	// Get handle using the paths provided in the settings.
	foreach ($paths_option_array as $script_path) {
		$script_path = trim($script_path);
		if (empty($script_path)) {
			continue;
		}

		if (strpos($src, $script_path) && ! in_array($handle, $skip_js, true)) {
			array_push($skip_js, $handle);
			break;
		}
	}

	$skip_js = array_unique(array_merge($skip_js, $handles_option_array));

	$array_regenerator_runtime_script = array_search('regenerator-runtime', $skip_js, true);

	// If page is single post or page and the script is not in the skip_js array then skip regenerator-runtime script.
	if (is_single() && ! $array_regenerator_runtime_script) {
		array_push($skip_js, 'regenerator-runtime');
	} elseif ($array_regenerator_runtime_script) {
		unset($skip_js[$array_regenerator_runtime_script]);
	}

	if (in_array($handle, $skip_js, true)) {
		return $tag;
	}

	// Change the script attributes and id before returning to remove it from main thread.
	$tag = sprintf(
		'<script type="text/rtscript" src="%s" id="%s"></script>',  // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript
		esc_url($src),
		$handle . '-js'
	);

	return $tag;
}
add_filter('script_loader_tag', 'rt_scripts_handler', 10, 3);

/**
 * Modifies stylesheet link tags to enable asynchronous or deferred loading based on plugin settings.
 *
 * Depending on the style handle and plugin options, this function can comment out stylesheets, convert them to preload with onload switching, or mark them for JavaScript-driven loading. Adds a `<noscript>` fallback for non-standard loading methods.
 *
 * @param string $html The original stylesheet link tag generated by WordPress.
 * @param string $handle The style enqueue handle.
 * @return string The modified or original HTML for the stylesheet link tag.
 */
function load_async_styles($html, $handle)
{

	if ('1' === get_option('rt_scripts_optimizer_disable_css_optimizations')) {
		return $html;
	}

	if (function_exists('amp_is_request') && amp_is_request()) {
		return $html;
	}

	/**
	 * Checks if the plugin has to be disabled.
	 *
	 * Return true if it has to be disabled.
	 *
	 * @return bool.
	 */
	$disable_rt_optimzer = apply_filters('disable_rt_scripts_optimizer', false);

	if ($disable_rt_optimzer) {
		return $html;
	}

	$comment_out_handles = explode(',', get_option('rt_scripts_optimizer_comment_out_style_handles'));

	if (! is_admin() && in_array($handle, $comment_out_handles, true)) {
		return '<!-- ' . $html . ' -->';
	}

	$async_loading = explode(',', get_option('rt_scripts_optimizer_style_async_handles'));
	if (! is_admin() && in_array($handle, $async_loading, true)) {
		$async_html  = str_replace('rel=\'stylesheet\'', 'rel=\'preload\' as=\'style\'', $html);
		$async_html  = str_replace('media=\'all\'', 'media=\'all\' onload="this.onload=null;this.rel=\'stylesheet\'"', $async_html);
		$async_html .= sprintf('<noscript>%s</noscript>', $html);
		return $async_html;
	}

	$async_js_loading = array(); // The above array can be used here also but that will cause FOUT as this script is included in the footer from where the CSS is loaded.
	if (! is_admin() && in_array($handle, $async_js_loading, true)) {
		$async_html  = str_replace('rel=\'stylesheet\'', 'rel=\'rt-optimized-stylesheet\'', $html);
		$async_html .= sprintf('<noscript>%s</noscript>', $html);
		return $async_html;
	}

	$optimized_loading = explode(',', get_option('rt_scripts_optimizer_style_async_handles_onevent'));
	if (! is_admin() && in_array($handle, $optimized_loading, true)) {
		$async_html  = str_replace('rel=\'stylesheet\'', 'rel=\'rt-optimized-onevent-stylesheet\'', $html);
		$async_html .= sprintf('<noscript>%s</noscript>', $html);
		return $async_html;
	}
	return $html;
}
add_filter('style_loader_tag', 'load_async_styles', 10, 2);

/**
 * Outputs inline JavaScript in the footer to enable on-demand loading of stylesheets and lazy loading of iframes and embed scripts.
 *
 * If CSS optimizations are enabled, adds event listeners to load stylesheets marked for deferred or on-event loading. If JS optimizations are enabled, sets up lazy loading for iframes and dynamically loads scripts for Twitter and Reddit embeds when they enter the viewport.
 */
function style_enqueue_script()
{

	if (is_admin()) {
		return null;
	}

	$disable_css_optimizations = '1' === get_option('rt_scripts_optimizer_disable_css_optimizations');
	$disable_js_optimizations  = '1' === get_option('rt_scripts_optimizer_disable_js_optimizations');

	if (! $disable_css_optimizations) {
	?>
		<script type="text/javascript">
			const s_i_e = ["mousemove", "keydown", "touchmove", "touchstart", "scroll"];

			function s_i_e_e() {
				s_i(), s_i_e.forEach(function(e) {
					window.removeEventListener(e, s_i_e_e, {
						passive: !0
					})
				})
			}

			function s_i_rti(e) {
				loadCSS(e.href), e.href || s_i_e_e()
			}

			function s_i() {
				var e = document.querySelectorAll("link");
				[].forEach.call(e, function(e) {
					"rt-optimized-onevent-stylesheet" == e.getAttribute("rel") && s_i_rti(e)
				})
			}
			s_i_e.forEach(function(e) {
					window.addEventListener(e, s_i_e_e, {
						passive: !0
					})
				}),
				function() {
					var e = document.querySelectorAll("link");
					[].forEach.call(e, function(e) {
						"rt-optimized-stylesheet" == e.getAttribute("rel") && loadCSS(e.href)
					})
				}();
		</script>
	<?php
	}

	if (! $disable_js_optimizations) {
	?>
		<script type="text/javascript">
			document.addEventListener('DOMContentLoaded', () => {
				const iframes = document.getElementsByTagName('iframe');
				const iframesObserver = new IntersectionObserver((entries, self) => {
					entries.forEach((entry) => {
						if (entry.isIntersecting) {
							const targetURL = entry.target.getAttribute('data-src');
							if (targetURL !== null) {
								entry.target.src = targetURL;
							}
							self.unobserve(entry.target);
						}
					})
				}, {
					threshold: 0,
					rootMargin: '200px'
				});
				Array.from(iframes).forEach(function(el) {
					iframesObserver.observe(el);
				});
				const tweets = document.getElementsByClassName('wp-block-embed-twitter');
				const reddit = document.getElementsByClassName('wp-block-embed-reddit');
				const scriptLoadedIframes = Array.from(tweets).concat(Array.from(reddit));
				const scriptLoadedIframesObserver = new IntersectionObserver((entries, self) => {
					entries.forEach((entry) => {
						if (entry.isIntersecting) {
							var target = entry.target;
							var scriptElement = target.getElementsByTagName('script');
							Array.from(scriptElement).forEach(function(el) {
								var newIframeScript = document.createElement('script');
								newIframeScript.setAttribute('src', el.src);
								target.append(newIframeScript);

							});
							self.unobserve(entry.target);
						}
					})
				}, {
					threshold: 0,
					rootMargin: '200px'
				});
				scriptLoadedIframes.forEach(function(el) {
					scriptLoadedIframesObserver.observe(el);
				});
			});
		</script>
<?php
	}
}
add_action('wp_footer', 'style_enqueue_script');

/**
 * Dequeues and deregisters styles for non-logged-in users based on plugin settings.
 *
 * If CSS optimizations are disabled, the plugin is disabled, or the request is for AMP, no styles are dequeued.
 * Only styles whose handles are listed in the "dequeue non-logged handles" option are affected, and only for users who are not logged in.
 */
function dequeue_styles()
{

	if ('1' === get_option('rt_scripts_optimizer_disable_css_optimizations')) {
		return;
	}

	if (function_exists('amp_is_request') && amp_is_request()) {
		return;
	}

	/**
	 * Checks if the plugin has to be disabled.
	 *
	 * Return true if it has to be disabled.
	 *
	 * @return bool.
	 */
	$disable_rt_optimzer = apply_filters('disable_rt_scripts_optimizer', false);

	if ($disable_rt_optimzer) {
		return;
	}

	// If user not logged in, these styles will be dequeued.
	$non_logged_in = explode(',', get_option('rt_scripts_optimizer_style_dequeue_non_logged_handles'));

	if (! is_user_logged_in()) {
		foreach ($non_logged_in as $dequeue_handle) {
			wp_dequeue_style($dequeue_handle);
			wp_deregister_style($dequeue_handle);
		}
	}
}
add_action('wp_print_styles', 'dequeue_styles');

/**
 * Remove concating all js if site is using nginx-http plugin for files concatination or site is hosted on WordPress VIP.
 */
add_filter('js_do_concat', '__return_false');

/**
 * Determines whether to skip CSS concatenation for a given handle based on plugin settings.
 *
 * Returns false to skip concatenation if the handle is listed in the `rt_scripts_optimizer_skip_css_concatination_handles` option; otherwise, returns the original value.
 *
 * @param bool $do_concat The default concatenation value for the current handle.
 * @param string $handle The stylesheet handle being processed.
 * @return bool True to allow concatenation, false to skip for this handle.
 */
function skip_css_concatination($do_concat, $handle)
{

	$skip_concatination_handles = explode(',', get_option('rt_scripts_optimizer_skip_css_concatination_handles'));

	foreach ($skip_concatination_handles as $skip_concatination_handle) {
		if ($skip_concatination_handle === $handle) {
			return false;
		}
	}

	return $do_concat;
}

/**
 * Disable concatination according to the supplied setting.
 */
if ('1' !== get_option('rt_scripts_optimizer_disable_css_optimizations')) {
	if ('1' === get_option('rt_scripts_optimizer_skip_css_concatination_all')) {

		add_filter('css_do_concat', '__return_false');
	} else {

		add_filter('css_do_concat', 'skip_css_concatination', 10, 2);
	}
}

/**
 * Disables all WordPress emoji scripts, styles, and filters on both frontend and admin.
 *
 * Removes emoji-related scripts and styles from page output and prevents emoji CDN DNS prefetching.
 */
function disable_emojis()
{
	remove_action('wp_head', 'print_emoji_detection_script', 7);
	remove_action('admin_print_scripts', 'print_emoji_detection_script');
	remove_action('wp_print_styles', 'print_emoji_styles');
	remove_action('admin_print_styles', 'print_emoji_styles');
	remove_filter('the_content_feed', 'wp_staticize_emoji');
	remove_filter('comment_text_rss', 'wp_staticize_emoji');
	remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
	add_filter('wp_resource_hints', 'disable_emojis_remove_dns_prefetch', 10, 2);
}
add_action('init', 'disable_emojis');

/**
 * Removes the WordPress emoji CDN URL from DNS prefetch resource hints.
 *
 * If the relation type is 'dns-prefetch', filters out the emoji SVG CDN URL from the provided URLs array.
 *
 * @param array $urls The list of resource hint URLs.
 * @param string $relation_type The type of resource hint relation (e.g., 'dns-prefetch').
 * @return array The filtered list of URLs with the emoji CDN removed if applicable.
 */
function disable_emojis_remove_dns_prefetch($urls, $relation_type)
{
	if ('dns-prefetch' === $relation_type) {

		/** This filter is documented in wp-includes/formatting.php */
		$emoji_svg_url = apply_filters('emoji_svg_url', 'https://s.w.org/images/core/emoji/2/svg/');

		$urls = array_diff($urls, array($emoji_svg_url));
	}
	return $urls;
}

/**
 * Enqueues the loadCSS script for asynchronous CSS loading if CSS optimizations are enabled.
 */
function rt_scripts_optimizer_load_scripts()
{

	if ('1' === get_option('rt_scripts_optimizer_disable_css_optimizations')) {
		return;
	}

	wp_enqueue_script('loadCSS', RT_SCRIPTS_OPTIMIZER_DIR_URL . '/assets/js/loadCSS.min.js', array(), filemtime(RT_SCRIPTS_OPTIMIZER_DIR_PATH . '/assets/js/loadCSS.min.js'), false);
}
add_action('wp_enqueue_scripts', 'rt_scripts_optimizer_load_scripts');

/**
 * Replaces the `src` attribute of iframe tags with `data-src` to prevent automatic loading of iframes on page load.
 *
 * This allows JavaScript to control when iframes are loaded, enabling lazy loading for improved performance.
 *
 * @param string $content The original post content.
 * @return string The modified content with iframe `src` attributes replaced.
 */
function rt_scripts_optimizer_iframe_lazy_loading($content)
{

	return preg_replace('~<iframe[^>]*\K (?=src=)~i', ' data-', $content);
}

if ('1' !== get_option('rt_scripts_optimizer_disable_js_optimizations')) {
	add_action('the_content', 'rt_scripts_optimizer_iframe_lazy_loading', PHP_INT_MAX);
}

/**
 * Modifies embed block output to defer loading of Reddit and Twitter embed scripts.
 *
 * For Gutenberg embed blocks with provider 'reddit' or 'twitter', changes the `<script>` tags to use `type='text/rtscript-noautoload'` to prevent automatic script execution.
 *
 * @param string $block_content The rendered HTML content of the block.
 * @param array $block The block's data array.
 * @return string The modified block content with deferred script tags for supported providers.
 */
function rt_scripts_optimizer_modify_embeds($block_content, $block)
{

	if ('core/embed' === $block['blockName'] && in_array($block['attrs']['providerNameSlug'], ['reddit', 'twitter'], true)) {

		$block_content = preg_replace('~<script~i', '<script type=\'text/rtscript-noautoload\'', $block_content);
	}

	return $block_content;
}

if ('1' !== get_option('rt_scripts_optimizer_disable_js_optimizations')) {
	add_filter('render_block', 'rt_scripts_optimizer_modify_embeds', 10, 2);
}
