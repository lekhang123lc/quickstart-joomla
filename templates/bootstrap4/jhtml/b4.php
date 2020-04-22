<?php

use Jaio\UI\bootstrap4;

class JHtmlB4
{
    protected static $loaded = array();

    public static function css($arrKeys, $useMin = false)
	{
        foreach($arrKeys as $key){
			list($links, $parents) = bootstrap4::findCss($key, $useMin);
			static::css($parents, $useMin);
            foreach($links as $link) JFactory::getDocument()->addStylesheet( JUri::root(true). '/'.$link );
        }
    }

    public static function js($arrKeys, $useMin = false)
	{
        foreach($arrKeys as $key){
			list($links, $parents) = bootstrap4::findJs($key, $useMin);
			static::js($parents, $useMin);
            foreach($links as $link) JFactory::getDocument()->addScript( JUri::root(true). '/'.$link );
        }
    }

    public static function jqueryframework($noConflict = true, $debug = null, $migrate = true)
	{
		// If no debugging value is set, use the configuration setting
		if ($debug === null)
		{
			$debug = (boolean) JFactory::getConfig()->get('debug');
		}

        JHtml::_('b4.js', ['jquery'],true);

		// Check if we are loading in noConflict
		if ($noConflict)
		{
			JHtml::_('script', 'jui/jquery-noconflict.js', array('version' => 'auto', 'relative' => true));
		}

		// Check if we are loading Migrate
		if ($migrate)
		{
			JHtml::_('script', 'jui/jquery-migrate.min.js', array('version' => 'auto', 'relative' => true, 'detectDebug' => $debug));
		}

		return;
	}

