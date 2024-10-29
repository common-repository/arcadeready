<?php
class ArcadeReady_ShortcodeHandler {

	public $gameHandler;

	public function __construct( $gameHandler ) {
		$this->gameHandler = $gameHandler;
		add_shortcode( 'ARgame', array( $this, 'load' ) );
	}
	public function load( $atts ) {
		$atts = shortcode_atts( array(
			'game' => '0',
			'data' => 'game',
			'structure' => false,
			'type' => false,
			'class' => false,
			'itemclass' => false,
			'bef' => false,
			'sep' => false,
			'aft' => false,
		), $atts, 'ARgame' );
		$postData = get_post( $atts['game'] );
		if ( $postData === null ) {
			return '**ID error.**';
		}
		if ( $postData->post_type !== 'ar_games' ) {
			return '**ID is not for a game.**';
		}
		$metaData = get_post_meta( $postData->ID );

		switch ( $atts['data'] ) {
			case 'game':
				return $this->gameHandler->getEmbedCode( $postData->ID,$metaData['ar_gameFile'][0] );
			break;
			case 'title':
				return esc_html( $postData->post_title );
			break;
			case 'added':
				if ( !isset( $metaData['ar_gameAdded'][0] ) || empty( $metaData['ar_gameAdded'][0] ) ) {
					return mysql2date( get_option( 'date_format' ), $postData->post_date );
				}
				return mysql2date( get_option( 'date_format' ), $metaData['ar_gameAdded'][0] );
			break;
			case 'description':
				return esc_html( $postData->post_content );
			break;
			case 'shortDescription':
				return esc_html( $postData->post_excerpt );
			break;
			case 'instruction':
				return esc_html( $metaData['ar_gameInst'][0] );
			break;
			case 'screenshots':
				$type = isset( $atts['type'] ) ? $atts['type'] : 'ul';
				$class = isset( $atts['class'] ) ? $atts['class'] : false;
				$itemClass = isset( $atts['itemclass'] ) ? $atts['itemclass'] : false;
				$screenshots = explode( ',', $metaData['ar_screenshots'][0] );
				if ( isset( $atts['structure'] ) && $atts['structure'] == 'img' ) {
					return $this->processScreenshots( $screenshots, 'img', $type, $class, $itemClass  );
				} else if ( isset( $atts['structure'] ) && $atts['structure'] == 'array' ) {
					$tagArr = array();
					foreach ( $screenshots as $screenshot ) {
						$link = wp_get_attachment_url( $screenshot );
						if ( $link ) {
							if ( $atts['type'] == 'A' ) {
								$tagArr[$screenshot] = array(
									'ID'  => $screenshot,
									'url' => $link
								);
							} else {
								$tagArr[] = array(
									'ID'  => $screenshot,
									'url' => $link
								);
							}
						}
					}
					return $tagArr;
				} else {
					return $this->processScreenshots( $screenshots, 'list', $type, $class, $itemClass   );
				}
			break;
			case 'categories':
				$tags = wp_get_post_terms( $atts['game'], 'gamecategory' );
				if ( is_wp_error( $tags ) || count ( $tags ) == 0 ) {
					return;
				}
				if ( isset( $atts['structure'] ) && $atts['structure'] == 'links' ) {
					$type = false;
					$class = false;
					$itemClass = isset( $atts['itemclass'] ) ? $atts['itemclass'] : false;
					$bef = isset( $atts['bef'] ) ? $atts['bef'] : false;
					$sep= isset( $atts['sep'] ) ? $atts['sep'] : false;
					$aft = isset( $atts['aft'] ) ? $atts['aft'] : false;
					return $this->processTax( $tags, 'links', $type, $class, $itemClass, $bef, $sep, $aft );
				} else if ( isset( $atts['structure'] ) && $atts['structure'] == 'array' ) {
					$tagArr = array();
					foreach ( $tags as $tag ) {
						if ( $atts['type'] == 'A' ) {
							$tagArr[$tag->name] = array(
								'ID' 		  => $tag->term_id,
								'name' 		  => $tag->name,
								'slug' 		  => $tag->slug,
								'description' => $tag->description,
							);
						} else {
							$tagArr[] = array(
								'ID' 		  => $tag->term_id,
								'name' 		  => $tag->name,
								'slug' 		  => $tag->slug,
								'description' => $tag->description,
							);
						}
					}
					return $tagArr;
				} else {
					$type = isset( $atts['type'] ) ? $atts['type'] : 'ul';
					$class = isset( $atts['class'] ) ? $atts['class'] : false;
					$itemClass = isset( $atts['itemclass'] ) ? $atts['itemclass'] : false;
					return $this->processTax( $tags, 'list', $type, $class, $itemClass );
				}
			break;
			case 'tags':
				$tags = wp_get_post_terms( $atts['game'], 'gametags' );
				if ( is_wp_error( $tags ) || count ( $tags ) == 0 ) {
					return;
				}
				if ( isset( $atts['structure'] ) && $atts['structure'] == 'links' ) {
					$type = false;
					$class = false;
					$itemClass = isset( $atts['itemclass'] ) ? $atts['itemclass'] : false;
					$bef = isset( $atts['bef'] ) ? $atts['bef'] : false;
					$sep= isset( $atts['sep'] ) ? $atts['sep'] : false;
					$aft = isset( $atts['aft'] ) ? $atts['aft'] : false;
					return $this->processTax( $tags, 'links', $type, $class, $itemClass, $bef, $sep, $aft );
				} else if ( isset( $atts['structure'] ) && $atts['structure'] == 'array' ) {
					$tagArr = array();
					foreach ( $tags as $tag ) {
						if ( $atts['type'] == 'A' ) {
							$tagArr[$tag->name] = array(
								'ID' 		  => $tag->term_id,
								'name' 		  => $tag->name,
								'slug' 		  => $tag->slug,
								'description' => $tag->description,
							);
						} else {
							$tagArr[] = array(
								'ID' 		  => $tag->term_id,
								'name' 		  => $tag->name,
								'slug' 		  => $tag->slug,
								'description' => $tag->description,
							);
						}
					}
					return $tagArr;
				} else {
					$type = isset( $atts['type'] ) ? $atts['type'] : 'ul';
					$class = isset( $atts['class'] ) ? $atts['class'] : false;
					$itemClass = isset( $atts['itemclass'] ) ? $atts['itemclass'] : false;
					return $this->processTax( $tags, 'list', $type, $class, $itemClass );
				}
			break;
			case 'author':
				return esc_html( $metaData['ar_gameCreator'][0] );
			break;
			case 'authorLink':
				if ( isset( $atts['class'] ) ) {
					return $this->processAuthorLink( $metaData['ar_gameCreator'][0], $metaData['ar_gameCreatorWebsite'][0], $atts['class'] );
				} else {
					return $this->processAuthorLink( $metaData['ar_gameCreator'][0], $metaData['ar_gameCreatorWebsite'][0] );
				}
			break;
			default:
				return '**Game data - ' . $atts['data'] . ' - not found**';
		}
	}
	private function dateAdded( $date ) {

	}
	private function processScreenshots( $screenshots, $structure, $type = false, $class = false, $itemClass = false ) {
		$html = '';
		if ( $class ) {
			$class = ' class="' . $class . '"';
		} else {
			$class = '';
		}
		if ( $itemClass ) {
			$itemClass = ' class="' . $itemClass . '"';
		} else {
			$itemClass = '';
		}

		if ( $structure == 'list' ) {
			if ( $type && $type == 'ol' ) {
				$htmlStart = '<ol' . $class . '>';
				$htmlEnd   = '</ol>';
			} else {
				$htmlStart = '<ul' . $class . '>';
				$htmlEnd   = '</ul>';
			}
			foreach ( $screenshots as $screenshot ) {
				$link = wp_get_attachment_url( $screenshot );
				if ( $link ) {
					$html .= '<li' . $itemClass . '><img src="' . $link . '" alt="Screenshot" /></li>';
				}
			}
			return $htmlStart . $html . $htmlEnd;
		} else if ( $structure == 'img' ) {
			foreach ( $screenshots as $screenshot ) {
				$link = wp_get_attachment_url( $screenshot );
				if ( $link ) {
					$html .= '<img' . $itemClass . ' src="' . $link . '" alt="Screenshot" />';
				}
			}
			return $html;
		}

	}
	private function processTax( $tags, $structure, $type, $class, $itemClass, $bef = false, $sep = false, $aft = false ) {
		$html = '';
		if ( $class ) {
			$class = ' class="' . $class . '"';
		} else {
			$class = '';
		}
		if ( $itemClass ) {
			$itemClass = ' class="' . $itemClass . '"';
		} else {
			$itemClass = '';
		}

		if ( $structure == 'list' ) {
			if ( $type && $type == 'ol' ) {
				$htmlStart = '<ol' . $class . '>';
				$htmlEnd   = '</ol>';
			} else {
				$htmlStart = '<ul' . $class . '>';
				$htmlEnd   = '</ul>';
			}
			foreach ( $tags as $tag ) {
				$link  = get_term_link( $tag->term_id, $tag->taxonomy );
				$html .= '<li' . $itemClass . '><a href="' . $link . '">' . $tag->name . '</a></li>';
			}
			return $htmlStart . $html . $htmlEnd;
		} else if ( $structure == 'links' ) {
			$htmlStart = $bef ? $bef : '';
			$htmlEnd   = $aft ? $aft : '';
			$htmlSep   = $sep ? $sep : '';
			foreach ( $tags as $tag ) {
				$link  = get_term_link( $tag->term_id, $tag->taxonomy );
				$html .= '<a' . $itemClass . ' href="' . $link . '">' . $tag->name . '</a>' . $htmlSep;
			}
			$html = rtrim( $html, $htmlSep );
			return $htmlStart . $html . $htmlEnd;
		}
	}
	private function processAuthorLink( $creator, $website, $class = false ) {
		if ( $class !== false ) {
			return '<a class="' . $class . '" href="' . esc_url( $website ) . '">' . $creator . '</a>';
		} else {
			return '<a href="' . esc_url( $website ) . '">' . $creator . '</a>';
		}
	}
}
$ARgame = new ArcadeReady_ShortcodeHandler( $ARgameHandler );
?>
