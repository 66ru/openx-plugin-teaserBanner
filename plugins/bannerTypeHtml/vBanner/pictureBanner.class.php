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
		foreach ($form->_elements as $id=>$elm) {
			if (in_array($elm->_attributes['name'], array('bannertext','statustext'))) {
				unset($form->_elements[$id]);
				unset($form->_elementIndex[ $elm->_attributes['name'] ]);
			}
		}

		// add my controls
		if (isset($aBanner['parameters'])) {
			$vastVariables = unserialize($aBanner['parameters']);
			$aBanner['pict_pos'] = $vastVariables['pict_pos'];
			$aBanner['banner_width'] = $aBanner['width'];
			$aBanner['banner_height'] = $aBanner['height'];
			$aBanner['width'] = $vastVariables['image_width'];
			$aBanner['height'] = $vastVariables['image_height'];
			$form->addElement('hidden', 'banner_width', $aBanner['banner_width']);
			$form->addElement('hidden', 'banner_height', $aBanner['banner_height']);
		}
		if (empty($aBanner['pict_pos'])) {
			$aBanner['pict_pos'] = 'left';
		}

		$picturePos[] = $form->createElement('radio', 'pict_pos', '', 'left', 'left');
		$picturePos[] = $form->createElement('radio', 'pict_pos', '', 'right', 'right');
		$form->addGroup($picturePos, 'VideoFormatAction', 'Picture Position', "<br/>");

		$header = $form->createElement('header', 'header_b_links', "vBanner");
		$header->setAttribute('icon', 'icon-banner-text.gif');
		$form->addElement($header);
		$form->addElement('text', 'bannertext', 'Banner text');

		$sizeG['banner_width'] = $form->createElement('text', 'banner_width', $GLOBALS['strWidth'].":");
		$sizeG['banner_width']->setAttribute('onChange', 'oa_sizeChangeUpdateMessage("warning_change_banner_size");');
		$sizeG['banner_width']->setSize(5);

		$sizeG['banner_height'] = $form->createElement('text', 'banner_height', $GLOBALS['strHeight'].":");
		$sizeG['banner_height']->setAttribute('onChange', 'oa_sizeChangeUpdateMessage("warning_change_banner_size");');
		$sizeG['banner_height']->setSize(5);
		$form->addGroup($sizeG, 'bannersize', 'Banner Size', "&nbsp;", false);

		//validation rules
		$translation = new OX_Translation();
		$widthRequiredRule = array($translation->translate($GLOBALS['strXRequiredField'], array($GLOBALS['strWidth'])), 'required');
		$widthPositiveRule = array($translation->translate($GLOBALS['strXGreaterThanZeroField'], array($GLOBALS['strWidth'])), 'min', 1);
		$heightRequiredRule = array($translation->translate($GLOBALS['strXRequiredField'], array($GLOBALS['strHeight'])), 'required');
		$heightPositiveRule = array($translation->translate($GLOBALS['strXGreaterThanZeroField'], array($GLOBALS['strHeight'])), 'min', 1);
		$numericRule = array($GLOBALS['strNumericField'] , 'numeric');

		$form->addGroupRule('bannersize', array(
			'banner_width' => array($widthRequiredRule, $numericRule, $widthPositiveRule),
			'banner_height' => array($heightRequiredRule, $numericRule, $heightPositiveRule)));

		$form->addElement('hidden', 'ext_bannertype', $this->getComponentIdentifier());

		$form->setAttribute("onSubmit", "return max_formValidateHtml(this.banner)");
	}

	function preprocessForm($insert, $bannerid, &$aFields, &$aVariables) {
		$aVastVariables = array();
		$aVastVariables['pict_pos'] = $aFields['pict_pos'];
		$aVastVariables['image_width'] = $aVariables['width'];
		$aVastVariables['image_height'] = $aVariables['height'];
		$aVariables['width'] = $aFields['banner_width'];
		$aVariables['height'] = $aFields['banner_height'];

		// We serialise all the data into an array which is part of the ox_banners table.
		// This is used by the deliveryEngine for serving ads and is faster then all joins
		// plus it gives us automatic caching
		$aVariables['parameters'] = serialize($aVastVariables);

		// attach the parameters to the normal array to be stored as per normal DataObject technique
		$aVariables = array_merge($aVariables, $aVastVariables);
		return true;
	}

}

?>
