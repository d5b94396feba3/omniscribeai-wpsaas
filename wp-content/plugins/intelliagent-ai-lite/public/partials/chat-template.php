<?php
/**
 * Frontend Chat Widget Template
 *
 * This template renders the user-facing chat interface, including the
 * floating launcher button, chat window, message bubbles, and recent
 * conversation history.
 *
 * @package           IntelliAgent_AI_Lite
 * @subpackage        Public/Partials
 * @link              https://aica-intelliagent.com/
 * @since             1.0.0
 */

defined( 'ABSPATH' ) || exit;

/* --- Widget Data Preparation --- */
$inteaili_logo_id       = get_option( 'inteaili_logo_id' );
$inteaili_logo_url      = $inteaili_logo_id ? wp_get_attachment_url( $inteaili_logo_id ) : esc_url( INTEAILI_URL . 'assets/images/default-avater.svg' );
$inteaili_display_name  = get_option( 'inteaili_display_name', esc_html__( 'AI Assistant', 'intelliagent-ai-lite' ) );
$inteaili_welcome_msg   = get_option( 'inteaili_welcome_msg', __( "Hi there! I'm your friendly AI Assistant.\nHow can I help you today?", 'intelliagent-ai-lite' ) );
$inteaili_placeholder   = get_option( 'inteaili_placeholder', esc_html__( 'Message...', 'intelliagent-ai-lite' ) );
$inteaili_primary_color = get_option( 'inteaili_primary_color', '#000000' );
$inteaili_theme_mode    = get_option( 'inteaili_theme_mode', 'light' );

// Process welcome message into individual chat bubbles.
$inteaili_welcome_lines = array_filter( explode( "\n", str_replace( "\r", '', $inteaili_welcome_msg ) ) );
$inteaili_chat_history  = isset( $chat_history ) ? $chat_history : array();
?>

