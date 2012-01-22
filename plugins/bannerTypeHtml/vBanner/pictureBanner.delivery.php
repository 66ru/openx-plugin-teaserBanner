<?php

function Plugin_BannerTypeHTML_vBanner_pictureBanner_delivery_adRender(&$aBanner, $zoneId=0, $source='', $ct0='', $withText=false, $logClick=true, $logView=true, $useAlt=false, $loc, $referer)
{
	$prepend = (!empty($aBanner['prepend'])) ? $aBanner['prepend'] : '';
	$append = (!empty($aBanner['append'])) ? $aBanner['append'] : '';

	if ($aBanner['contenttype'] == 'swf')
		$bannerText = Plugin_BannerTypeHTML_vBanner_pictureBanner_delivery_adRenderFlash($aBanner,$zoneId, $source, $ct0, $withText, $logClick, $logView, $useAlt, $loc, $referer);
	else
		$bannerText = Plugin_BannerTypeHTML_vBanner_pictureBanner_delivery_adRenderImage($aBanner,$zoneId, $source, $ct0, $withText, $logClick, $logView, $useAlt, $loc, $referer);

	$clickUrl = _adRenderBuildClickUrl($aBanner, $zoneId, $source, $ct0, $logClick);
	$target = !empty($aBanner['target']) ? $aBanner['target'] : '_blank';

	$pictPos = $aBanner['pict_pos'];
	if (!in_array($pictPos, array('left','right'))) {
		$pictPos = 'left';
	}

	if ($pictPos == 'left') {
		$bannerText = "<td class='mediaTd'>$bannerText</td><td class='textTd' style='width:100%'><a href='$clickUrl' target='$target' class='textAnchor'>{$aBanner['bannertext']}</a></td>";
	} else {
		$bannerText = "<td class='textTd' style='width:100%'><a href='$clickUrl' target='$target' class='textAnchor'>{$aBanner['bannertext']}</a></td><td class='mediaTd'>$bannerText</td>";
	}
    $bannerText = "<table style='width:{$aBanner['width']}px; height:{$aBanner['height']}px' class='vBanner'><tr>$bannerText</tr></table>";
	return $prepend . $bannerText . $append;
}

function Plugin_BannerTypeHTML_vBanner_pictureBanner_delivery_adRenderImage(&$aBanner, $zoneId=0, $source='', $ct0='', $withText=false, $logClick=true, $logView=true, $useAlt=false, $richMedia=true, $loc='', $referer='', $context=array(), $useAppend=true)
{
	if (isset($aBanner['parameters'])) {
		$vastVariables = unserialize($aBanner['parameters']);
		$aBanner = array_merge($aBanner, $vastVariables);
	}

	$conf = $GLOBALS['_MAX']['CONF'];
	$aBanner['bannerContent'] = $imageUrl = _adRenderBuildFileUrl($aBanner, $useAlt);

	if (!$richMedia) {
		return _adRenderBuildFileUrl($aBanner, $useAlt);
	}

	$clickUrl = _adRenderBuildClickUrl($aBanner, $zoneId, $source, $ct0, $logClick);
	$target = !empty($aBanner['target']) ? $aBanner['target'] : '_blank';
	$alt = !empty($aBanner['alt']) ? $aBanner['alt'] : '';
	$bannerText = '';

	if (!empty($aBanner['filename'])) {
		$imageStyle = '';
		if ($aBanner['image_width'] && $aBanner['image_height']) {
			$imageStyle = "style='width:{$aBanner['image_width']}px; height:{$aBanner['image_height']}px'";
		}
		$bannerText.= "<a href='$clickUrl' target='$target' class='imageAnchor'><img border='0' alt='$alt' title='$alt' src='$imageUrl' $imageStyle /></a>";
	}

	$beaconTag = ($logView && $conf['logging']['adImpressions']) ? _adRenderImageBeacon($aBanner, $zoneId, $source, $loc, $referer) : '';
	return $bannerText . $beaconTag;
}

