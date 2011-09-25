<?php

require_once MAX_PATH . '/lib/OA.php';
require_once LIB_PATH . '/Extension/bannerTypeHtml/bannerTypeHtml.php';
require_once MAX_PATH . '/lib/max/Plugin/Common.php';
require_once MAX_PATH . '/lib/max/Plugin/Translation.php';

class Plugins_BannerTypeHTML_vBanner_pictureBanner extends Plugins_BannerTypeHTML {

	function getStorageType()
	{
	    return 'web';
	}

	function getOptionDescription() {
		return $this->translate("OpenX Picture Banner");
	}

	function buildForm(&$form, &$aBanner) {
		// remove std controls
		$deleteId = false;
		foreach ($form->_elements as $id=>$elm) {
			if (is_a($elm, 'HTML_QuickForm_header') && $elm->_attributes['name'] == 'header_sql') {
				$deleteId = $id;
				unset($form->_elements[$id]);
				unset($form->_elementIndex[ $elm->_attributes['name'] ]);
			}
		}
		foreach ($form->_elements as $id=>$elm) {
			if ($id > $deleteId)
				unset($form->_elements[$id]);
		}
		foreach ($form->_elementIndex as $elm=>$id) {
			if ($id > $deleteId)
				unset($form->_elementIndex[$elm]);
		}

		// add my controls
		if (isset($aBanner['parameters'])) {
			$vastVariables = unserialize($aBanner['parameters']);
			$aBanner = array_merge($aBanner, $vastVariables);
		}
		if (empty($aBanner['pict_pos'])) {
			$aBanner['pict_pos'] = 'left';
		}

		$form->addElement('header', 'header_b_links', "Banner picture");
		$imageName = _getContentTypeIconImageName($aBanner['image_type']);
		$size = _getBannerSizeText('web', $aBanner['filename']);
		$filename = $aBanner['filename'];
		addUploadGroup($form, $aBanner,
			array(
				'uploadName' => 'bannerimage',
				'radioName' => 'replaceimage',
				'imageName' => $imageName,
				'fileName' => $filename,
				'fileSize' => $size,
				'newLabel' => $GLOBALS['strNewBannerFile'],
				'updateLabel' => $GLOBALS['strUploadOrKeep'],
			)
		);
		$picturePos[] = $form->createElement('radio', 'pict_pos', '', 'left', 'left');
		$picturePos[] = $form->createElement('radio', 'pict_pos', '', 'right', 'right');
		$form->addGroup($picturePos, 'VideoFormatAction', 'Picture Position', "<br/>");

		$header = $form->createElement('header', 'header_b_links', "Banner text");
		$header->setAttribute('icon', 'icon-banner-text.gif');
		$form->addElement($header);
		$form->addElement('text', 'bannertext', 'Text');

		$header = $form->createElement('header', 'header_b_links', "Banner link");
		$header->setAttribute('icon', 'icon-banner-html.gif');
		$form->addElement($header);
		$form->addElement('text', 'url', $GLOBALS['strURL']);
		$form->addElement('text', 'target', $GLOBALS['strTarget']);

		$form->addElement('header', 'header_b_display', 'Banner display');
		$sizeG['width'] = $form->createElement('text', 'width', $GLOBALS['strWidth'] . ":");
		$sizeG['width']->setSize(5);
		$sizeG['height'] = $form->createElement('text', 'height', $GLOBALS['strHeight'] . ":");
		$sizeG['height']->setSize(5);
		if (!empty($aBanner['bannerid'])) {
			$sizeG['height']->setAttribute('onChange', 'oa_sizeChangeUpdateMessage("warning_change_banner_size");');
			$sizeG['width']->setAttribute('onChange', 'oa_sizeChangeUpdateMessage("warning_change_banner_size");');
		}
		$form->addGroup($sizeG, 'size', $GLOBALS['strSize'], "&nbsp;", false);

		$form->addElement('hidden', 'ext_bannertype', $this->getComponentIdentifier());

		//validation rules
		$translation = new OX_Translation();
		$widthRequiredRule = array($translation->translate($GLOBALS['strXRequiredField'], array($GLOBALS['strWidth'])), 'required');
		$heightRequiredRule = array($translation->translate($GLOBALS['strXRequiredField'], array($GLOBALS['strHeight'])), 'required');
		$numericRule = array($GLOBALS['strNumericField'], 'numeric');

		$form->addGroupRule('size', array(
				'width' => array($widthRequiredRule, $numericRule),
				'height' => array($heightRequiredRule, $numericRule)
			));
		$form->setAttribute("onSubmit", "return max_formValidateHtml(this.banner)");
	}

	function preprocessForm($insert, $bannerid, &$aFields, &$aVariables) {
		$this->processNewUploadedFile($aFields, $aVariables);

		$aVastVariables = array();
		$aVastVariables['pict_pos'] = $aFields['pict_pos'];
		$aVastVariables['image_type'] = $aFields['image_type'];
		$aVastVariables['image_width'] = $aFields['image_width'];
		$aVastVariables['image_height'] = $aFields['image_height'];

		// We serialise all the data into an array which is part of the ox_banners table.
		// This is used by the deliveryEngine for serving ads and is faster then all joins
		// plus it gives us automatic caching
		$aVariables['parameters'] = serialize($aVastVariables);

		// attach the parameters to the normal array to be stored as per normal DataObject technique
		$aVariables = array_merge($aVariables, $aVastVariables);
		return true;
	}

	function processNewUploadedFile(&$aFields, &$aVariables) {
		if (empty($_FILES['bannerimage']['name'])) {
			return;
		}
		$oFile = OA_Creative_File::factoryUploadedFile('bannerimage');
		checkForErrorFileUploaded($oFile);
		$oFile->store('web'); // store file on webserver
		$aFile = $oFile->getFileDetails();

		if (!empty($aFile)) {
			// using $aVariables here - as this is an attribute of the base class banner row
			$aVariables['filename'] = $aFile['filename'];
			$aFields['image_type'] = $aFile['contenttype'];
			$aFields['image_width'] = $aFile['width'];
			$aFields['image_height'] = $aFile['height'];
		}
	}
}

?>