<div id="inteaili-wrapper" class="theme-<?php echo esc_attr( $inteaili_theme_mode ); ?>">    
	<div id="inteaili-c-w" class="hidden">
		<div class="chat-header">
			<div class="header-info">
				<img src="<?php echo esc_url( $inteaili_logo_url ); ?>" class="header-logo" alt="<?php echo esc_attr( $inteaili_display_name ); ?>">
				<span class="header-name"><?php echo esc_html( $inteaili_display_name ); ?></span>
			</div>
			<div class="header-actions">
				<button id="chat-menu-btn" class="header-action-btn" title="<?php esc_attr_e( 'Menu', 'intelliagent-ai-lite' ); ?>">
					<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#6b7280" stroke-width="2.5">
						<circle cx="5" cy="12" r="1.5" fill="currentColor"></circle>
						<circle cx="12" cy="12" r="1.5" fill="currentColor"></circle>
						<circle cx="19" cy="12" r="1.5" fill="currentColor"></circle>
					</svg>
				</button>
				<div id="chat-options-popup" class="hidden">
					<button class="menu-item" id="menu-start-new">
						<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
						<?php esc_html_e( 'Start a new chat', 'intelliagent-ai-lite' ); ?>
					</button>
					<button class="menu-item" id="menu-end-chat">
						<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" ><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
						<?php esc_html_e( 'End chat', 'intelliagent-ai-lite' ); ?>
					</button>
					<button class="menu-item" id="menu-view-recent">
						<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
						<?php esc_html_e( 'View recent chats', 'intelliagent-ai-lite' ); ?>
					</button>
				</div>
			</div>
		</div>

		<div id="recent-chats-view" class="hidden">
			<div class="recent-header">
				<button id="back-to-chat" class="header-action-btn" title="<?php esc_attr_e( 'Back to chat', 'intelliagent-ai-lite' ); ?>">
					<svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
						<polyline points="15 18 9 12 15 6"></polyline>
					</svg>
				</button>
				<span><?php esc_html_e( 'Recent Conversations', 'intelliagent-ai-lite' ); ?></span>
			</div>
			<div id="recent-chats-list">
				<div class="recent-loading">
					<span class="spinner is-active"></span>
					<span><?php esc_html_e( 'Loading...', 'intelliagent-ai-lite' ); ?></span>
				</div>
			</div>
		</div>

		<div id="chat-confirm-overlay" class="hidden">
			<div class="confirm-box">
				<h4 id="confirm-title"><?php esc_html_e( 'Clear conversation?', 'intelliagent-ai-lite' ); ?></h4>
				<p id="confirm-desc"><?php esc_html_e( 'This will delete your current chat history and start a fresh session.', 'intelliagent-ai-lite' ); ?></p>
				<div class="confirm-actions">
					<button id="confirm-cancel" class="btn-secondary"><?php esc_html_e( 'Cancel', 'intelliagent-ai-lite' ); ?></button>
					<button id="confirm-execute" class="btn-primary"><?php esc_html_e( 'Confirm', 'intelliagent-ai-lite' ); ?></button>
				</div>
			</div>
		</div>

		<div id="chat-messages">
			<?php foreach ( $inteaili_welcome_lines as $inteaili_index => $inteaili_line ) : ?>
				<div class="msg ai welcome-msg">
					<div class="msg-avatar-container">
						<?php
						if ( 0 === $inteaili_index ) :
							?>
							<img src="<?php echo esc_url( $inteaili_logo_url ); ?>" class="msg-avatar" alt=""><?php endif; ?>
					</div>
					<div class="msg-content-wrapper">
						<?php
						if ( 0 === $inteaili_index ) :
							?>
							<div class="msg-bot-name"><?php echo esc_html( $inteaili_display_name ); ?></div><?php endif; ?>
						<div class="bubble"><?php echo esc_html( $inteaili_line ); ?></div>
					</div>
				</div>
			<?php endforeach; ?>

			<?php if ( ! empty( $inteaili_chat_history ) ) : ?>
				<?php
				foreach ( $inteaili_chat_history as $inteaili_msg ) :
					$inteaili_role    = ( 'user' === $inteaili_msg['role'] ) ? 'user' : 'ai';
					$inteaili_is_bot  = ( 'ai' === $inteaili_role );
					$inteaili_sent_at = isset( $inteaili_msg['time'] ) ? (int) $inteaili_msg['time'] : time();
					$inteaili_diff    = time() - $inteaili_sent_at;

					if ( $inteaili_diff < 60 ) {
						$inteaili_time_text = esc_html__( 'just now', 'intelliagent-ai-lite' );
					} else {
						$inteaili_minutes = floor( $inteaili_diff / 60 );
						/* translators: %d: minutes ago */
						$inteaili_time_text = sprintf( _n( '%d min ago', '%d mins ago', $inteaili_minutes, 'intelliagent-ai-lite' ), $inteaili_minutes );
					}
					?>
					<div class="msg <?php echo esc_attr( $inteaili_role ); ?>">
						<?php if ( $inteaili_is_bot ) : ?>
							<div class="msg-avatar-container">
								<img src="<?php echo esc_url( $inteaili_logo_url ); ?>" class="msg-avatar" alt="">
							</div>
						<?php endif; ?>
						<div class="msg-content-wrapper">
							<?php if ( $inteaili_is_bot ) : ?>
								<div class="msg-bot-name"><?php echo esc_html( $inteaili_display_name ); ?></div>
							<?php endif; ?>
							<div class="bubble"><?php echo wp_kses_post( $inteaili_msg['content'] ); ?></div>
							<?php if ( $inteaili_is_bot ) : ?>
								<div class="msg-footer">
									<span class="msg-time" data-time="<?php echo esc_attr( $inteaili_sent_at ); ?>"><?php echo esc_html( $inteaili_time_text ); ?></span>
									<div class="msg-actions">
										<button class="ai-action-btn copy-msg" title="<?php esc_attr_e( 'Copy', 'intelliagent-ai-lite' ); ?>">
											<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
										</button>
										<button class="ai-action-btn regen-msg" title="<?php esc_attr_e( 'Regenerate', 'intelliagent-ai-lite' ); ?>">
											<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 4 23 10 17 10"></polyline><polyline points="1 20 1 14 7 14"></polyline><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path></svg>
										</button>
										<button class="ai-action-btn rate-msg like" data-rating="like" title="<?php esc_attr_e( 'Helpful', 'intelliagent-ai-lite' ); ?>">
											<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3zM7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3"></path></svg>
										</button>
										<button class="ai-action-btn rate-msg dislike" data-rating="dislike" title="<?php esc_attr_e( 'Not helpful', 'intelliagent-ai-lite' ); ?>">
											<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 15v4a3 3 0 0 0 3 3l4-9V2H5.72a2 2 0 0 0-2 1.7l-1.38 9a2 2 0 0 0 2 2.3zm7-13h3a2 2 0 0 1 2 2v7a2 2 0 0 1-2 2h-3"></path></svg>
										</button>
									</div>
								</div>
							<?php endif; ?>
						</div>
					</div>
				<?php endforeach; ?>
			<?php endif; ?>
		</div>

		<div class="chat-input-area">
			<?php if ( (int) get_option( 'inteaili_show_branding', 0 ) === 1 ) : ?>
				<div class="powered-by-brand">
					<?php
					printf(
						/* translators: %s: AI Provider Name */
						esc_html__( 'Powered by %s', 'intelliagent-ai-lite' ),
						'IntelliAgent AI'
					);
					?>
				</div>
			<?php endif; ?>
			
			<div class="input-wrapper">
				<textarea id="chat-input" placeholder="<?php echo esc_attr( $inteaili_placeholder ); ?>" rows="1"></textarea>
				<button id="chat-send" class="action-icon" aria-label="<?php esc_attr_e( 'Send message', 'intelliagent-ai-lite' ); ?>">
					<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="2"><path d="M22 2L11 13M22 2l-7 20-4-9-9-4 20-7z"/></svg>
				</button>
			</div>
		</div>
	</div>

	<div id="ai-chat-welcome-popup" class="hidden welcome-text-click">
		<div class="popup-bubbles-stack">
			<?php foreach ( $inteaili_welcome_lines as $inteaili_index => $inteaili_line ) : ?>
				<div class="popup-bubble-item">
					<?php
					if ( 0 === $inteaili_index ) :
						?>
						<button id="close-welcome-popup" title="<?php esc_attr_e( 'Close', 'intelliagent-ai-lite' ); ?>">&times;</button><?php endif; ?>
					<?php echo esc_html( $inteaili_line ); ?>
				</div>
			<?php endforeach; ?>
		</div>
	</div>

	<button id="ai-chat-bubble" class="launcher-btn" style="background-color: <?php echo esc_attr( $inteaili_primary_color ); ?>;" aria-label="<?php esc_attr_e( 'Open Chat', 'intelliagent-ai-lite' ); ?>">
		<div class="launcher-img-container <?php echo ( 'default' === get_option( 'inteaili_bubble_icon', 'default' ) ) ? 'is-default-icon' : ''; ?>">
			<?php if ( 'logo' === get_option( 'inteaili_bubble_icon', 'default' ) ) : ?>
				<img src="<?php echo esc_url( $inteaili_logo_url ); ?>" class="launcher-img" alt="">
			<?php else : ?>
				<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
					<path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
				</svg>
			<?php endif; ?>
		</div>
		<div class="launcher-close-icon hidden">
			<svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
				<path d="M6 9l6 6 6-6"/>
			</svg>
		</div>
	</button>
</div>