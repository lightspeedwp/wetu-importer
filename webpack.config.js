
/**
 * External dependencies
 */
const RemoveEmptyScriptsPlugin = require( 'webpack-remove-empty-scripts' );
const path = require( 'path' );
const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );

module.exports = {
	...defaultConfig,
	entry: {
		// Admin JavaScript
		'admin': path.resolve( process.cwd(), 'assets/js/lsx-wetu-importer.js' ),

		// Admin CSS from SCSS
		'admin-style': path.resolve( process.cwd(), 'assets/css/lsx-wetu-importer.scss' ),
	},

	output: {
		...defaultConfig.output,
		path: path.resolve( process.cwd(), 'build' ),
	},

	plugins: [
		...defaultConfig.plugins,
		new RemoveEmptyScriptsPlugin(),
	],

	externals: {
		...defaultConfig.externals,
		jquery: 'jQuery',
	}
};