function Plugin_BannerTypeHTML_vBanner_pictureBanner_delivery_adRenderFlash(&$aBanner, $zoneId=0, $source='', $ct0='', $withText=false, $logClick=true, $logView=true, $useAlt=false, $richMedia=true, $loc='', $referer='', $context=array())
{
	if (isset($aBanner['parameters'])) {
		$vastVariables = unserialize($aBanner['parameters']);
		$aBanner = array_merge($aBanner, $vastVariables);
	}

	$conf = $GLOBALS['_MAX']['CONF'];
	$width = !empty($aBanner['image_width']) ? $aBanner['image_width'] : 0;
	$height = !empty($aBanner['image_height']) ? $aBanner['image_height'] : 0;
	$pluginVersion = !empty($aBanner['pluginversion']) ? $aBanner['pluginversion'] : '4';
	if (!empty($aBanner['alt_filename']) || !empty($aBanner['alt_imageurl'])) {
		$altImageAdCode = Plugin_BannerTypeHTML_vBanner_pictureBanner_delivery_adRenderImage($aBanner, $zoneId, $source, $ct0, false, $logClick, false, true, true, $loc, $referer, false);
		$fallBackLogURL = _adRenderBuildLogURL($aBanner, $zoneId, $source, $loc, $referer, '&', true);
	} else {
		$altImageAdCode = "<img src='" . _adRenderBuildImageUrlPrefix() . '/1x1.gif' . "' alt='".$aBanner['alt']."' title='".$aBanner['alt']."' border='0' />";
		$fallBackLogURL = false;
	}

	// Create the anchor tag..
	$clickUrl = _adRenderBuildClickUrl($aBanner, $zoneId, $source, $ct0, $logClick);
	if (!empty($clickUrl)) {  // There is a link
		$status = _adRenderBuildStatusCode($aBanner);
		$target = !empty($aBanner['target']) ? $aBanner['target'] : '_blank';
		$swfParams = array('clickTARGET' => $target, 'clickTAG' => $clickUrl);
	} else {
		$swfParams = array();
	}

	if (!empty($aBanner['parameters'])) {
		$aAdParams = unserialize($aBanner['parameters']);
		if (isset($aAdParams['swf']) && is_array($aAdParams['swf'])) {
			// Converted SWF file, use paramters content
			$swfParams = array();
			$aBannerSwf = $aBanner;
			// Set the flag to let _adRenderBuildClickUrl know that we're not using clickTAG
			$aBannerSwf['noClickTag'] = true;
			foreach ($aAdParams['swf'] as $iKey => $aSwf) {
				$aBannerSwf['url'] = $aSwf['link'];
				$swfParams["alink{$iKey}"] = _adRenderBuildClickUrl($aBannerSwf, $zoneId, $source, $ct0, $logClick);
				$swfParams["atar{$iKey}"]  = $aSwf['tar'];
			}
		}
	}
	$fileUrl = _adRenderBuildFileUrl($aBanner, false);
	$rnd = md5(microtime());

	$swfId = (!empty($aBanner['alt']) ? $aBanner['alt'] : 'Advertisement');

	$code = "
<div id='ox_$rnd'>$altImageAdCode</div>
<script type='text/javascript'><!--/"."/ <![CDATA[
    var ox_swf = new FlashObject('{$fileUrl}', '{$swfId}', '{$width}', '{$height}', '{$pluginVersion}');\n";
	foreach ($swfParams as $key => $value) {
		// URL encode the value, but leave any Openads "magic macros" unescaped to allow substitution
		$code .= "    ox_swf.addVariable('{$key}', '" . preg_replace('#%7B(.*?)%7D#', '{$1}', urlencode($value)) . "');\n";
	}
	if (!empty($aBanner['transparent'])) {
		$code .= "\n   ox_swf.addParam('wmode','transparent');";
	}
	$code .= "
    ox_swf.addParam('allowScriptAccess','always');
    ox_swf.write('ox_$rnd');\n";

	if ($logView && $conf['logging']['adImpressions']) {
		// Only render the log beacon if the user has the minumum required flash player version
		$code .= "    if (ox_swf.installedVer.versionIsValid(ox_swf.getAttribute('version'))) { document.write(\""._adRenderImageBeacon($aBanner, $zoneId, $source, $loc, $referer)."\"); }";
		// Otherwise log a fallback impression (if there is a fallback creative configured)
		if ($fallBackLogURL) {
			$code .= ' else { document.write("'._adRenderImageBeacon($aBanner, $zoneId, $source, $loc, $referer, $fallBackLogURL).'"); }';
		}
	}
	$code .= "\n/"."/ ]]> --></script>";
	if ($fallBackLogURL) {
		$code .= '<noscript>' . _adRenderImageBeacon($aBanner, $zoneId, $source, $loc, $referer, $fallBackLogURL) . '</noscript>';
	}

	return $code;
}

?>
