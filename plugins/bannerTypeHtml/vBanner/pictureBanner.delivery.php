<?php

function Plugin_BannerTypeHTML_vBanner_pictureBanner_delivery_adRender(&$aBanner, $zoneId=0, $source='', $ct0='', $withText=false, $logClick=true, $logView=true, $useAlt=false, $loc, $referer)
{
	if (isset($aBanner['parameters'])) {
		$vastVariables = unserialize($aBanner['parameters']);
		$aBanner = array_merge($aBanner, $vastVariables);
	}

    $prepend = !empty($aBanner['prepend']) ? $aBanner['prepend'] : '';
    $append = !empty($aBanner['append']) ? $aBanner['append'] : '';
	$url = _adRenderBuildClickUrl($aBanner, $zoneId, $source, $ct0, $logClick);
    $target = !empty($aBanner['target']) ? $aBanner['target'] : '_blank';
    $bannerText = '';

	$pictPos = $aBanner['pict_pos'];
	if (!in_array($pictPos, array('left','right'))) {
		$pictPos = 'left';
	}
    if (!empty($aBanner['filename'])) {
        $imagePath = _adRenderBuildImageUrlPrefix() . "/" . $aBanner['filename'];
	    $imageStyle = '';
	    if ($aBanner['image_width'] && $aBanner['image_height']) {
		    $imageStyle = "style='width:{$aBanner['image_width']}px; height:{$aBanner['image_height']}px'";
	    }
        $bannerText.= "<a href='$url' target='$target' style='float:$pictPos' class='imageAnchor'><img border='0' src='$imagePath' $imageStyle /></a>";
    }
	$bannerText.= "<a href='$url' target='$target' class='textAnchor'>{$aBanner['bannertext']}</a>";

    $bannerText = "<div style='width:{$aBanner['width']}px; height:{$aBanner['height']}px' class='vBanner'>$bannerText</div>";
	$conf = $GLOBALS['_MAX']['CONF'];
	$beaconTag = ($logView && $conf['logging']['adImpressions']) ? _adRenderImageBeacon($aBanner, $zoneId, $source, $loc, $referer) : '';
	return $prepend . $bannerText . $beaconTag . $append;
}

?>
