<?php

class ArcadeReady {
	public static function setMessage( $id, $message, $type, $dismissable = true ) {
		if ( !current_user_can('manage_options') ) {
			wp_die( 'fsdfsd' );
		}
		$messages = get_transient( $id );
		$messageArr['message'] = $message;
		$messageArr['typeTag'] = '';
		$messageArr['dismissableTag'] = '';
		if ( $type == 'success' ) {
			$messageArr['typeTag'] = 'notice-success';
		} else if ( $type == 'error' ) {
			$messageArr['typeTag'] = 'notice-error';
		}
		if ( $dismissable ) {
			$messageArr['dismissableTag'] = 'is-dismissible';
		}
		$messages[] = $messageArr;
		set_transient( $id, $messages, 60*10 );
	}
	public static function javascriptTranslations( $frameTypes ) {
		$translation_array = array(
			'frameTypes' => $frameTypes,
			'shortcodePrompt' => __( 'Insert: Press Enter. or; Copy to clipboard: Ctrl+C, Esc', 'arcade-ready-tr' )
		);
		return $translation_array;
	}
	public static function buildGameList() {
		global $wpdb;
		$query = 'SELECT ID,post_title FROM ' . $wpdb->posts . ' WHERE post_status = "publish" AND post_type = "ar_games"';
		$results = $wpdb->get_results( $query, ARRAY_A );
		$html = '<div id="ARgamesList"><h1>' . __( 'List of games:', 'arcade-ready-tr' ) . '</h1><span class="closeList">' . _x( 'close', 'Close the games list', 'arcade-ready-tr' ) . '</span><ul>';
		foreach ( $results as $game ) {
			$html .= '<li>
						<span class="gameTitle"><i class="fa fa-plus-circle"></i> ID: ' . $game['ID'] . ' - ' . $game['post_title'] .'</span>
						<div class="shortcodes">
							<i title="' . __( 'Game Embed Shortcode', 'arcade-ready-tr' ) . '" class="fa fa-file-code-o"><span>[ARgame game="' . $game['ID'] . '"]</span></i>
							<i title="' . __( 'Description', 'arcade-ready-tr' ) . '" class="fa fa-align-left"><span>[ARgame game="' . $game['ID'] . '" data="description"]</span></i>
							<i title="' . __( 'Short Description', 'arcade-ready-tr' ) . '" class="fa fa-align-center"><span>[ARgame game="' . $game['ID'] . '" data="shortDescription"]</span></i>
							<i title="' . __( 'Instructions', 'arcade-ready-tr' ) . '" class="fa fa-info-circle"><span>[ARgame game="' . $game['ID'] . '" data="instruction"]</span></i>
							<i title="' . __( 'Game Added on', 'arcade-ready-tr' ) . '" class="fa fa-clock-o"><span>[ARgame game="' . $game['ID'] . '" data="added"]</span></i>
							<i title="' . __( 'Screenshots', 'arcade-ready-tr' ) . '" class="fa fa-picture-o"><span>[ARgame game="' . $game['ID'] . '" data="screenshots" structure="list"]</span></i>
							<i title="' . __( 'Categories', 'arcade-ready-tr' ) . '" class="fa fa-list-ol"><span>[ARgame game="' . $game['ID'] . '" data="categories" structure="links"]</span></i>
							<i title="' . __( 'Tags', 'arcade-ready-tr' ) . '" class="fa fa-tags"><span>[ARgame game="' . $game['ID'] . '" data="tags" structure="list"]</span></i>
							<i title="' . __( 'Author Name', 'arcade-ready-tr' ) . '" class="fa fa-user"><span>[ARgame game="' . $game['ID'] . '" data="author"]</span></i>
							<i title="' . __( 'Author Link', 'arcade-ready-tr' ) . '" class="fa fa-external-link"><span>[ARgame game="' . $game['ID'] . '" data="authorLink"]</span></i>
						</div>
					  </li>';
		}
		$html .= '</ul></div>';
		echo $html;
	}
	public static function showGamesListButton( ) {
		if( get_post_type() != 'ar_games' ) {
			echo '<a onClick="return false;" href="#" id="ARshowGamesList" class="button"><i class="fa fa-gamepad"></i> ' . _x( 'Show Games List', 'Show games list button text', 'arcade-ready-tr' ) . '</a>';
		}
	}
	public static function uninstallPlugin() {
		delete_option( 'ArcadeReadyOptions' );
	}
}
?>
