<?php
/**
 * Define the Description property.
 */

namespace Datavis_Block\Vega_Lite\Properties\Description;

/**
 * Name of this property.
 */
const PROPERTY = 'description';

/**
 * Apply a format to the value.
 *
 * @param mixed $value Value to format.
 *
 * @return string
 */
function format( $value ) {
	return strval( $value );
}
