<?php
if ( !defined( 'MEDIAWIKI' ) ) {
	exit( 'Wait! What?' );
}
/**
 * Credits.
 */
$wgExtensionCredits['other'][] = array(
	'path'			=> __FILE__,
	'name'			=> 'UnitedUnits',
	'description'		=> 'Units of measurements l10n.',
	'descriptionmsg'	=> 'unitedunits-desc',
	'version'		=> 'POC',
	'author'		=> array( '[http://community.wikia.com/wiki/User:Mroszka Michał Roszka (Mix)]' ),
	'url'			=> 'http://www.mediawiki.org/wiki/Extension:UnitedUnits'
);

/**
 * Hooks.
 */
$wgHooks['ParserFirstCallInit'][]	= 'wfUnitedUnitsParserInit';
$wgHooks['BeforePageDisplay'][]		= 'wfUnitedUnitsBeforePageDisplay';

/**
 * Functions.
 */

/**
 * wfUnitedUnitsParserInit - hooks the main function into the parser.
 */
function wfUnitedUnitsParserInit( Parser $oParser ) {
	$oParser->setHook( 'unit', 'wfUnitedUnitsRender' );
	return true;
}

/**
 * wfUnitedUnitsBeforePageDisplay - loads the JavaScript and CSS
 */
function wfUnitedUnitsBeforePageDisplay( OutputPage $oOut, Skin $oSkin ) {
	$oOut->addScript( '<script src="' . F::app()->wg->extensionsPath . '/wikia/UnitedUnits/UnitedUnits.js"></script>' );
	$oOut->addStyle( AssetsManager::getInstance()->getSassCommonURL( 'extensions/wikia/UnitedUnits/UnitedUnits.scss' ) );
	return true;
}

/**
 * wfUnitedUnitsRender - the main function, does everything :)
 */
function wfUnitedUnitsRender( $sValue, array $aArguments, Parser $oParser, PPFrame $oFrame) {
	global $wgLang;

	$aUnits = array(
		'length' => array( 'kilometre', 'mile' )
	);

	$aOutput = array();

	foreach( $aUnits[$aArguments['quantity']] as $aUnit ) {
		$aAbbreviations = array( 'kilometre' => 'km', 'mile' => 'mi' );
		if ( $aUnit == $aArguments['input'] ) {
			$aOutput[$aUnit] = $wgLang->formatNum( $sValue ) . ' ' . $aAbbreviations[$aUnit];
			continue;
		}

		$sConverter =  'wfUnitedUnitsConverter' . ucfirst( $aArguments['quantity'] ) . ucfirst( $aArguments['input'] ) . ucfirst( $aUnit );
		$aOutput[$aUnit] = $wgLang->formatNum( $sConverter( $sValue ) ) . ' ' . $aAbbreviations[$aUnit];
	}

	ksort( $aOutput );

	return "<span class='unit' data='" . json_encode( $aOutput ) . "'></span>";
}

/**
 * wfUnitedUnitsConverterLengthMileKilometre - converts miles to kilometres.
 */
function wfUnitedUnitsConverterLengthMileKilometre( $sValue ) {
	return bcmul( '1.609344', $sValue, 3);
}

/**
 * wfUnitedUnitsConverterLengthKilometreMile - converts kilometres to miles.
 */
function wfUnitedUnitsConverterLengthKilometreMile( $sValue ) {
	return bcdiv( $sValue, '1.609344', 3);
}

/**
 * Internationalisation.
 */
$wgExtensionMessagesFiles['UnitedUnits'] = __DIR__ . '/UnitedUnits.i18n.php';
