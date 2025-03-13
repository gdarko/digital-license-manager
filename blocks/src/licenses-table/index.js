/**
 * Copyright (C) 2020-present Darko Gjorgjijoski <https://darkog.com>
 * All Rights Reserved.
 * Licensed under GPLv3.
 */

import { registerBlockType } from '@wordpress/blocks';
import json from './block.json';
import Edit from './edit';
import Save from "./save";

// Destructure the json file to get the name of the block
// For more information on how this works, see: https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Operators/Destructuring_assignment
const { name } = json;

// Register the block
registerBlockType( name, {
	attributes: {
		statusFilter: {
			type: String,
		}
	},
	edit: Edit,
	save: Save
} );