	public static function behaviorcaption($selector = 'img.caption')
	{
		JLog::add('JHtmlBehavior::caption is deprecated. Use native HTML figure tags.', JLog::WARNING, 'deprecated');

		// Only load once
		if (isset(static::$loaded[__METHOD__][$selector]))
		{
			return;
		}

		// Include jQuery
		JHtml::_('b4.jqueryframework');

		JHtml::_('script', 'system/caption.js', array('version' => 'auto', 'relative' => true));

		// Attach caption to document
		JFactory::getDocument()->addScriptDeclaration(
			"jQuery(window).on('load',  function() {
				new JCaption('" . $selector . "');
			});"
		);

		// Set static array
		static::$loaded[__METHOD__][$selector] = true;
	}

	public static function gridsort($title, $order, $direction = 'asc', $selected = '', $task = null, $new_direction = 'asc', $tip = '', $form = null)
	{
		JHtml::_('behavior.core');
		static::popover();

		$direction = strtolower($direction);
		$icon = array('up', 'down');
		$index = (int) ($direction === 'desc');

		if ($order != $selected)
		{
			$direction = $new_direction;
		}
		else
		{
			$direction = $direction === 'desc' ? 'asc' : 'desc';
		}

		if ($form)
		{
			$form = ', document.getElementById(\'' . $form . '\')';
		}

		$html = '<a href="#" onclick="Joomla.tableOrdering(\'' . $order . '\',\'' . $direction . '\',\'' . $task . '\'' . $form . ');return false;"'
			. ' class="hasPopover" title="' . htmlspecialchars(JText::_($tip ?: $title)) . '"'
			. ' data-content="' . htmlspecialchars(JText::_('JGLOBAL_CLICK_TO_SORT_THIS_COLUMN')) . '" data-placement="top">';

		if (isset($title['0']) && $title['0'] === '<')
		{
			$html .= $title;
		}
		else
		{
			$html .= JText::_($title);
		}

		if ($order == $selected)
		{
			$html .= ' <span class="fa fa-caret-' . $icon[$index] . '"></span>';
		}

		$html .= '</a>';

		return $html;
	}

	public static function behaviortabstate()
	{
		if (isset(self::$loaded[__METHOD__]))
		{
			return;
		}
		// Include jQuery
		static::jqueryframework();
		JHtml::_('behavior.polyfill', array('filter','xpath'));
		JHtml::_('script', 'system/tabs-state.js', array('version' => 'auto', 'relative' => true));
		self::$loaded[__METHOD__] = true;
	}

	public static function behaviorformvalidator()
	{
		// Only load once
		if (isset(static::$loaded[__METHOD__]))
		{
			return;
		}

		// Include core
		JHtml::_('behavior.core');
		
		// Include jQuery
		static::jqueryframework();

		// Add validate.js language strings
		JText::script('JLIB_FORM_FIELD_INVALID');

		JHtml::_('script', 'system/punycode.js', array('version' => 'auto', 'relative' => true));
		//JHtml::_('script', 'system/validate.js', array('version' => 'auto', 'relative' => true));

		// Attach tooltips to document
		JFactory::getDocument()
			->addScriptDeclaration("(function() {
				'use strict';
				window.addEventListener('load', function() {
				  // Fetch all the forms we want to apply custom Bootstrap validation styles to
				  var forms = document.getElementsByClassName('needs-validation');
				  // Loop over them and prevent submission
				  var validation = Array.prototype.filter.call(forms, function(form) {
					form.addEventListener('submit', function(event) {
					  if (form.checkValidity() === false) {
						event.preventDefault();
						event.stopPropagation();
					  }
					  form.classList.add('was-validated');
					}, false);
				  });
				}, false);
			  })();");

		static::$loaded[__METHOD__] = true;
	}

	public static function behaviorhighlighter(array $terms, $start = 'highlighter-start', $end = 'highlighter-end', $className = 'highlight', $tag = 'span')
	{
		$sig = md5(serialize(array($terms, $start, $end)));

		if (isset(static::$loaded[__METHOD__][$sig]))
		{
			return;
		}

		$terms = array_filter($terms, 'strlen');

		// Nothing to Highlight
		if (empty($terms))
		{
			static::$loaded[__METHOD__][$sig] = true;

			return;
		}

		// Include core
		JHtml::_('behavior.core');

		// Include jQuery
		static::jqueryframework();

		JHtml::_('script', 'system/highlighter.js', array('version' => 'auto', 'relative' => true));

		foreach ($terms as $i => $term)
		{
			$terms[$i] = JFilterOutput::stringJSSafe($term);
		}

		$document = JFactory::getDocument();
		$document->addScriptDeclaration("
			jQuery(function ($) {
				var start = document.getElementById('" . $start . "');
				var end = document.getElementById('" . $end . "');
				if (!start || !end || !Joomla.Highlighter) {
					return true;
				}
				highlighter = new Joomla.Highlighter({
					startElement: start,
					endElement: end,
					className: '" . $className . "',
					onlyWords: false,
					tag: '" . $tag . "'
				}).highlight([\"" . implode('","', $terms) . "\"]);
				$(start).remove();
				$(end).remove();
			});
		");

		static::$loaded[__METHOD__][$sig] = true;

		return;
	}

	public static function behaviormodal($selector = 'a.modal', $params = array())
	{
		$document = JFactory::getDocument();

		// Load the necessary files if they haven't yet been loaded
		if (!isset(static::$loaded[__METHOD__]))
		{
			// Include MooTools framework
			JHtml::_('behavior.framework',true);

			// Load the JavaScript and css
			JHtml::_('script', 'system/modal.js', array('framework' => true, 'version' => 'auto', 'relative' => true));
			JHtml::_('stylesheet', 'system/modal.css', array('version' => 'auto', 'relative' => true));
		}

		$sig = md5(serialize(array($selector, $params)));

		if (isset(static::$loaded[__METHOD__][$sig]))
		{
			return;
		}

		JLog::add('JHtmlBehavior::modal is deprecated. Use the modal equivalent from bootstrap.', JLog::WARNING, 'deprecated');

		// Setup options object
		$opt['ajaxOptions']   = isset($params['ajaxOptions']) && is_array($params['ajaxOptions']) ? $params['ajaxOptions'] : null;
		$opt['handler']       = isset($params['handler']) ? $params['handler'] : null;
		$opt['parseSecure']   = isset($params['parseSecure']) ? (bool) $params['parseSecure'] : null;
		$opt['closable']      = isset($params['closable']) ? (bool) $params['closable'] : null;
		$opt['closeBtn']      = isset($params['closeBtn']) ? (bool) $params['closeBtn'] : null;
		$opt['iframePreload'] = isset($params['iframePreload']) ? (bool) $params['iframePreload'] : null;
		$opt['iframeOptions'] = isset($params['iframeOptions']) && is_array($params['iframeOptions']) ? $params['iframeOptions'] : null;
		$opt['size']          = isset($params['size']) && is_array($params['size']) ? $params['size'] : null;
		$opt['shadow']        = isset($params['shadow']) ? $params['shadow'] : null;
		$opt['overlay']       = isset($params['overlay']) ? $params['overlay'] : null;
		$opt['onOpen']        = isset($params['onOpen']) ? $params['onOpen'] : null;
		$opt['onClose']       = isset($params['onClose']) ? $params['onClose'] : null;
		$opt['onUpdate']      = isset($params['onUpdate']) ? $params['onUpdate'] : null;
		$opt['onResize']      = isset($params['onResize']) ? $params['onResize'] : null;
		$opt['onMove']        = isset($params['onMove']) ? $params['onMove'] : null;
		$opt['onShow']        = isset($params['onShow']) ? $params['onShow'] : null;
		$opt['onHide']        = isset($params['onHide']) ? $params['onHide'] : null;

		// Include jQuery
		static::jqueryframework();

		if (isset($params['fullScreen']) && (bool) $params['fullScreen'])
		{
			$opt['size']      = array('x' => '\\jQuery(window).width() - 80', 'y' => '\\jQuery(window).height() - 80');
		}

		$options = JHtml::getJSObject($opt);

		// Attach modal behavior to document
		$document
			->addScriptDeclaration(
			"
		jQuery(function($) {
			SqueezeBox.initialize(" . $options . ");
			initSqueezeBox();
			$(document).on('subform-row-add', initSqueezeBox);

			function initSqueezeBox(event, container)
			{
				SqueezeBox.assign($(container || document).find('" . $selector . "').get(), {
					parse: 'rel'
				});
			}
		});

		window.jModalClose = function () {
			SqueezeBox.close();
		};

		// Add extra modal close functionality for tinyMCE-based editors
		document.onreadystatechange = function () {
			if (document.readyState == 'interactive' && typeof tinyMCE != 'undefined' && tinyMCE)
			{
				if (typeof window.jModalClose_no_tinyMCE === 'undefined')
				{
					window.jModalClose_no_tinyMCE = typeof(jModalClose) == 'function'  ?  jModalClose  :  false;

					jModalClose = function () {
						if (window.jModalClose_no_tinyMCE) window.jModalClose_no_tinyMCE.apply(this, arguments);
						tinyMCE.activeEditor.windowManager.close();
					};
				}

				if (typeof window.SqueezeBoxClose_no_tinyMCE === 'undefined')
				{
					if (typeof(SqueezeBox) == 'undefined')  SqueezeBox = {};
					window.SqueezeBoxClose_no_tinyMCE = typeof(SqueezeBox.close) == 'function'  ?  SqueezeBox.close  :  false;

					SqueezeBox.close = function () {
						if (window.SqueezeBoxClose_no_tinyMCE)  window.SqueezeBoxClose_no_tinyMCE.apply(this, arguments);
						tinyMCE.activeEditor.windowManager.close();
					};
				}
			}
		};
		"
		);

		// Set static array
		static::$loaded[__METHOD__][$sig] = true;

		return;
	}

	public static function behaviortooltip($selector = '.hasTip', $params = array())
	{
		$sig = md5(serialize(array($selector, $params)));

		if (isset(static::$loaded[__METHOD__][$sig]))
		{
			return;
		}

		// Include MooTools framework
		JHtml::_('behavior.framework',true);

		// Setup options object
		$opt['maxTitleChars'] = isset($params['maxTitleChars']) && $params['maxTitleChars'] ? (int) $params['maxTitleChars'] : 50;

		// Offsets needs an array in the format: array('x'=>20, 'y'=>30)
		$opt['offset']    = isset($params['offset']) && is_array($params['offset']) ? $params['offset'] : null;
		$opt['showDelay'] = isset($params['showDelay']) ? (int) $params['showDelay'] : null;
		$opt['hideDelay'] = isset($params['hideDelay']) ? (int) $params['hideDelay'] : null;
		$opt['className'] = isset($params['className']) ? $params['className'] : null;
		$opt['fixed']     = isset($params['fixed']) && $params['fixed'];
		$opt['onShow']    = isset($params['onShow']) ? '\\' . $params['onShow'] : null;
		$opt['onHide']    = isset($params['onHide']) ? '\\' . $params['onHide'] : null;

		$options = JHtml::getJSObject($opt);

		// Include jQuery
		static::jqueryframework();

		// Attach tooltips to document
		JFactory::getDocument()->addScriptDeclaration(
			"jQuery(function($) {
			 $('$selector').each(function() {
				var title = $(this).attr('title');
				if (title) {
					var parts = title.split('::', 2);
					var mtelement = document.id(this);
					mtelement.store('tip:title', parts[0]);
					mtelement.store('tip:text', parts[1]);
				}
			});
			var JTooltips = new Tips($('$selector').get(), $options);
		});"
		);

		// Set static array
		static::$loaded[__METHOD__][$sig] = true;

		return;
    }
    
    public static function popover($selector = '.hasPopover', $params = array())
	{
		// Only load once
		if (isset(static::$loaded[__METHOD__][$selector]))
		{
			return;
		}

		// Include Bootstrap framework
		// static::js(['bootstrap']);

		$opt['animation'] = isset($params['animation']) ? $params['animation'] : null;
		$opt['html']      = isset($params['html']) ? $params['html'] : true;
		$opt['placement'] = isset($params['placement']) ? $params['placement'] : null;
		$opt['selector']  = isset($params['selector']) ? $params['selector'] : null;
		$opt['title']     = isset($params['title']) ? $params['title'] : null;
		$opt['trigger']   = isset($params['trigger']) ? $params['trigger'] : 'hover focus';
		$opt['content']   = isset($params['content']) ? $params['content'] : null;
		$opt['delay']     = isset($params['delay']) ? $params['delay'] : null;
		$opt['container'] = isset($params['container']) ? $params['container'] : 'body';
        $opt['template']  = isset($params['template']) ? (string) $params['template'] : null;
        $opt['offset']  = isset($params['offset']) ? (string) $params['offset'] : null;
        $opt['fallbackPlacement']  = isset($params['fallbackPlacement']) ? (string) $params['fallbackPlacement'] : null;
        $opt['boundary']  = isset($params['boundary']) ? (string) $params['boundary'] : null;
        $opt['sanitize']  = isset($params['sanitize']) ? (string) $params['sanitize'] : null;
        $opt['whiteList']  = isset($params['whiteList']) ? (string) $params['whiteList'] : null;
        $opt['sanitizeFn']  = isset($params['sanitizeFn']) ? (string) $params['sanitizeFn'] : null;

		$options = JHtml::getJSObject($opt);

		$initFunction = 'function initPopovers (event, container) { ' .
				'$(container || document).find(' . json_encode($selector) . ').popover(' . $options . ');' .
			'}';

		// Attach the popover to the document
		JFactory::getDocument()->addScriptDeclaration(
			'jQuery(function($){ initPopovers(); $("body").on("subform-row-add", initPopovers); ' . $initFunction . ' });'
		);

		static::$loaded[__METHOD__][$selector] = true;

		return;
	}

	public static function tooltip($selector = '.hasTooltip', $params = array())
	{
		if (!isset(static::$loaded[__METHOD__][$selector]))
		{
			// Include Bootstrap framework
			//static::js(['bootstrap']);

			// Setup options object
			$opt['animation'] = isset($params['animation']) ? (boolean) $params['animation'] : null;
			$opt['html']      = isset($params['html']) ? (boolean) $params['html'] : true;
			$opt['placement'] = isset($params['placement']) ? (string) $params['placement'] : null;
			$opt['selector']  = isset($params['selector']) ? (string) $params['selector'] : null;
			$opt['title']     = isset($params['title']) ? (string) $params['title'] : null;
			$opt['trigger']   = isset($params['trigger']) ? (string) $params['trigger'] : null;
			$opt['delay']     = isset($params['delay']) ? (is_array($params['delay']) ? $params['delay'] : (int) $params['delay']) : null;
			$opt['container'] = isset($params['container']) ? $params['container'] : 'body';
			$opt['template']  = isset($params['template']) ? (string) $params['template'] : null;
			$opt['offset']  = isset($params['offset']) ? (string) $params['offset'] : null;
			$opt['fallbackPlacement']  = isset($params['fallbackPlacement']) ? (string) $params['fallbackPlacement'] : null;
			$opt['boundary']  = isset($params['boundary']) ? (string) $params['boundary'] : null;
			$opt['sanitize']  = isset($params['sanitize']) ? (string) $params['sanitize'] : null;
			$opt['whiteList']  = isset($params['whiteList']) ? (string) $params['whiteList'] : null;
			$opt['sanitizeFn']  = isset($params['sanitizeFn']) ? (string) $params['sanitizeFn'] : null;
			$onShow           = isset($params['onShow']) ? (string) $params['onShow'] : null;
			$onShown          = isset($params['onShown']) ? (string) $params['onShown'] : null;
			$onHide           = isset($params['onHide']) ? (string) $params['onHide'] : null;
			$onHidden         = isset($params['onHidden']) ? (string) $params['onHidden'] : null;
			$inserted         = isset($params['inserted']) ? (string) $params['inserted'] : null;

			$options = JHtml::getJSObject($opt);

			// Build the script.
			$script = array('$(container).find(' . json_encode($selector) . ').tooltip(' . $options . ')');

			if ($onShow)
			{
				$script[] = 'on("show.bs.tooltip", ' . $onShow . ')';
			}

			if ($onShown)
			{
				$script[] = 'on("shown.bs.tooltip", ' . $onShown . ')';
			}

			if ($onHide)
			{
				$script[] = 'on("hide.bs.tooltip", ' . $onHide . ')';
			}

			if ($onHidden)
			{
				$script[] = 'on("hidden.bs.tooltip", ' . $onHidden . ')';
            }
            
			if ($inserted)
			{
				$script[] = 'on("inserted.bs.tooltip", ' . $inserted . ')';
			}

			$initFunction = 'function initTooltips (event, container) { ' .
				'container = container || document;' .
				implode('.', $script) . ';' .
				'}';

			// Attach tooltips to document
			JFactory::getDocument()
				->addScriptDeclaration('jQuery(function($){ initTooltips(); $("body").on("subform-row-add", initTooltips); ' . $initFunction . ' });');

			// Set static array
			static::$loaded[__METHOD__][$selector] = true;
		}

		return;
	}

	public static function jquerytoken($name = 'csrf.token')
	{
		// Only load once
		if (!empty(static::$loaded[__METHOD__][$name]))
		{
			return;
		}

		static::jqueryframework();
		JHtml::_('form.csrf', $name);

		$doc = JFactory::getDocument();

		$doc->addScriptDeclaration(
<<<JS
;(function ($) {
	$.ajaxSetup({
		headers: {
			'X-CSRF-Token': Joomla.getOptions('$name')
		}
	});
})(jQuery);
JS
		);

		static::$loaded[__METHOD__][$name] = true;
	}

	public static function jqueryui(array $components = array('core'), $debug = null)
	{
		// Set an array containing the supported jQuery UI components handled by this method
		$supported = array('core', 'sortable');

		// Include jQuery
		static::jqueryframework();

		// If no debugging value is set, use the configuration setting
		if ($debug === null)
		{
			$debug = JDEBUG;
		}

		// Load each of the requested components
		foreach ($components as $component)
		{
			// Only attempt to load the component if it's supported in core and hasn't already been loaded
			if (in_array($component, $supported) && empty(static::$loaded[__METHOD__][$component]))
			{
				JHtml::_('script', 'jui/jquery.ui.' . $component . '.min.js', array('version' => 'auto', 'relative' => true, 'detectDebug' => $debug));
				static::$loaded[__METHOD__][$component] = true;
			}
		}

		return;
	}

}