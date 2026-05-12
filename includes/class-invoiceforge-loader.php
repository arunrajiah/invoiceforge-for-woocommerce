<?php
/**
 * Hook loader — registers all actions and filters.
 *
 * @package InvoiceForge
 */

namespace InvoiceForge;

defined( 'ABSPATH' ) || exit;

/**
 * Maintains a list of hooks to register with WordPress.
 */
class InvoiceForge_Loader {

	/** @var array<int, array{hook: string, component: object, callback: string, priority: int, accepted_args: int}> */
	private array $actions = [];

	/** @var array<int, array{hook: string, component: object, callback: string, priority: int, accepted_args: int}> */
	private array $filters = [];

	public function add_action( string $hook, object $component, string $callback, int $priority = 10, int $accepted_args = 1 ): void {
		$this->actions[] = compact( 'hook', 'component', 'callback', 'priority', 'accepted_args' );
	}

	public function add_filter( string $hook, object $component, string $callback, int $priority = 10, int $accepted_args = 1 ): void {
		$this->filters[] = compact( 'hook', 'component', 'callback', 'priority', 'accepted_args' );
	}

	public function run(): void {
		foreach ( $this->filters as $hook ) {
			add_filter( $hook['hook'], [ $hook['component'], $hook['callback'] ], $hook['priority'], $hook['accepted_args'] );
		}
		foreach ( $this->actions as $hook ) {
			add_action( $hook['hook'], [ $hook['component'], $hook['callback'] ], $hook['priority'], $hook['accepted_args'] );
		}
	}
}
