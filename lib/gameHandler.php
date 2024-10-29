<?php
class ArcadeReady_GameHandler {

	public $gameID;
	public $gameData;

	public function getEmbedCode( $gameID,$gameFileID ) {
		$this->gameID = $gameID;
		$this->gameFileId = $gameFileID;
		// check if embed code is present.
		$embedCode = get_post_meta( $this->gameID, 'ar_gameEmbed', true );
		if ( !empty( $embedCode ) ) {
			return $this->embedCode( $embedCode );
		}
		$filePath = get_attached_file( $this->gameFileId );
		$fileUrl = wp_get_attachment_url( $this->gameFileId );
		$filename = basename( $filePath );
		$ext = $this->getExt( $filename );
		$size = $this->getSize( $filePath, $ext );
		$this->gameData = array(
			'filename' => $filename,
			'filePath' => $filePath,
			'fileUrl'  => $fileUrl,
			'fileExt'  => $ext,
			'size' 	   => $size,
		);
		switch ( $this->gameData['fileExt'] ) {
			case 'swf':
				return $this->embedSWF();
			break;
			default:
				return '**error in embed code**';
			break;
		}
	}
	public function embedCode( $embedCode ) {
		return base64_decode( $embedCode );
	}
	public function embedSWF() {
		$html = '<!--[if !IE]> -->
				<object type="application/x-shockwave-flash" data="' . $this->gameData['fileUrl'] . '" width="' . $this->gameData['size']['width'] . '" height="' . $this->gameData['size']['height'] . '">
			<!-- <![endif]-->
			<!--[if IE]>
				<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000"
				codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9.0.115.0"
				width="' . $this->gameData['size']['width'] . '" height="' . $this->gameData['size']['height'] . '">
				<param name="movie" value="' . $this->gameData['fileUrl'] . '" />
			<!-->
			<param name="quality" value="high">
			<param name="AllowScriptAccess" value="never">
				<param name="loop" value="true" />
				<param name="menu" value="false" />
				<p>It appears you are missing the plugin required to play this game, you can download it at: <a href="http://get.adobe.com/flashplayer/ ">Adobe.com</a></p>
				</object>
			<!-- <![endif]-->';
		return $html;
	}
	public function getExt( $file ) {
		$ext = pathinfo( $file, PATHINFO_EXTENSION );
		return $ext;
	}
	public function getSize( $filePath, $ext ) {

		if ( $ext == 'swf' ) {
			$data = getimagesize( $filePath );
			$size = array(
				'width' => $data[0],
				'height' => $data[1],
			);
			return $size;
		}
	}
}
$ARgameHandler = new ArcadeReady_GameHandler();
?>
