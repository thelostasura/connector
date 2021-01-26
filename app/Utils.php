<?php

namespace TheLostAsura\Connector;

class Utils {
	public static function is_oxygen_editor() {
		return defined( 'SHOW_CT_BUILDER' ) && ! defined( 'OXYGEN_IFRAME' );
	}
}