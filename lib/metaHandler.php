<?php
class ArcadeReady_MetaHandler {
	private $metaBox;
	private $dataArr;

	public function __construct( $args )
	{
		$this->metaBox = $args;
		$this->metaBox['args']['btn_txt'] = isset( $this->metaBox['args']['btn_txt'] ) ? $this->metaBox['args']['btn_txt'] : 'Upload Media';
		if ( $this->metaBox['args']['type'] == 'media' ) {
			$this->metaBox['args']['frame_title'] = isset( $this->metaBox['args']['frame_title'] ) ? $this->metaBox['args']['btn_txt'] : 'Select Media';
			$this->metaBox['args']['multiple'] = isset( $this->metaBox['args']['multiple'] ) ? $this->metaBox['args']['multiple'] : false;
			$this->metaBox['args']['preview'] = isset( $this->metaBox['args']['preview'] ) ? $this->metaBox['args']['preview'] : false;
			if ( !isset( $this->metaBox['args']['display'] ) || !( $this->metaBox['args']['display'] == 'filename' || $this->metaBox['args']['display'] == 'id' ) ) {
				$this->metaBox['args']['display'] = 'id';
			}
			$this->dataArr = $this->metaBox['args'];
			unset( $this->dataArr['desc'] );
			unset( $this->dataArr['type'] );
		}
		add_action( 'add_meta_boxes_' . $this->metaBox['postType'], array( $this, 'createMetaBox' ) );
		add_action('save_post', array($this,'saveMetaBox'));
		if ( $this->metaBox['args']['type'] == 'media' ) {
			add_action( 'add_meta_boxes_' . $this->metaBox['postType'], array( $this, 'enqueueMediaScripts' ) );
		}
	}
	public function enqueueMediaScripts() {
		wp_enqueue_media();
		wp_enqueue_script( 'bytephp-media-scripts', ARCADEREADY_PLUGIN_URL . '/admin/js/bytephp-media-scripts.js' );
		$this->dataArr['postID'] = $this->metaBox['postID'];
		wp_localize_script( 'bytephp-media-scripts', 'bytephp_mo_' . $this->metaBox['id'], $this->dataArr );
	}
	public function createMetaBox( $post )
	{
		$this->metaBox['postID'] = $post->ID;
		add_meta_box(
	        $this->metaBox['id'],
	        __( $this->metaBox['title'], 'arcade-ready-tr' ),
	        array( $this, 'renderMetaBox' ),
	        $this->metaBox['postType'],
	        isset( $this->metaBox['context'] ) ? $this->metaBox['context'] : 'normal',
            isset( $this->metaBox['priority'] ) ? $this->metaBox['priority'] : 'default',
            $this->metaBox['args']
	    );
	}
	public function renderMetaBox( )
	{
		$postMeta = get_post_meta( $this->metaBox['postID'], $this->metaBox['id'], true );
		if ( isset( $this->metaBox['args']['dataprocess'] ) && $this->metaBox['args']['dataprocess'] == 'base64' ) {
			$postMeta = base64_decode( $postMeta );
		}
		switch( $this->metaBox['args']['type'] )
        {
            case 'textfield':
                wp_nonce_field( 'saveMetaBox', $this->metaBox['id'] . '_nonce' );
        		echo '<input type="text" name="' . $this->metaBox['id'] . '" value="' . $postMeta . '" /><small>' . $this->metaBox['args']['desc'] . '</small><br/>';
            break;
						case 'datepicker':
                wp_nonce_field( 'saveMetaBox', $this->metaBox['id'] . '_nonce' );
        		echo '<input type="text" class="datepicker" name="' . $this->metaBox['id'] . '" value="' . mysql2date( get_option( 'date_format' ), $postMeta ) . '" /><small>' . $this->metaBox['args']['desc'] . '</small><br/>';
            break;
            case 'textarea':
                wp_nonce_field( 'saveMetaBox', $this->metaBox['id'] . '_nonce' );
        		echo '<textarea row="1" cols="40" name="' . $this->metaBox['id'] . '">' . $postMeta . '</textarea><small>' . $this->metaBox['args']['desc'] . '</small><br/>';
            break;
            case 'checkbox':
                wp_nonce_field( 'saveMetaBox', $this->metaBox['id'] . '_nonce' );
        		echo '<input type="checkbox" name="' . $this->metaBox['id'] . '" checked( 1, ' . $postMeta . ', false ) /><small>' . $this->metaBox['args']['desc'] . '</small><br/>';
            break;
            case 'fileupload':
                wp_nonce_field( 'saveMetaBox', $this->metaBox['id'] . '_nonce' );
        		echo '<input type="file" name="' . $this->metaBox['id'] . '" value="" />Current File:' . $postMeta . '<small>' . $this->metaBox['args']['desc'] . '</small><br/>';
            break;
			case 'media':
				wp_nonce_field( 'saveMetaBox', $this->metaBox['id'] . '_nonce' );
				if ( isset( $this->metaBox['args']['preview'] ) && $this->metaBox['args']['preview'] == true ) {
					$mediaList = '';
					foreach ( explode( ',', $postMeta ) as $img )
					{
						$imageSrc = wp_get_attachment_image_src( $img, 'large' );
						if ( $imageSrc !== false ) {
							$mediaList .= '<li><img src="' . $imageSrc[0] . '"/></li>';
						}
					}
					echo '<ul id="' . $this->metaBox['id'] . '-preview" class="media-preview">' . $mediaList . '</ul>';
				}
				// find filename for
				if ( $this->metaBox['args']['display'] == 'filename' ) {
					$filenames = array();
					$attachments = explode( ',', $postMeta );
					foreach ( $attachments as $attachment ) {
						$filenames[] = basename( get_attached_file( $attachment ) );
					}
					$displayValue = implode( ',', $filenames );
				} else {
					$displayValue = $postMeta;
				}
				echo '<input id="' . $this->metaBox['id'] . '-button" type="button" class="button bytephp_upload-button" value="' . $this->metaBox['args']['btn_txt'] . '" />
				<input type="hidden" name="' . $this->metaBox['id'] . '" id="' . $this->metaBox['id'] . '" value="' . $postMeta . '" />
				<input type="text" name="' . $this->metaBox['id'] . '_display" id="' . $this->metaBox['id'] . '_display" value="' . $displayValue . '" readonly="readonly" /><br/><small>' . $this->metaBox['args']['desc'] . '</small><br/>';
			break;
        }
	}
	public function saveMetaBox( $post )
	{
		if ( ! isset( $_POST[$this->metaBox['id'] . '_nonce'] ) ) {
			return;
		}
		if ( ! wp_verify_nonce( $_POST[$this->metaBox['id'] . '_nonce'], 'saveMetaBox' ) ) {
			return;
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( isset( $_POST['post_type'] ) && 'page' == $_POST['post_type'] ) {
			if ( ! current_user_can( 'edit_page', $post ) ) {
				return;
			}
		} else {

			if ( ! current_user_can( 'edit_post', $post ) ) {
				return;
			}
		}
		if ( ! isset( $_POST[$this->metaBox['id']] ) ) {
			return;
		}
		if ( isset( $this->metaBox['args']['dataprocess'] ) && $this->metaBox['args']['dataprocess'] == 'base64' ) {
			$my_data = base64_encode( stripslashes( $_POST[ $this->metaBox['id'] ] ) );
		} else {
			$my_data = sanitize_text_field( $_POST[$this->metaBox['id']] );
		}
		if ( isset( $this->metaBox['args']['type'] ) && $this->metaBox['args']['type'] == 'datepicker' ) {
			if ( !empty( $_POST[$this->metaBox['id']] ) ) {
				$my_data = date( 'Y-m-d H:i:s', strtotime( $_POST[$this->metaBox['id']] ) );
			}
		}
		$output = update_post_meta( $post, $this->metaBox['id'], $my_data );
	}
}
$args = array(
	'id' => 'ar_gameInst',
	'title' => __( 'Game Instructions', 'arcade-ready-tr' ),
	'postType' => 'ar_games',
	'context' => 'normal',
	'priority' => 'high',
	'args' => array(
		'desc' => __( 'Enter instructions on how to play the game.', 'arcade-ready-tr' ),
		'type' => 'textarea',
	)
);
new ArcadeReady_MetaHandler( $args );
$args = array(
	'id' => 'ar_gameAdded',
	'title' => __( 'Game Added On', 'arcade-ready-tr' ),
	'postType' => 'ar_games',
	'context' => 'side',
	'args' => array(
		'desc' => __( 'Defaults to games "published on" date if left empty.', 'arcade-ready-tr' ),
		'type' => 'datepicker',
	)
);
new ArcadeReady_MetaHandler( $args );
$args = array(
	'id' => 'ar_gameCreator',
	'title' => __( 'Game Creator', 'arcade-ready-tr' ),
	'postType' => 'ar_games',
	'context' => 'side',
	'args' => array(
		'desc' => __( 'Name of the game creator.', 'arcade-ready-tr' ),
		'type' => 'textfield',
	)
);
new ArcadeReady_MetaHandler( $args );
$args = array(
	'id' => 'ar_screenshots',
	'title' => __( 'Screenshots', 'arcade-ready-tr' ),
	'postType' => 'ar_games',
	'args' => array(
		'desc' => __( 'Select a list of screenshots', 'arcade-ready-tr' ),
		'frame_title' => __( 'Select Game File', 'arcade-ready-tr' ),
		'frameTypes' => 'image',
		'multiple' => true,
		'preview' => true,
		'display' => 'id',
		'btn_txt' => __( 'Select Screenshots', 'arcade-ready-tr' ),
		'type' => 'media',
	)
);
new ArcadeReady_MetaHandler( $args );
$args = array(
	'id' => 'ar_gameFile',
	'title' => __( 'Game File', 'arcade-ready-tr' ),
	'postType' => 'ar_games',
	'args' => array(
		'desc' => __( 'Gamefile to embed.', 'arcade-ready-tr' ),
		'frame_title' => __( 'Select Game File', 'arcade-ready-tr' ),
		'frameTypes' => 'games',
		'multiple' => false,
		'preview' => false,
		'display' => 'filename',
		'btn_txt' => __( 'Select Game File', 'arcade-ready-tr' ),
		'type' => 'media',
	)
);
new ArcadeReady_MetaHandler( $args );
$args = array(
	'id' => 'ar_gameEmbed',
	'title' => __( 'Game Embed Code', 'arcade-ready-tr' ),
	'postType' => 'ar_games',
	'args' => array(
		'dataprocess' => 'base64',
		'desc' => __( 'Paste Game Embed Code (iframe, javascript, etc). NOTE: Takes precidence over Game File if both are present.', 'arcade-ready-tr' ),
		'type' => 'textarea',
	)
);
new ArcadeReady_MetaHandler( $args );
$args = array(
	'id' => 'ar_gameCreatorWebsite',
	'title' => __( 'Game Creator Website', 'arcade-ready-tr' ),
	'postType' => 'ar_games',
	'context' => 'side',
	'args' => array(
		'desc' => __( 'Website of the game creator.', 'arcade-ready-tr' ),
		'type' => 'textfield',
	)
);
new ArcadeReady_MetaHandler( $args );
?>
