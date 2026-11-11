<?php
/**
 * Register all actions and filters for the plugin.
 *
 * @link       https://aica-intelliagent.com/
 * @since      1.0.0
 * @package    IntelliAgent_AI_Lite
 * @subpackage IntelliAgent_AI_Lite/includes
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register all actions and filters for the plugin.
 *
 * Maintains a list of all hooks that are registered throughout
 * the plugin, and registers them with the WordPress API when the run
 * function is called.
 */
class INTEAILI_Loader {

	/**
	 * The array of actions registered with WordPress.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      array    $actions    The actions registered with WordPress to run when the filter is fired.
	 */
	protected $actions = array();

	/**
	 * The array of filters registered with WordPress.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      array    $filters    The filters registered with WordPress to run when the filter is fired.
	 */
	protected $filters = array();

	/**
	 * The array of shortcodes registered with WordPress.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      array    $shortcodes    The shortcodes registered with WordPress.
	 */
	protected $shortcodes = array();

	/**
	 * Add a new action to the collection to be registered with WordPress.
	 *
	 * @since    1.0.0
	 * @param    string $hook             The name of the WordPress action that is being registered.
	 * @param    object $component        A reference to the instance of the object on which the action is defined.
	 * @param    string $callback         The name of the function definition on the $component.
	 * @param    int    $priority         Optional. The priority at which the function should be fired. Default is 10.
	 * @param    int    $accepted_args    Optional. The number of arguments that should be passed to the $callback. Default is 1.
	 */
	public function add_action( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) {
		$this->actions = $this->add( $this->actions, $hook, $component, $callback, $priority, $accepted_args );
	}

	/**
	 * Add a new filter to the collection to be registered with WordPress.
	 *
	 * @since    1.0.0
	 * @param    string $hook             The name of the WordPress filter that is being registered.
	 * @param    object $component        A reference to the instance of the object on which the filter is defined.
	 * @param    string $callback         The name of the function definition on the $component.
	 * @param    int    $priority         Optional. The priority at which the function should be fired. Default is 10.
	 * @param    int    $accepted_args    Optional. The number of arguments that should be passed to the $callback. Default is 1.
	 */
	public function add_filter( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) {
		$this->filters = $this->add( $this->filters, $hook, $component, $callback, $priority, $accepted_args );
	}

	/**
	 * Add a new shortcode to the collection to be registered with WordPress.
	 *
	 * @since    1.0.0
	 * @param    string $tag              The name of the shortcode tag.
	 * @param    object $component        A reference to the instance of the object on which the shortcode is defined.
	 * @param    string $callback         The name of the function definition on the $component.
	 */
	public function add_shortcode( $tag, $component, $callback ) {
		$this->shortcodes[] = array(
			'tag'       => $tag,
			'component' => $component,
			'callback'  => $callback,
		);
	}

	/**
	 * A utility function that is used to register the actions and filters into a single collection.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @param    array  $hooks            The collection of hooks that is being registered (that is, actions or filters).
	 * @param    string $hook             The name of the WordPress filter that is being registered.
	 * @param    object $component        A reference to the instance of the object on which the filter is defined.
	 * @param    string $callback         The name of the function definition on the $component.
	 * @param    int    $priority         The priority at which the function should be fired.
	 * @param    int    $accepted_args    The number of arguments that should be passed to the $callback.
	 * @return   array                                  The collection of hooks that is being registered.
	 */
	private function add( $hooks, $hook, $component, $callback, $priority, $accepted_args ) {
		$hooks[] = array(
			'hook'          => $hook,
			'component'     => $component,
			'callback'      => $callback,
			'priority'      => $priority,
			'accepted_args' => $accepted_args,
		);

		return $hooks;
	}

	/**
	 * Register the filters, actions, and shortcodes with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		foreach ( $this->filters as $hook ) {
			add_filter(
				$hook['hook'],
				array( $hook['component'], $hook['callback'] ),
				$hook['priority'],
				$hook['accepted_args']
			);
		}

		foreach ( $this->actions as $hook ) {
			add_action(
				$hook['hook'],
				array( $hook['component'], $hook['callback'] ),
				$hook['priority'],
				$hook['accepted_args']
			);
		}

		foreach ( $this->shortcodes as $shortcode ) {
			add_shortcode(
				$shortcode['tag'],
				array( $shortcode['component'], $shortcode['callback'] )
			);
		}
	}

	/**
	 * Get counts for debugging.
	 *
	 * @since    1.0.0
	 * @return   array    Counts of actions, filters, and shortcodes.
	 */
	public function get_hook_counts() {
		return array(
			'actions'    => count( $this->actions ),
			'filters'    => count( $this->filters ),
			'shortcodes' => count( $this->shortcodes ),
		);
	}

	/**
	 * Get all registered hooks for debugging.
	 *
	 * @since    1.0.0
	 * @return   array    All registered actions, filters, and shortcodes.
	 */
	public function get_registered_hooks() {
		return array(
			'actions'    => $this->actions,
			'filters'    => $this->filters,
			'shortcodes' => $this->shortcodes,
		);
	}
}
